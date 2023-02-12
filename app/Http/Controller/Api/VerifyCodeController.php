<?php

namespace App\Http\Controller\Api;

use App\Lib\MyCode;
use App\Lib\MyCommon;
use App\Lib\MyQuit;
use App\Lib\MyValidator;
use App\Model\Entity\Captcha;
use App\Model\Entity\CountryCode;
use App\Model\Entity\UserBasicalInfo;
use App\Rpc\Lib\UserInterface;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Db\DB;
use Swoft\Http\Message\Request;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\Middleware;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Http\Server\Annotation\Mapping\RequestMethod;
use Swoft\Log\Helper\CLog;
use Swoft\Log\Helper\Log;
use Swoft\Redis\Redis;
use Swoft\Stdlib\Helper\JsonHelper;
use App\Http\Middleware\AuthMiddleware;
use Swoft\Rpc\Client\Annotation\Mapping\Reference;
use App\Http\Middleware\BaseMiddleware;

/**
 * 验证码控制器
 * Class CaptchaController
 * @package App\Http\Controller\Api
 * @Controller("v1/verify_code")
 * @Middleware(BaseMiddleware::class)
 */
class VerifyCodeController
{

    /**
     * @Inject()
     * @var MyValidator
     */
    private $myValidator;

    /**
     * @Inject()
     * @var MyCommon
     */
    private $myCommon;

    /**
     * @Reference(pool="auth.pool")
     * @var UserInterface
     */
    private $userService;

    /**
     * 发送验证码(登录前)
     * @RequestMapping(method={RequestMethod::POST})
     * @param Request $request
     * @return
     * @throws \Swoft\Validator\Exception\ValidatorException
     */
    public function send(Request $request)
    {
        $params = $request->params;
        //验证参数
        $params = validate($params, 'AuthValidator', ['account', 'action', 'type', 'token']);
        //验证参数
        if (empty($params['account']) && empty($params['token'])) {
            return MyQuit::returnMessage(MyCode::PARAM_ERROR, '参数错误');
        }
        if (!empty($params['token'])) {
            $user_info = json_decode(Redis::get($params['account'] . '_' . $params['token']), true);
            if (empty($user_info)) {
                return MyQuit::returnMessage(MyCode::OPERATE_ERROR, '操作有误');
            }
            $params['account'] = $user_info[$params['type']];
            $params['area_code'] = $user_info['area_code'];
        }
        //验证action
        if (!array_key_exists($params['action'], config('app.actions'))) {
            return MyQuit::returnMessage(MyCode::PARAM_ERROR, '参数错误');
        }
        //获取国家信息
        if ($params['type'] === 'mobile') {
            if (empty($params['area_code'])) {
                return MyQuit::returnMessage(MyCode::PARAM_ERROR, '参数错误！');
            }
            $country_info = CountryCode::where(['area_code' => $params['area_code']])->exists();
            if (empty($country_info)) {
                return MyQuit::returnMessage(MyCode::PARAM_ERROR, '参数错误！');
            }
            if (!$this->myCommon->is_mobile($params['account'], $params['area_code'])) {
                return MyQuit::returnMessage(MyCode::MOBILE_ERROR, '手机号码格式错误！');
            }
        } else {
            if (!$this->myCommon->is_email($params['account'])) {
                return MyQuit::returnMessage(MyCode::EMAIL_ERROR, '邮箱格式错误！');
            }
        }
        //验证account类型
        $account_type = $params['type'];
        $code_key = $params['action'] . '_code_key';
        $redis_data = Redis::hget($code_key, $params['account']);
        if (!empty($redis_data)) {
            $redis_data = json_decode($redis_data, true);
            if (time() < $redis_data['create_time'] + config('app.code_operate_expire_time')) {
                return MyQuit::returnMessage(MyCode::INTERVAL_AGAIN_SEND_CPATCHA, '一分钟内只能发送一次验证码');
            }
        }
        $is_exists = $this->userService->is_exists([$account_type => $params['account']]);
        switch ($params['action']) {
            case 'register':
                if ($is_exists) {
                    return MyQuit::returnMessage(MyCode::REGISTER_ALREADY, $account_type == 'mobile' ? '该手机号已被注册' : '该邮箱已被注册');
                }
                break;
            case 'bind_email':
                if ($is_exists) {
                    return MyQuit::returnMessage(MyCode::PHONE_BING, '邮箱已经被绑定！');
                }
                break;
            case 'modify_mobile':
            case 'bind_mobile':
                if ($is_exists) {
                    return MyQuit::returnMessage(MyCode::EMAIL_BING, '手机号码已经被绑定！');
                }
                break;
            default:
                if (!$is_exists) {
                    return MyQuit::returnMessage(MyCode::USER_NOT_LOGIN, '用户不存在！');
                }
                break;
        }
        try {
            $params['area_code'] = $params['area_code'] ?? '';
            $code = $this->myCommon->send_verify_code($params['account'], $params['area_code'], $params['action']);
            if (!$code) {
                throw new \Exception('push list error');
            }
            $data = [
                'account' => $params['account'],
                'type' => $account_type,
                'ip' => $request->ip,
                'create_time' => time(),
                'code' => $code,
                'action' => $params['action'],
                'user_agent' => $request->getHeaderLine('user-agent'),
            ];
            $res = Captcha::insert($data);
            if (!$res) {
                throw new \Exception('insert captcha log error');
            }
            $response = MyQuit::returnMessage(MyCode::SUCCESS, '发送成功！');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            CLog::error($e->getMessage());
            $response = MyQuit::returnMessage(MyCode::SERVER_ERROR, '服务器繁忙');
        }
        return $response;
    }

    /**
     * 短信验证码（登录后）
     * @param Request $request
     * @RequestMapping(method={RequestMethod::POST})
     * @Middleware(AuthMiddleware::class)
     */
    public function send_code(Request $request)
    {
        $params = $request->params;
        //验证参数
        $params = validate($params, 'AuthValidator', ['action', 'type']);
        //验证action
        if (!array_key_exists($params['action'], config('app.actions'))) {
            return MyQuit::returnMessage(MyCode::PARAM_ERROR, '参数错误');
        }
        $account = $request->user_info[$params['type']];
        if (!$account) {
            return MyQuit::returnMessage(MyCode::PARAM_ERROR, '参数错误');
        }
        $code_key = $params['action'] . '_code_key';
        $redis_data = Redis::hget($code_key, $account);
        if (!empty($redis_data)) {
            $redis_data = json_decode($redis_data, true);
            if (time() < $redis_data['create_time'] + config('app.code_operate_expire_time')) {
                return MyQuit::returnMessage(MyCode::INTERVAL_AGAIN_SEND_CPATCHA, '一分钟内只能发送一次验证码');
            }
            Redis::hDel($code_key, $account);
        }
        try {
            $code = $this->myCommon->send_verify_code($account, $request->user_info['area_code'], $params['action']);
            if (!$code) {
                throw new \Exception('push list error');
            }
            $data = [
                'account' => $account,
                'type' => $params['type'],
                'ip' => $request->ip,
                'create_time' => time(),
                'code' => $code,
                'action' => $params['action'],
                'user_agent' => $request->getHeaderLine('user-agent'),
            ];
            $res = Captcha::insert($data);
            if (!$res) {
                throw new \Exception('insert captcha log error');
            }
            $response = MyQuit::returnMessage(MyCode::SUCCESS, '发送成功！');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            CLog::error($e->getMessage());
            $response = MyQuit::returnMessage(MyCode::SERVER_ERROR, '服务器繁忙');
        }
        return $response;
    }

}

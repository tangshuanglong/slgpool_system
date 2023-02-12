<?php

namespace App\Rpc\Service;

use App\lib\MyCode;
use App\Lib\MyCommon;
use App\Lib\MyQuit;
use App\Lib\MyRedisHelper;
use App\Lib\MySign;
use App\Lib\MyValidator;
use App\Model\Entity\GoogleSecret;
use App\Rpc\Lib\AuthInterface;
use App\Rpc\Lib\UserInterface;
use App\Rpc\Lib\VerifyInterface;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Bean\BeanFactory;
use Swoft\Redis\Redis;
use Swoft\Rpc\Client\Annotation\Mapping\Reference;
use Swoft\Rpc\Server\Annotation\Mapping\Service;

/**
 * @since 2.0
 * Class VerifyService
 * @package App\Rpc\Service
 * @Service(version="1.0")
 */
class VerifyService implements VerifyInterface
{

    /**
     * @Reference(pool="auth.pool")
     * @var UserInterface
     */
    private $userService;

    /**
     * @Inject()
     * @var MyValidator
     */
    private $myValidator;

    /**
     * 验证签名
     * @param array $data
     * @return bool
     */
    public function verify_sign(array $data): bool
    {
        /**@var MySign $mySign */
        $mySign = BeanFactory::getBean('MySign');
        return $mySign->checkSign($data);
    }

    /**
     * 验证验证码
     * @param string $account
     * @param string $code
     * @param string $action
     * @return bool
     */
    public function verify_code(string $account, string $code, string $action): bool
    {
        $code_key = $action . '_code_key';
        $data = MyRedisHelper::hget($code_key, $account);
        if (empty($data)) {
            return false;
        }
        if (time() > $data['create_time'] + (config('app.code_expire_time', 15) * 60)) {
            Redis::hDel($code_key, $account);
            return false;
        }
        if ($code != $data['code']) {
            return false;
        }
        return Redis::hDel($code_key, $account);
    }

    /**
     * 验证所有验证码
     * @param array $data
     * @return bool|mixed|string
     */
    public function verify_all(array $data)
    {
        if (empty($data)) {
            return false;
        }
        $del_info = [];
        foreach ($data as $type => $val) {
            $code_key = $val['action'] . '_code_key';
            $data = MyRedisHelper::hget($code_key, $val['account']);
            if (empty($data)) {
                return $type;
            }
            if (time() > $data['create_time'] + (config('app.code_expire_time', 15) * 60)) {
                Redis::hDel($code_key, $val['account']);
                return $type;
            }
            if ($val['code'] != $data['code']) {
                return $type;
            }
            $del_info[] = [
                'key'     => $code_key,
                'account' => $val['account'],
            ];
            unset($type, $val);
        }
        $count = count($del_info);
        if ($count === 1) {
            return Redis::hDel($del_info[0]['key'], $del_info[0]['account']);
        }
        if ($count === 2) {
            $script = '
                if redis.call("hDel", KEYS[1], ARGV[1]) then
                    return redis.call("hDel", KEYS[2], ARGV[2])
                else
                    return false
                end
            ';
            return Redis::eval($script, [$del_info[0]['key'], $del_info[1]['key'], $del_info[0]['account'], $del_info[1]['account']], $count);
        }
        return false;
    }

    /**
     * 验证所有验证码
     * @param $uid
     * @param array $params
     * @param string $action
     * @return mixed
     * @throws \Swoft\Db\Exception\DbException
     */
    public function auth_all_verify_code($uid, array $params, string $action)
    {
        $user_bind_info = $this->userService->get_bind_info($uid);
        $verify_data = [];
        if ($user_bind_info['google_validator'] == 1) {
            //验证谷歌验证码
            $google_verify_code = isset($params['gv_code']) ? $params['gv_code'] : '';
            $res = $this->myValidator->google_verify($google_verify_code, $uid);
            if (!$res) {
                return MyCommon::returnRst(MyCode::CAPTCHA_GOOGLE_ERROR, '谷歌验证码错误');
            }
        }
        if ($user_bind_info['mobile_verify'] == 1) {
            $mobile_verify_code = isset($params['mv_code']) ? $params['mv_code'] : '';
            //验证手机验证码
            $verify_data['mobile'] = [
                'account' => $user_bind_info['mobile'],
                'code'    => $mobile_verify_code,
                'action'  => $action,
            ];
        }
        if ($user_bind_info['email_verify'] == 1) {
            //验证邮箱验证码
            $email_verify_code = isset($params['ev_code']) ? $params['ev_code'] : '';
            $verify_data['email'] = [
                'account' => $user_bind_info['email'],
                'code'    => $email_verify_code,
                'action'  => $action,
            ];
        }

        $res = true;
        // 当开了手机和邮箱至少一种验证的时候，才需要调用verify_all接口
        if (!empty($verify_data)) {
            $verify_res = $this->verify_all($verify_data);
            if ($verify_res === 'mobile') {
                $res = MyCommon::returnRst(MyCode::CAPTCHA_MOBILE_ERROR, '短信验证码错误');
            } elseif ($verify_res === 'email') {
                $res = MyCommon::returnRst(MyCode::CAPTCHA_EMAIL_ERROR, '邮箱验证码错误');
            } elseif ($verify_res === false) {
                $res = MyCommon::returnRst(MyCode::PARAM_ERROR, '参数错误');
            }
        }

        return $res;
    }

    /**
     * @param $code
     * @param $uid
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    public function google_verify($code, $uid)
    {
        return $this->myValidator->google_verify($code, $uid);
    }


}

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
 *
 * Class NotifyController
 * @package App\Http\Controller\Api
 * @Controller("v1/notify")
 * @Middleware(BaseMiddleware::class)
 */
class NotifyController
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
     * 发送通知
     * @RequestMapping(method={RequestMethod::POST})
     * @param Request $request
     * @return
     * @throws \Swoft\Validator\Exception\ValidatorException
     */
    public function send(Request $request)
    {
        $params = $request->params;
        if (empty($params)) {
            return MyQuit::returnMessage(MyCode::LACK_PARAM, '缺乏参数');
        }
        //验证参数
        $params = validate($params, 'NotifyValidator', ['app_id', 'app_secret', 'to', 'area', 'action_name', 'temp_key', 'send_data']);
        if ($this->myValidator->account_check($params['to'], $params['area']) === false) {
            return MyQuit::returnMessage(MyCode::PARAM_ERROR, 'to参数格式错误');
        }
        if ($params['app_id'] !== config('rpc_app_id') || $params['app_secret'] !== config('rpc_app_secret')) {
            return MyQuit::returnMessage(MyCode::PARAM_ERROR, '参数错误');
        }
        $action_name = $params['action_name'] ?? '';
        $res = $this->myCommon->push_notice_queue($params['to'], $params['area'], $params['temp_key'], $action_name, $params['send_data']);
        if ($res) {
            return MyQuit::returnMessage(MyCode::SUCCESS, 'success');
        }
        return MyQuit::returnMessage(MyCode::SERVER_ERROR, '服务器错误');
    }

}

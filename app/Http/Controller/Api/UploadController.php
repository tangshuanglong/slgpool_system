<?php

namespace App\Http\Controller\Api;

use App\Lib\MyCode;
use App\Lib\MyFileToken;
use App\Lib\MyQuit;
use Qiniu\Auth;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Http\Message\Request;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\Middleware;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Http\Server\Annotation\Mapping\RequestMethod;
use App\Http\Middleware\BaseMiddleware;
use App\Http\Middleware\AuthMiddleware;
/**
 * Class UploadController
 * @package App\Http\Controller\Api
 * @Controller(prefix="/v1/upload")
 * @Middleware(BaseMiddleware::class)
 */
class UploadController{

    /**
     * @Inject()
     * @var MyFileToken
     */
    private $myFileToken;

    /**
     * 获取七牛云token
     * @param Request $request
     * @RequestMapping(method={RequestMethod::GET})
     * @Middleware(AuthMiddleware::class)
     * @return array
     */
    public function get_token(Request $request)
    {
        //实例化鉴权类
        $auth = new Auth(config('app.qi_niu_ak'), config('app.qi_niu_sk'));
        $body = [
            "filename" => "$(key)",
            "uid" => $request->uid,
        ];
        $policy = [
            //回调url
            'callbackUrl' => config('qi_niu_callback_url'),
            //回调服务器收到的body
            'callbackBody' => json_encode($body),//"filename=$(key)&uid=".$this->uid."&uploadType=".$type
        ];
        $upToken = $auth->uploadToken(config('app.qi_niu_bucket'), null, 600, $policy);
        return MyQuit::returnSuccess( ['upToken' => $upToken], MyCode::SUCCESS,  '获取成功！');
    }

    /**
     * 获取文件服务器的token【暂时没用】
     * @return array
     * @throws \Exception
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function file_token()
    {
        $upToken = $this->myFileToken->generateToken(config('file_url'));
        return MyQuit::returnSuccess( ['upToken' => $upToken], MyCode::SUCCESS,  '获取成功！');
    }
}

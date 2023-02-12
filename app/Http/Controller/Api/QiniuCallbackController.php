<?php

namespace App\Http\Controller\Api;

use App\Lib\MyCommon;
use Qiniu\Auth;
use Swoft\Http\Message\Request;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Http\Server\Annotation\Mapping\RequestMethod;

/**
 * 七牛云回调地址
 * Class QiniuCallbackController
 * @package App\Http\Controller\Api
 * @Controller(prefix="/v1/callback")
 */
class QiniuCallbackController
{

    /**
     *
     * @param Request $request
     * @return array
     * @RequestMapping(method={RequestMethod::POST})
     */
    public function index(Request $request)
    {
        //获取七牛云回调的数据
        $callbackBody = $request->raw();
        //实例化鉴权类
        $auth = new Auth(config('app.qi_niu_ak'), config('app.qi_niu_sk'));
        $contentType = 'application/x-www-form-urlencoded';
        //获取验证字符串
        $authorization = $request->headerLine('authorization');
        $url = config('qi_niu_callback_url');
        //鉴权
        $isQiniuCallback = $auth->verifyCallback($contentType, $authorization, $url, $callbackBody);
        if (!$isQiniuCallback) {
            return $this->response_msg(403, 'auth error', array('error' => '鉴权失败！！！'));
        }
        $data = json_decode($callbackBody, true);
        $blacklist = [];
        if (in_array($data['filename'], $blacklist)) {
            return $this->response_msg(500, 'Upload Failure', array('error' => '图片类型错误！！！'));
        }
        $filename = config('qi_niu_domain') . "/" . $data['filename'];
        return $this->response_msg(100, 'Upload Successfully', array('image_path' => $filename));
    }

    /**
     * 响应
     * @param int $errcode
     * @param string $msg
     * @param array $data
     * @return array
     */
    private function response_msg(int $errcode, string $msg, array $data = array())
    {
        return array(
            'errcode' => $errcode,
            'msg'     => $msg,
            'data'    => $data
        );
    }

}

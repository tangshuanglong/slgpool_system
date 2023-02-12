<?php

namespace App\Lib;

use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Stdlib\Helper\JsonHelper;

/**
 * Class MyFileToken
 * @package App\Lib
 * 生成上传图片token
 * @Bean("MyFileToken")
 */
class MyFileToken
{

    private $access_key = '';

    private $secret_key = '';


    public function __construct()
    {
        $this->access_key = config('app.qi_niu_ak');
        $this->secret_key = config('app.qi_niu_sk');
        $this->aes_key = hex2bin(config('app.file_aes_key'));
    }

    /**
     * 生成token
     * @param string $url
     * @return string
     * @throws \Exception
     */
    public function generateToken(string $url)
    {
        $time = time();
        $exp = $time + 1800;
        $header = [
            'exp' => $exp,
            'ak' => $this->access_key,
            'url' => $url
        ];
        //对头信息进行aes加密
        $aes_header = MyAes::getInstance()->encrypt(JsonHelper::encode($header), $this->aes_key);
        //生成签名
        $sign = base64_encode(hash_hmac('sha256', $aes_header, $this->secret_key, true));
        //拼接token
        return $aes_header.'.'.$sign;
    }


    /**
     * 验证token
     * @param string $token
     * @return bool
     * @throws \Exception
     */
    public function checkToken(string $token)
    {
        if($token === '0' || $token === ''){
            return false;
        }
        $explode_token = explode('.', $token);
        if (count($explode_token) !== 2){
            return false;
        }
        list($aes_header, $verify_sign) = $explode_token;
        //生成签名
        $sign = base64_encode(hash_hmac('sha256', $aes_header, $this->secret_key, true));
        //签名不一致返回登录过期
        if(strcasecmp($verify_sign, $sign) != 0){
            Log::write_log('签名不一致', '/logs/file_server');
            return false;
        }
        $header = JsonHelper::decode(MyAes::getInstance()->decrypt($aes_header, $this->aes_key), true);
        if(!$header){
            Log::write_log('解析不到数据', '/logs/file_server');
            return false;
        }
        if (strcasecmp($header['ak'], $this->access_key) != 0) {
            Log::write_log('ak不一致', '/logs/file_server');
            return false;
        }
        //如果缓存不存在或did不一致，或过期返回登录过期
        if($header['exp'] < time()){
            Log::write_log('token已过期过期', '/logs/file_server');
            return false;
        }
        return $header['url'];
    }


}

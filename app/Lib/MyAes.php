<?php

namespace App\Lib;

use Swoft\Bean\Annotation\Mapping\Bean;

/**
 * AES加解密类
 * Class MyAes
 * @package App\Lib
 */
class MyAes
{

    private $method;

    public function __construct(string $method)
    {
        $this->method = $method;
    }

    /**
     * 获取实例
     * @param string $method
     * @return MyAes
     * @throws \Exception
     */
    public static function getInstance(string $method = 'AES-128-CBC')
    {
        $methods = openssl_get_cipher_methods();
        if (!in_array($method, $methods)) {
            Throw new \Exception('aes cipher method not exists');
        }
        return new self($method);
    }


    /**
     * 加密
     * @param string $input
     * @param string $key
     * @return string
     */
    public function encrypt(string $input, string $key = '')
    {
        //获取iv长度
        $iv = '';
        $iv_size = openssl_cipher_iv_length($this->method);
        if(!$iv_size){
            $crypted = openssl_encrypt($input, $this->method, $key,OPENSSL_RAW_DATA);;
        } else {
            //生成iv
            $iv = substr(md5(time().mt_rand(0,1000000), true), 0, $iv_size);
            //加密数据
            $crypted = openssl_encrypt($input, $this->method, $key,OPENSSL_RAW_DATA , $iv);
        }
        //iv拼在加密数据签名base64返回
        return base64_encode($iv.$crypted);
    }

    /**
     * 解密
     * @param string $input
     * @param string $key
     * @return bool|false|string
     */
    public function decrypt(string $input, string $key)
    {
        $data = base64_decode($input);
        if(empty($data)){
            return false;
        }
        $iv_size = openssl_cipher_iv_length($this->method);
        //如果iv大小为0，代表不用向量，如ecb模式
        if(!$iv_size){
            return openssl_decrypt($data, $this->method, $key, OPENSSL_RAW_DATA);
        }
        //得到iv和加密数据
        $iv = substr($data, 0, $iv_size);
        $crypted = substr($data, $iv_size);
        if(empty($crypted)){
            return false;
        }
        return openssl_decrypt($crypted, $this->method, $key, OPENSSL_RAW_DATA, $iv);
    }

}

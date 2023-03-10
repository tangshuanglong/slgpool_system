<?php

namespace App\Rpc\Lib;

/**
 * Interface VerifyInterface
 * @package App\Rpc\Lib
 */
interface VerifyInterface
{

    /**
     * 验证签名
     * @param $sign 签名
     * @param $data 签名的数据
     * @return mixed
     */
    public function verify_sign(array $data): bool;

    /**
     * 验证验证码
     * @param string $account
     * @param string $code
     * @param string $action
     * @return bool
     */
    public function verify_code(string $account, string $code, string $action): bool;



}

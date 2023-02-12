<?php

namespace App\Rpc\Lib;

/**
 * Interface VerifyCodeInterface
 */
interface FileTokenInterface{

    /**
     * 生成token
     * @return mixed
     */
    public function generate_token();

}

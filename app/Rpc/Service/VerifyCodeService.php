<?php

namespace App\Rpc\Service;

use App\Lib\MyCommon;
use App\Model\Entity\Captcha;
use App\Rpc\Lib\VerifyCodeInterface;
use App\Rpc\Lib\VerifyInterface;
use Swoft\Bean\BeanFactory;
use Swoft\Rpc\Server\Annotation\Mapping\Service;

/**
 * Class VerifyCodeService
 * @package App\Rpc\Service
 * @Service()
 */
class VerifyCodeService implements VerifyCodeInterface{


    /**
     * 发送验证码
     * @param string $account
     * @param string $area_code
     * @param string $action
     * @return mixed
     */
    public function send_verify_code(string $account, string $area_code, string $action)
    {
        /**@var MyCommon $myCommon */
        $myCommon = BeanFactory::getBean('MyCommon');
        $code = $myCommon->send_verify_code($account, $area_code, $action);
        $account_type = 'mobile';
        $is_email = $myCommon->is_email($account);
        if ($is_email) {
            $account_type = 'email';
        }
        $data = [
            'account' => $account,
            'type' => $account_type,
            'ip' => '127.0.0.1',
            'create_time' => time(),
            'code' => $code,
            'action' => $action,
            'user_agent' => 'localhost:rpc',
        ];
        Captcha::insert($data);
        return $code;
    }


}

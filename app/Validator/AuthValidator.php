<?php

namespace App\Validator;

use Swoft\Validator\Annotation\Mapping\Email;
use Swoft\Validator\Annotation\Mapping\Enum;
use Swoft\Validator\Annotation\Mapping\IsInt;
use Swoft\Validator\Annotation\Mapping\IsString;
use Swoft\Validator\Annotation\Mapping\Length;
use Swoft\Validator\Annotation\Mapping\NotEmpty;
use Swoft\Validator\Annotation\Mapping\NotInEnum;
use Swoft\Validator\Annotation\Mapping\Validator;

/**
 * Class AuthValidator
 * @package App\Validator
 *
 * @Validator(name="AuthValidator")
 */
class AuthValidator{

    /**
     * 账号
     * @IsString()
     * @var string
     */
    protected $account;

    /**
     *
     * @IsString()
     * Length(min=32, max=32, message="登录密码长度错误")
     * @var string
     */
    protected $token;

    /**
     * 发送短信类型
     * @IsString()
     * @NotEmpty()
     * @var
     */
    protected $action;

    /**
     * 手机区号
     * @IsString()
     * @var
     */
    protected $area_code;

    /**
     * @IsString()
     * @NotEmpty()
     * @Enum(values={"email", "mobile"})
     * @var
     */
    protected $type;

}

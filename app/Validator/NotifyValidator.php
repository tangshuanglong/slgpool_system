<?php

namespace App\Validator;

use Swoft\Validator\Annotation\Mapping\Enum;
use Swoft\Validator\Annotation\Mapping\IsInt;
use Swoft\Validator\Annotation\Mapping\IsString;
use Swoft\Validator\Annotation\Mapping\Length;
use Swoft\Validator\Annotation\Mapping\NotEmpty;
use Swoft\Validator\Annotation\Mapping\Validator;
use Swoft\Validator\Annotation\Mapping\IsArray;

/**
 * Class UserValidator
 * @package App\Validator
 * @Validator(name="NotifyValidator")
 */
class NotifyValidator{


    /**
     * @IsString()
     * @NotEmpty()
     * @var
     */
    protected $app_id;

    /**
     * @IsString()
     * @NotEmpty()
     * @var
     */
    protected $app_secret;

    /**
     * @IsString()
     * @var
     */
    protected $to;

    /**
     * @IsString()
     * @NotEmpty()
     * @var
     */
    protected $area;

    /**
     * @IsString()
     * @var
     */
    protected $action_name;

    /**
     * @IsString()
     * @NotEmpty()
     * @var
     */
    protected $temp_key;

    /**
     * @IsArray()
     * @NotEmpty()
     * @var
     */
    protected $send_data;

}

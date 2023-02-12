<?php

namespace App\Validator;

use Swoft\Validator\Annotation\Mapping\Enum;
use Swoft\Validator\Annotation\Mapping\IsString;
use Swoft\Validator\Annotation\Mapping\NotEmpty;
use Swoft\Validator\Annotation\Mapping\Validator;

/**
 * Class AppVersionValidator
 * @package App\Validator
 * @Validator("AppVersionValidator")
 */
class AppVersionValidator{


    /**
     * @IsString()
     * @NotEmpty()
     * @Enum(values={"1", "2"})
     * @var
     */
    protected $type;

    /**
     * @IsString()
     * @NotEmpty()
     * @var
     */
    protected $version;

}

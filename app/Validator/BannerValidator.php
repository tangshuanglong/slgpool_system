<?php

namespace App\Validator;

use Swoft\Validator\Annotation\Mapping\Enum;
use Swoft\Validator\Annotation\Mapping\IsString;
use Swoft\Validator\Annotation\Mapping\NotEmpty;
use Swoft\Validator\Annotation\Mapping\Validator;

/**
 * Class BannerValidator
 * @package App\Validator
 * @Validator("BannerValidator")
 */
class BannerValidator
{

    /**
     * @IsString()
     * @NotEmpty()
     * @Enum(values={"pc", "app"})
     * @var
     */
    protected $use_client;

    /**
     * @IsString()
     * @Enum(values={"index", "invite", "start"})
     * @var
     */
    protected $position;

}

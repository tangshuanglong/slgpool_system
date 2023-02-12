<?php

namespace App\Validator;

use Swoft\Validator\Annotation\Mapping\Enum;
use Swoft\Validator\Annotation\Mapping\IsString;
use Swoft\Validator\Annotation\Mapping\NotEmpty;
use Swoft\Validator\Annotation\Mapping\Validator;

/**
 * Class CoinValidator
 * @package App\Validator
 * @Validator("CoinValidator")
 */
class CoinValidator{


    /**
     * @IsString()
     * @NotEmpty()
     * @var
     */
    protected $coin_type;

    /**
     * @IsString()
     * @NotEmpty()
     * @Enum(values={"transfer", "withdraw", "deposit", "exchange", "all"})
     * @var
     */
    protected $type;

}

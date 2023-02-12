<?php

namespace App\Validator;

use Swoft\Validator\Annotation\Mapping\Email;
use Swoft\Validator\Annotation\Mapping\Enum;
use Swoft\Validator\Annotation\Mapping\IsInt;
use Swoft\Validator\Annotation\Mapping\IsString;
use Swoft\Validator\Annotation\Mapping\Length;
use Swoft\Validator\Annotation\Mapping\NotEmpty;
use Swoft\Validator\Annotation\Mapping\NotInEnum;
use Swoft\Validator\Annotation\Mapping\IsFloat;
use Swoft\Validator\Annotation\Mapping\Validator;

/**
 * Class CalcValidator
 * @package App\Validator
 * @Validator("CalcValidator")
 */
class CalcValidator{


    /**
     * @IsFloat
     * @NotEmpty()
     * @var
     */
    protected $price;


    /**
     * @IsFloat()
     * @NotEmpty()
     * @var
     */
    protected $hash;


    /**
     * @IsFloat()
     * @NotEmpty()
     * @var
     */
    protected $cost;

    /**
     * @IsString()
     * @NotEmpty()
     * @var
     */
    protected $current_date;

    /**
     * @IsFloat()
     * @NotEmpty()
     * @var
     */
    protected $power_per_day;

}

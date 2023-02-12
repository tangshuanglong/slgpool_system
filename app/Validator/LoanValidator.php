<?php

namespace App\Validator;

use Swoft\Validator\Annotation\Mapping\Enum;
use Swoft\Validator\Annotation\Mapping\IsString;
use Swoft\Validator\Annotation\Mapping\NotEmpty;
use Swoft\Validator\Annotation\Mapping\Validator;

/**
 * Class LoanValidator
 * @package App\Validator
 * @Validator("LoanValidator")
 */
class LoanValidator{


    /**
     * @IsString()
     * @NotEmpty()
     * @var
     */
    protected $symbol;

    /**
     * @IsString()
     * @Enum(values={"out", "in"})
     * @NotEmpty()
     * @var
     */
    protected $loan_type;

}

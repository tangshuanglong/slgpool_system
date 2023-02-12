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
 * Class ArticleValidator
 * @package App\Validator
 * @Validator("ArticleValidator")
 */
class ArticleValidator
{
    /**
     * @IsString
     * @NotEmpty()
     * @var
     */
    protected $type;

    /**
     * @isInt
     * @NotEmpty()
     * @var
     */
    protected $id;

}

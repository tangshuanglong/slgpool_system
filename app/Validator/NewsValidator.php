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
 * Class NewsValidator
 * @package App\Validator
 * @Validator("NewsValidator")
 */
class NewsValidator
{
    /**
     * @isInt
     * @NotEmpty()
     * @var
     */
    protected $id;

    /**
     * @IsString
     * @NotEmpty()
     * @var
     */
    protected $news_type;

    /**
     * @IsString
     * @NotEmpty()
     * @var
     */
    protected $type;

    /**
     * @isInt
     * @var
     */
    protected $receive_user_id;

    /**
     * @IsInt
     * @NotEmpty()
     * @var
     */
    protected $target_id;

    /**
     * @IsString()
     * @NotEmpty()
     * @Enum(values={"news", "comment"})
     * @var
     */
    protected $target_type;

    /**
     * @IsString()
     * @NotEmpty()
     * @Enum(values={"like", "not_like"})
     * @var
     */
    protected $like_action_type;

    /**
     * @IsString()
     * @NotEmpty()
     * @Enum(values={"good", "not_good", "bad", "not_bad"})
     * @var
     */
    protected $good_bad_action_type;
}

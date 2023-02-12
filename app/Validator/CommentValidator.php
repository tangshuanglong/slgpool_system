<?php

namespace App\Validator;

use Swoft\Validator\Annotation\Mapping\Email;
use Swoft\Validator\Annotation\Mapping\Enum;
use Swoft\Validator\Annotation\Mapping\IsInt;
use Swoft\Validator\Annotation\Mapping\IsString;
use Swoft\Validator\Annotation\Mapping\Length;
use Swoft\Validator\Annotation\Mapping\NotEmpty;
use Swoft\Validator\Annotation\Mapping\NotInEnum;
use Swoft\Validator\Annotation\Mapping\Max;
use Swoft\Validator\Annotation\Mapping\IsFloat;
use Swoft\Validator\Annotation\Mapping\Validator;

/**
 * Class CommentValidator
 * @package App\Validator
 * @Validator("CommentValidator")
 */
class CommentValidator
{
    /**
     * @IsInt
     * @NotEmpty()
     * @var
     */
    protected $id;

    /**
     * @IsInt
     * @var
     */
    protected $receive_user_id;

    /**
     * @IsInt
     * @var
     */
    protected $parent_comment_id;

    /**
     * @IsInt
     * @var
     */
    protected $reply_id;

    /**
     * @IsInt
     * @NotEmpty()
     * @var
     */
    protected $news_id;

    /**
     * @IsString
     * @NotEmpty(message="评论内容不能为空")
     * @Length(min=1,max=300,message="字符长度超出限制")
     * @var
     */
    protected $content;

}

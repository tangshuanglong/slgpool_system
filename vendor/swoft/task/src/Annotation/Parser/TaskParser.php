<?php declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace Swoft\Task\Annotation\Parser;

use Swoft\Annotation\Annotation\Mapping\AnnotationParser;
use Swoft\Annotation\Annotation\Parser\Parser;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Task\Annotation\Mapping\Task;
use Swoft\Task\Router\RouteRegister;

/**
 * Class TaskParser
 *
 * @since 2.0
 *
 * @AnnotationParser(annotation=Task::class)
 */
class TaskParser extends Parser
{
    /**
     * @param int  $type
     * @param Task $annotationObject
     *
     * @return array
     */
    public function parse(int $type, $annotationObject): array
    {
        RouteRegister::registerByClassName($this->className, $annotationObject->getName());

        return [$this->className, $this->className, Bean::SINGLETON, ''];
    }
}

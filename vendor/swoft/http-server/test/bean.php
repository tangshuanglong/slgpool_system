<?php declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */
return [
    'config'         => [
        'path' => __DIR__ . '/config',
    ],
    'logger'         => [
        'flushRequest' => false,
        'enable'       => false,
        'json'         => false,
    ],
    'httpDispatcher' => [
        'afterMiddlewares' => [
            \Swoft\Http\Server\Middleware\ValidatorMiddleware::class
        ]
    ],
];

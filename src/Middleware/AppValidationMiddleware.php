<?php

declare(strict_types=1);
/**
 * @link     https://51coode.com
 * @contact  https://51coode.com
 */
namespace WJaneCode\HyperfBase\Middleware;

use Hyperf\Validation\Middleware\ValidationMiddleware;
use Hyperf\Validation\UnauthorizedException;
use Psr\Http\Message\ResponseInterface;

/**
 * 参数合法性检查插件
 * Class AppValidationMiddleware.
 */
class AppValidationMiddleware extends ValidationMiddleware
{
    /**
     * 重写这个行为,不要变成响应回去,抛出异常，让异常捕捉器去处理.
     */
    protected function handleUnauthorizedException(UnauthorizedException $exception): ResponseInterface
    {
        throw $exception;
    }
}

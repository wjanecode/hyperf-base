<?php

declare(strict_types=1);
/**
 * @link     https://51coode.com
 * @contact  https://51coode.com
 */
namespace WJaneCode\HyperfBase\Exception;

use WJaneCode\HyperfBase\Constant\ErrorCode;

class HyperfBaseException extends \Exception
{
    public function __construct(int $code = 0, string $message = null, \Throwable $throwable = null)
    {
        if (is_null($message)) {
            $message = ErrorCode::getMessage($code);
        }
        parent::__construct($message, $code, $throwable);
    }
}

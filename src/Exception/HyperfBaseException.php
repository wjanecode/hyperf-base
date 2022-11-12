<?php
declare(strict_types=1);

namespace WJaneCode\HyperfBase\Exception;

use Exception;
use Throwable;
use WJaneCode\HyperfBase\Constants\ErrorCode;

class HyperfBaseException extends Exception
{
    public function __construct(string $message = null, int $code = 0, Throwable $throwable)
    {
        if (is_null($message)){
            $message = ErrorCode::getMessage($code);
        }
        parent::__construct($message,$code,$throwable);
    }

}
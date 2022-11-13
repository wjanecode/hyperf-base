<?php
declare(strict_types=1);

namespace WJaneCode\HyperfBase\Component;

use WJaneCode\HyperfBase\Constant\ErrorCode;
use WJaneCode\HyperfBase\Exception\HyperfBaseException;

/**
 * 请求第三方服务的结果，有的不是关键路径，不能抛出异常
 * 所以单独处理结果
 * Class CallResult
 */
class CallResult
{
    public int $code = 0;

    public string $message = 'ok';

    public array $data = [];

    public function __construct($code = 0, $message = 'ok', $data = [])
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }

    public static function success($data): CallResult
    {
        return new CallResult(0, "ok", $data);
    }

    public static function fail($code, $message = 'fail', $data = []): CallResult
    {
        return new CallResult($code, $message, $data);
    }

    /**
     * 是返回成功结果还是抛出异常
     * @return array
     * @throws HyperfBaseException
     */
    public function successOrFailException(): array
    {
        if (!$this->isSuccess()) {
            throw new HyperfBaseException(ErrorCode::MODULE_CALL_FAIL,"module call fail with code({$this->code}) message({$this->message})");
        }else{
            return $this->data;
        }
    }

    public function isSuccess(): bool
    {
        return $this->code == 0;
    }
}
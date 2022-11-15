<?php

declare(strict_types=1);
/**
 * @link     https://51coode.com
 * @contact  https://51coode.com
 */
namespace WJaneCode\HyperfBase\Exception\Handler;

use Hyperf\Contract\ContainerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\Server\Exception\ServerException;
use Hyperf\Validation\UnauthorizedException;
use Hyperf\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Qbhy\HyperfAuth\Exception\AuthException;
use Throwable;
use WJaneCode\HyperfBase\Constant\ErrorCode;
use WJaneCode\HyperfBase\Exception\HyperfBaseException;
use WJaneCode\HyperfBase\Log\Log;
use WJaneCode\HyperfBase\Response\Response;

class HyperfBaseExceptionHandler extends ExceptionHandler
{
    /**
     * @Inject()
     */
    protected Response $response;

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        Log::debug('exception response',$response->getHeaders(),(array)$response);
        Log::debug('response',(array)$this->response);
        $this->stopPropagation(); // 停止异常继续抛出
        Log::error('exception code: ' . $throwable->getCode());
        // 记录错误堆栈
        $trace = $throwable->getTraceAsString();
        Log::error($trace);
        Log::req($trace);

        if ($throwable instanceof HyperfBaseException) {
            Log::error('hyperf base error');
            return $this->response->fail($throwable->getCode(), $throwable->getMessage());
        }
        if ($throwable instanceof ValidationException) {
            $convertErrors = [];
            $outPutParams = [];
            foreach ($throwable->errors() as $paramName => $errors) {
                $errorMsg = $paramName . ' error description is: ' . implode('、', $errors);
                $convertErrors[] = $errorMsg;
                $outPutParams[] = $paramName;
            }
            $errorMsg = 'param validate error: ' . implode(';', $convertErrors);
            // 具体错误记录到日记
            Log::error($errorMsg);
            // 粗略错误返回前端
            $errorMsg = implode(',', $outPutParams) . '参数出现错误';
            return $this->response->fail(ErrorCode::PARAM_ERROR, $errorMsg);
        }
        if ($throwable instanceof AuthException) {
            Log::error('auth fail: ' . $throwable->getMessage());
            return $this->response->fail(ErrorCode::AUTH_FAIL, 'auth fail!');
        }
        if ($throwable instanceof UnauthorizedException) {
            $errorMsg = 'user have no permission do this action or have no token in request!';
            Log::error($errorMsg);
            return $this->response->fail(ErrorCode::PERMISSION_ERROR, $errorMsg);
        }
        if ($throwable instanceof ServerException) {
            Log::error('server exception did get');
            return $this->response->fail(ErrorCode::SERVER_ERROR, $throwable->getMessage());
        }

        if ($throwable instanceof HttpException) {
            $code = $throwable->getStatusCode();
            $errorMsg = $throwable->getMessage();
        } else {
            $code = ErrorCode::SERVER_ERROR;
            $originErrorMsg = $throwable->getMessage();
            $originErrorCode = $throwable->getCode();
            Log::error('origin error code: ' . $originErrorCode . ' origin error message: ' . $originErrorMsg);
            $errorMsg = 'Server got an bad internal error!';
        }

        // 打印致命错误信息
        $logMsg = 'throw exception with code: ' . $code . ' detail: ' . $errorMsg;
        Log::error($logMsg);
        Log::req($logMsg);

        return $this->response->fail($code, $errorMsg);
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}

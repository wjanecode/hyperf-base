<?php
declare(strict_types=1);

namespace WJaneCode\HyperfBase\Task;

use Hyperf\Utils\ApplicationContext;
use WJaneCode\HyperfBase\Log\Log;
use WJaneCode\HyperfBase\Service\LogService;

/**
 * 定时清除过期日记文件
 */
class ClearLogFileTask
{
    public function execute()
    {
        $service = ApplicationContext::getContainer()->get(LogService::class);
        try {
            $service->clearExpireLog();
        }catch (\Throwable $exception) {
            $code = $exception->getCode();
            $message = $exception->getMessage();
            $backTrace = $exception->getTraceAsString();
            Log::task("clear log task exception:$code message:$message");
            Log::task($backTrace);
        }
    }

}
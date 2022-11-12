<?php
declare(strict_types=1);

namespace WJaneCode\HyperfBase\Task;

use Hyperf\Utils\ApplicationContext;
use WJaneCode\HyperfBase\Service\CaptchaService;

/**
 * crontab 定时清除过期的验证文件
 */
class ClearExpireCaptchaTask
{
    public function execute()
    {
        $captchaService = ApplicationContext::getContainer()->get(CaptchaService::class);
        $captchaService->clearExpireCaptcha();
    }
}
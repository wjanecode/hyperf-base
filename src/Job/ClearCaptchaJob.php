<?php
declare(strict_types=1);

namespace WJaneCode\HyperfBase\Job;

use Hyperf\AsyncQueue\Job;
use Hyperf\Utils\ApplicationContext;
use WJaneCode\HyperfBase\Log\Log;
use WJaneCode\HyperfBase\Service\CaptchaService;

/**
 * 异步清理验证码
 */
class ClearCaptchaJob extends Job
{
    private string $cacheKey;

    public function __construct(string $cacheKey)
    {
        $this->cacheKey = $cacheKey;
    }

    /**
     * @return void
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function handle()
    {
        $captchaService = ApplicationContext::getContainer()->get(CaptchaService::class);
        $captchaService->remove($this->cacheKey);
        Log::info("async clear captcha success with key:".$this->cacheKey);
    }
}
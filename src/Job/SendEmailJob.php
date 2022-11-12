<?php
declare(strict_types=1);

namespace WJaneCode\HyperfBase\Job;

use Hyperf\Utils\ApplicationContext;
use WJaneCode\HyperfBase\Entity\EmailEntity;
use WJaneCode\HyperfBase\Log\Log;
use WJaneCode\HyperfBase\Service\EmailService;

class SendEmailJob
{
    private EmailEntity $emailEntity;

    /**
     * 最大重试次数
     */
    protected int $maxAttempts = 3;

    public function __construct(EmailEntity $emailEntity)
    {
        $this->emailEntity = $emailEntity;
    }

    /**
     * @inheritDoc
     */
    public function handle()
    {
        Log::info("begin process send email task:".json_encode($this->emailEntity));
        $service = ApplicationContext::getContainer()->get(EmailService::class);
        $service->sendEmail($this->emailEntity);
        Log::info("async success send email:".json_encode($this->emailEntity));
    }
}
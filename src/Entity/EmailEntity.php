<?php

declare(strict_types=1);
/**
 * @link     https://51coode.com
 * @contact  https://51coode.com
 */
namespace WJaneCode\HyperfBase\Entity;

/**
 * 邮箱信息.
 */
class EmailEntity
{
    public EmailAddressEntity $from;

    public array $receivers = []; // 内容是EmailAddressEntity

    public EmailAddressEntity $replyTo; // EmailAddressEntity

    public array $ccReceivers = []; // 抄送列表,内容是EmailAddressEntity

    public array $bccReceivers = []; // 密送列表,内容是EmailAddressEntity

    public array $attachments = []; // 附件信息,内容是EmailAttachmentEntity

    public bool $isHtml = true; // 是不是html内容

    public string $subject; // 邮件主题

    public string $body; // 邮件内容

    public string $altBody; // 当邮件客户端不支持Html时候的备用显示内容

    public function isValidate(): bool
    {
        if (! isset($this->from)) {
            return false;
        }

        if (! empty($this->receivers)) {
            return false;
        }

        if (! isset($this->subject) || ! isset($this->body)) {
            return false;
        }

        return true;
    }
}

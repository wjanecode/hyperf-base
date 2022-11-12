<?php
declare(strict_types=1);

namespace WJaneCode\HyperfBase\Service;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use WJaneCode\HyperfBase\Entity\EmailAddressEntity;
use WJaneCode\HyperfBase\Entity\EmailAttachmentEntity;
use WJaneCode\HyperfBase\Entity\EmailEntity;
use WJaneCode\HyperfBase\Log\Log;

class EmailService
{
    protected function mailer(): PHPMailer
    {
        $mail = new PHPMailer(true);
        $config = config('hyperf-common.mail.smtp');
        $mail->SMTPDebug = SMTP::DEBUG_OFF;                      // Enable verbose debug output
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = $config['host'];                    // Set the SMTP server to send through
        $mail->SMTPAuth   = $config['auth'];                                   // Enable SMTP authentication
        $mail->Username   = $config['username'];                     // SMTP username
        $mail->Password   = $config['password'];                               // SMTP password
        $mail->SMTPSecure = $config['secure'];         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port       = $config['port'];
        return $mail;
    }

    /**
     * 发送邮件，是直接发，还是在异步任务内发
     * 可以将相应的日志写入对应的日志文件
     * @param EmailEntity $emailEntity
     * @param bool $isInTask
     * @return bool
     */
    public function sendEmail(EmailEntity $emailEntity, bool $isInTask = true): bool
    {
        $logger = Log::logger('task');
        if (!$isInTask) {
            $logger = Log::logger('default');
        }

        if (!$emailEntity || !$emailEntity->isValidate()) {
            $logger->error("email entry is not validate to send!");
            return false;
        }

        $mail = $this->mailer();
        try{
            $mail->setFrom($emailEntity->from->address, $emailEntity->from->name);
            if (!empty($emailEntity->receivers)) {
                array_map(function (EmailAddressEntity $address) use ($mail) {
                    $mail->addAddress($address->address,$address->name);
                }, $emailEntity->receivers);
            }
            if (isset($emailEntity->replyTo)) {
                $mail->addReplyTo($emailEntity->replyTo->address,$emailEntity->replyTo->name);
            }
            if (isset($emailEntity->ccReceivers)) {
                array_map(function (EmailAddressEntity $address) use ($mail) {
                    $mail->addCC($address->address,$address->name);
                }, $emailEntity->ccReceivers);
            }
            if (isset($emailEntity->bccReceivers)) {
                array_map(function (EmailAddressEntity $address) use ($mail) {
                    $mail->addBCC($address->address,$address->name);
                }, $emailEntity->bccReceivers);
            }
            if (isset($emailEntity->attachments)) {
                array_map(function (EmailAttachmentEntity $attachment) use ($mail) {
                    $mail->addAttachment($attachment->path, $attachment->name);
                }, $emailEntity->attachments);
            }
            $mail->isHTML($emailEntity->isHtml);
            $mail->Subject = $emailEntity->subject;
            $mail->Body = $emailEntity->body;
            if (isset($emailEntity->altBody)) {
                $mail->AltBody = $emailEntity->altBody;
            }
            $logger->info("did send email with info:".json_encode($emailEntity));
            return $mail->send();
        }catch (Exception $exception) {
            $code = $exception->getCode();
            $message = $exception->getMessage();
            $trace = $exception->getTraceAsString();
            $logger->error("send email error code:$code message:$message");
            $logger->error($trace);
            return false;
        }
    }
}
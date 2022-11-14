<?php

declare(strict_types=1);
/**
 * @link     https://51coode.com
 * @contact  https://51coode.com
 */
namespace WJaneCode\HyperfBase\Entity;

/**
 * 附件.
 */
class EmailAttachmentEntity
{
    public string $path;

    public string $name = '';

    public function __construct(string $path, string $name)
    {
        $this->path = $path;
        $this->name = $name;
    }
}

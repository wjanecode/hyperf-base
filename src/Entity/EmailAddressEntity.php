<?php

declare(strict_types=1);
/**
 * @link     https://51coode.com
 * @contact  https://51coode.com
 */
namespace WJaneCode\HyperfBase\Entity;

/**
 * 邮箱地址
 */
class EmailAddressEntity
{
    public string $address;

    /**
     * 地址对应的名字.
     */
    public string $name;

    public function __construct(string $address, string $name)
    {
        $this->address = $address;
        $this->name = $name;
    }
}

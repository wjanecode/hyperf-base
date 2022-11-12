<?php
declare(strict_types=1);

namespace WJaneCode\HyperfBase\Entity;

/**
 * 邮箱地址
 */
class EmailAddressEntity
{
    /**
     * @var string
     */
    public string $address;

    /**
     * 地址对应的名字
     * @var string
     */
    public string $name;

    public function __construct(string $address, string $name)
    {
        $this->address = $address;
        $this->name = $name;
    }
}
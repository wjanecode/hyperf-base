<?php
declare(strict_types=1);

namespace WJaneCode\HyperfBase\Cache;
use Hyperf\Cache\Cache as HyperfCache;

/**
 * 替换框架原来的Cache，将按照前缀清理缓存的方法暴露出来
 */
class Cache extends HyperfCache
{
    /**
     * 按照前缀清除缓存
     * @param string $prefix
     * @return bool
     */
    public function clearPrefix(string $prefix):bool
    {
        return $this->driver->clearPrefix($prefix);
    }
}
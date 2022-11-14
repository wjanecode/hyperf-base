<?php

declare(strict_types=1);
/**
 * @link     https://51coode.com
 * @contact  https://51coode.com
 */
namespace WJaneCode\HyperfBase\Service;

use Hyperf\Di\Annotation\Inject;
use Redis;

/**
 * redis 锁 服务
 */
class LockService
{
    /**
     * @Inject
     */
    protected \Redis $redis;

    /**
     * @param mixed $key
     * @param mixed $callback
     * @throws \Exception
     */
    public function run($key, $callback)
    {
        $flag = $this->lock($key);
        if (! $flag) {
            throw new \Exception('不能重复提交');
        }
        try {
            return $callback();
        } catch (\Exception $e) {
            throw $e;
        } finally {
            // 运行不管成不成功，最后释放锁
            $this->unlock($key);
        }
    }

    public function lock(string $key, int $expire = 3): bool
    {
        return $this->redis->set($key, 1, ['NX', 'EX' => $expire]);
    }

    public function unlock(string $key)
    {
        $this->redis->del($key);
    }
}

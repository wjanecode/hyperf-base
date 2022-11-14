<?php

declare(strict_types=1);
/**
 * @link     https://51coode.com
 * @contact  https://51coode.com
 */
namespace WJaneCode\HyperfBase\Job;

use Hyperf\AsyncQueue\Job;
use Hyperf\Utils\ApplicationContext;
use Psr\SimpleCache\CacheInterface;
use WJaneCode\HyperfBase\Cache\Cache;
use WJaneCode\HyperfBase\Log\Log;

/**
 * 按照缓存key前缀进行缓存清理的任务
 * Class ClearPrefixCacheJob.
 */
class ClearPrefixCacheJob extends Job
{
    /**
     * 缓存的前缀
     */
    private string $prefix;

    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * 异步任务执行的时候，调用系统的Cache组件清理缓存.
     */
    public function handle()
    {
        $cache = ApplicationContext::getContainer()->get(CacheInterface::class);
        if ($cache instanceof Cache) {
            Log::info('clear prefix cache async!');
            $cache->clearPrefix($this->prefix);
        }
    }
}

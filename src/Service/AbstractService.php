<?php

declare(strict_types=1);
/**
 * @link     https://51coode.com
 * @contact  https://51coode.com
 */
namespace WJaneCode\HyperfBase\Service;

use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Driver\DriverInterface;
use Hyperf\AsyncQueue\Job;
use Hyperf\Cache\Listener\DeleteListenerEvent;
use Hyperf\Contract\SessionInterface;
use Hyperf\Filesystem\FilesystemFactory;
use League\Flysystem\Filesystem;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\SimpleCache\CacheInterface;
use Qbhy\HyperfAuth\Authenticatable;
use Qbhy\HyperfAuth\AuthManager;
use WJaneCode\HyperfBase\Entity\EmailEntity;
use WJaneCode\HyperfBase\Job\ClearListCacheJob;
use WJaneCode\HyperfBase\Job\ClearPrefixCacheJob;
use WJaneCode\HyperfBase\Job\SendEmailJob;
use WJaneCode\HyperfBase\Log\Log;

abstract class AbstractService
{
    protected ContainerInterface $container;

    protected AuthManager $auth;

    protected CacheInterface $cache;

    protected SessionInterface $session;

    protected EventDispatcherInterface $eventDispatcher;

    protected DriverInterface $driver;

    protected DriverFactory $driverFactory;

    protected FilesystemFactory $fileSystemFactory;

    protected EmailService $emailService;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->auth = $container->get(AuthManager::class);
        $this->session = $container->get(SessionInterface::class);
        $this->cache = $container->get(CacheInterface::class);
        $this->eventDispatcher = $container->get(EventDispatcherInterface::class);
        $this->driverFactory = $container->get(DriverFactory::class);
        $this->driver = $this->driverFactory->get('default');
        $this->fileSystemFactory = $container->get(FilesystemFactory::class);
        $this->emailService = $container->get(EmailService::class);
    }

    /**
     * 选择指定分组的队列来执行某个异步任务
     */
    protected function pushWithGroup(string $group, Job $job, int $delay = 0)
    {
        $driver = $this->driverFactory->get($group);
        if (! $driver) {
            Log::error("drive:{$group} is not exist!");
            return;
        }
        $driver->push($job, $delay);
    }

    /**
     * 使用默认分组default队列来执行任务
     */
    protected function push(Job $job, int $delay = 0)
    {
        $this->driver->push($job, $delay);
    }

    /**
     * 分发事件.
     */
    protected function dispatch(object $event)
    {
        $this->eventDispatcher->dispatch($event);
    }

    protected function fileLocal()
    {
        return $this->fileSystemFactory->get('local');
    }

    protected function fileQiniu(): Filesystem
    {
        return $this->fileSystemFactory->get('qiniu');
    }

    protected function fileQiniuAdapter()
    {
        $options = config('file');
        return $this->fileSystemFactory->getAdapter($options, 'qiniu');
    }

    protected function userId()
    {
        return $this->user()->getId();
    }

    protected function user(): Authenticatable
    {
        return $this->auth->user();
    }

    protected function success($data = [])
    {
        return $data;
    }

    protected function clearAllCache()
    {
        return $this->cache->clear();
    }

    protected function sendEmail(EmailEntity $emailEntity)
    {
        $this->emailService->sendEmail($emailEntity, false);
    }

    protected function asyncSendEmail(EmailEntity $emailEntity)
    {
        $this->push(new SendEmailJob($emailEntity));
    }

    /**
     * 清除缓存.
     */
    protected function clearCache(string $listener, array $arguments)
    {
        $deleteEvent = new DeleteListenerEvent($listener, $arguments);
        $this->dispatch($deleteEvent);
    }

    /**
     * 通过异步任务清除缓存.
     */
    protected function clearCachePrefix(string $prefix)
    {
        $this->push(new ClearPrefixCacheJob($prefix));
    }

    /**
     * 设计的缓存列表接口参数形式必须保证为(int $pageIndex, int $pageSize, ...$customValues)
     * 否则无法通过这种形式删除列表类型的缓存.
     */
    protected function clearListCacheWithMaxPage(string $listener, array $customValues, int $pageSize, int $maxPageCount = 15)
    {
        $this->push(new ClearListCacheJob($listener, $customValues, $pageSize, $maxPageCount));
    }
}

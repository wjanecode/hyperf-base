<?php

declare(strict_types=1);
/**
 * @link     https://51coode.com
 * @contact  https://51coode.com
 */
namespace WJaneCode\HyperfBase\Service;

use Carbon\Carbon;
use Gregwar\Captcha\CaptchaBuilder;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Driver\DriverInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Str;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use WJaneCode\HyperfBase\Common\PublicFile;
use WJaneCode\HyperfBase\Constant\ErrorCode;
use WJaneCode\HyperfBase\Exception\HyperfBaseException;
use WJaneCode\HyperfBase\Job\ClearCaptchaJob;
use WJaneCode\HyperfBase\Log\Log;

/**
 * 验证码服务
 */
class CaptchaService
{
    public const DIR_NAME_CURRENT = '.';

    public const DIR_NAME_LAST_LEVEL = '..';

    /**
     * @Inject
     */
    protected DriverFactory $driverFactory;

    /**
     * @Inject
     */
    private PublicFile $publicFile;

    /**
     * @Inject
     */
    private CacheInterface $cache;

    public function subDirPath($cacheKey): ?string
    {
        $result = $this->publicFile->createPublicSubDirIfNotExist($this->saveDir());
        if (! $result) {
            return null;
        }
        return $this->saveDir() . $cacheKey . '.jpeg';
    }

    public function savePath($cacheKey): ?string
    {
        return $this->publicFile->publicPath($this->subDirPath($cacheKey));
    }

    /**
     * 获取一张验证码图片和信息.
     * @return string[]
     * @throws InvalidArgumentException
     */
    public function get(): array
    {
        $builder = new CaptchaBuilder();
        $builder->build();
        $phrase = $builder->getPhrase();
        $time = Carbon::now()->timestamp;
        $cacheKey = $this->prefix() . $time;
        $subDirPath = $this->subDirPath($cacheKey);
        $savePath = $this->savePath($cacheKey);
        $builder->save($savePath);
        $this->cache->set($cacheKey, $phrase, $this->ttl());
        $urlPrefix = config('hyperf-common.upload.local.url_prefix');
        $urlPrefix = rtrim($urlPrefix, '/');
        return [
            'url' => $urlPrefix . DIRECTORY_SEPARATOR . ltrim($subDirPath, '/'),
            'key' => $cacheKey,
        ];
    }

    /**
     * 校验提交的验证码是否正确.
     * @throws InvalidArgumentException
     * @throws HyperfBaseException
     */
    public function validate(string $cacheKey, string $input): bool
    {
        $phrase = $this->cache->get($cacheKey);
        if (is_null($phrase)) {
            $this->asyncClear($cacheKey);
            throw new HyperfBaseException(ErrorCode::SYSTEM_ERROR_CAPTCHA_EXPIRED);
        }

        Log::info("input:{$input} phrase:{$phrase}");

        $isStrictMode = config('hyperf-common.captcha.strict');

        if ($isStrictMode && $phrase !== $input) {
            throw new HyperfBaseException(ErrorCode::SYSTEM_ERROR_CAPTCHA_INVALIDATE);
        }

        if (Str::lower($phrase) !== Str::lower($input)) {
            throw new HyperfBaseException(ErrorCode::SYSTEM_ERROR_CAPTCHA_INVALIDATE);
        }

        $this->asyncClear($cacheKey);

        return true;
    }

    /**
     * 异步清除指定的验证码信息.
     */
    public function asyncClear(string $cacheKey)
    {
        $this->driver()->push(new ClearCaptchaJob($cacheKey));
    }

    /**
     * 删除验证码图片.
     * @throws InvalidArgumentException
     */
    public function remove(string $cacheKey)
    {
        $savePath = $this->subDirPath($cacheKey);
        $this->publicFile->deletePublicPath($savePath);
        $this->cache->delete($cacheKey);
    }

    /**
     * 刷新验证码
     * @return string[]
     * @throws InvalidArgumentException
     */
    public function refresh(string $cacheKey = null): array
    {
        if (! isset($cacheKey)) {
            return $this->get();
        }
        $phrase = $this->cache->get($cacheKey);
        if (! isset($phrase)) {
            return $this->get();
        }
        $this->asyncClear($cacheKey);
        return $this->get();
    }

    /**
     * 清理过期的验证码图片和缓存
     * 通常都在异步任务里面执行.
     */
    public function clearExpireCaptcha()
    {
        $files = scandir($this->publicFile->publicPath($this->saveDir()));
        if (empty($files)) {
            Log::task('no captcha file to check expire!');
            return;
        }
        Log::task('will check captcha files:' . json_encode($files));

        $expireKeys = [];
        array_map(function (string $filename) use (&$expireKeys) {
            if ($filename == self::DIR_NAME_CURRENT || $filename == self::DIR_NAME_LAST_LEVEL) {
                Log::task("no need deal system file name :{$filename}");
                return;
            }
            $name = Arr::first(explode('.', $filename));
            $timestamp = Str::after($name, $this->prefix());
            $date = Carbon::createFromTimestamp($timestamp);
            Log::task('get an captcha file time:' . $date->toString());
            $secondsDidPass = Carbon::now()->diffInRealSeconds($date);
            Log::task("{$filename} has been created {$secondsDidPass} seconds");
            if ($secondsDidPass > $this->ttl()) {
                $expireKeys[] = $timestamp;
            }
        }, $files);
        Log::task('will clear expire captcha keys:' . json_encode($expireKeys));

        array_map(function (string $expireKey) {
            $cacheKey = $this->prefix() . $expireKey;
            $this->remove($cacheKey);
        }, $expireKeys);
        Log::task('success clear expire captcha!');
    }

    protected function driver(): DriverInterface
    {
        return $this->driverFactory->get('default');
    }

    protected function saveDir()
    {
        return $this->dirname() . DIRECTORY_SEPARATOR;
    }

    private function ttl()
    {
        return config('hyperf-common.captcha.ttl');
    }

    private function prefix()
    {
        return config('hyperf-common.captcha.prefix');
    }

    private function dirname()
    {
        return config('hyperf-common.captcha.dirname');
    }
}

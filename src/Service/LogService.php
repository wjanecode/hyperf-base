<?php

declare(strict_types=1);
/**
 * @link     https://51coode.com
 * @contact  https://51coode.com
 */
namespace WJaneCode\HyperfBase\Service;

use Carbon\Carbon;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Filesystem\FilesystemFactory;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Str;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use WJaneCode\HyperfBase\Log\Log;

/**
 * 清理日志文件时用.
 */
class LogService
{
    public const KEEP_LOG_FILE_FOREVER = -1;

    /**
     * @Inject
     */
    protected FilesystemFactory $fileSystem;

    /**
     * 自动清理过期的日志文件.
     * @throws FilesystemException
     */
    public function clearExpireLog()
    {
        if ($this->isKeepForever()) {
            Log::task('no need to clear log because setting forever');
            return;
        }

        if (! $this->hasLog()) {
            Log::task('has no log files to check clear');
            return;
        }
        $items = $this->local()->listContents('/logs')->toArray();
        Log::task('start deal with log files:' . json_encode($items));

        $clearPaths = [];
        array_map(function (array $file) use (&$clearPaths) {
            $path = Arr::get($file, 'path');
            if (Str::endsWith($path, '.log') == false) {
                Log::info("not a log file:{$path}");
                return;
            }
            $timestamp = Arr::get($file, 'timestamp');
            $lastDate = Carbon::createFromTimestamp($timestamp);
            $daysDidPass = Carbon::now()->floatDiffInRealDays($lastDate);
            Log::task("{$path} last modify time has been over {$daysDidPass} days");
            if ($daysDidPass > $this->keepFileDays()) {
                Log::task("{$path} need to be clear!");
                $clearPaths[] = $path;
            }
        }, $items);

        Log::task('will clear this log paths:' . json_encode($clearPaths));

        array_map(function ($path) {
            $this->local()->delete($path);
        }, $clearPaths);

        Log::task('success clear expire log files!');
    }

    protected function keepFileDays()
    {
        return config('hyperf-base.clear_log.days');
    }

    protected function isKeepForever(): bool
    {
        return $this->keepFileDays() == self::KEEP_LOG_FILE_FOREVER;
    }

    protected function local(): Filesystem
    {
        return $this->fileSystem->get('local');
    }

    /**
     * @throws FilesystemException
     */
    protected function hasLog(): bool
    {
        $hasDir = $this->local()->fileExists('/logs');
        if (! $hasDir) {
            return false;
        }
        $items = collect($this->local()->listContents('/logs'));
        $items->filter(function (array $item) {
            $systemFiles = ['.', '..'];
            return ! in_array($item['filename'], $systemFiles);
        });
        if ($items->isEmpty()) {
            return false;
        }
        return true;
    }
}

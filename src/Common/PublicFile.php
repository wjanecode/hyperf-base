<?php
declare(strict_types=1);

namespace WJaneCode\HyperfBase\Common;

/**
 * 公开目录的一些文件操作行为的封装
 */
class PublicFile
{
    public function publicRootPath()
    {
        return config('server.settings.document_root');
    }

    public function createPublicDirIfNotExist(): bool
    {
        $publicDir = $this->publicRootPath();
        if (file_exists($publicDir)) {
            if (!is_dir($publicDir)) {
                return false;
            }
            return true;
        }
        return mkdir($publicDir, 0755, true);
    }

    public function createPublicSubDirIfNotExist(string $subDir): bool
    {
        $subDirPath = $this->publicPath($subDir);
        if (is_null($subDirPath)) {
            return false;
        }
        if (file_exists($subDirPath)){
            if (is_dir($subDirPath)) {
                return true;
            }
            return false;
        }
        return mkdir($subDirPath, 0755, true);
    }

    public function publicPath(string $subPath): ?string
    {
        $result = $this->createPublicDirIfNotExist();
        if (!$result) {
            return null;
        }
        return $this->publicRootPath().$subPath;
    }

    public function deletePublicPath(string $subPath): bool
    {
        $fullPath = $this->publicPath($subPath);
        if (!file_exists($fullPath)) {
            return true;
        }
        if (is_dir($fullPath)) {
            return rmdir($fullPath);
        }
        return unlink($fullPath);
    }
}
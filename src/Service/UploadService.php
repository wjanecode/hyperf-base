<?php
declare(strict_types=1);

namespace WJaneCode\HyperfBase\Service;

use Carbon\Carbon;
use Hyperf\HttpMessage\Upload\UploadedFile;
use League\Flysystem\FilesystemException;
use Overtrue\Flysystem\Qiniu\QiniuAdapter;
use Qiniu\Auth;
use WJaneCode\HyperfBase\Constant\ErrorCode;
use WJaneCode\HyperfBase\Exception\HyperfBaseException;

class UploadService extends AbstractService
{
    public function getQiniuImageUploadToken(string $fileKey): array
    {
        $policy = [
            'insertOnly' => true,
            'mimeLimit' => 'image/*',
        ];
        return $this->getQiniuCommonUploadToken($fileKey, $policy);
    }

    public function getQiniuCommonUploadToken(string $fileKey, array $policy = null): array
    {
        $accessKey = config('file.qiniu.accessKey');
        $secretKey = config('file.qiniu.secretKey');
        $bucket = config('file.qiniu.bucket');
        if (empty($accessKey) || empty($secretKey) || empty($bucket)) {
            throw new HyperfBaseException(ErrorCode::SYSTEM_ERROR_QINIU_UPLOAD_CONFIG_NOT_SET);
        }
        $auth = new Auth($accessKey, $secretKey);
        $ttl = config('hyperf-common.upload.qiniu.token_ttl', 3600);
        $token = $auth->uploadToken($bucket, $fileKey, $ttl, $policy);
        return ['token' => $token];
    }

    /**
     * @throws HyperfBaseException
     * @throws FilesystemException
     */
    public function uploadLocalFileToQiniu(UploadedFile $file)
    {
        $stream = fopen($file->getRealPath(), 'r+');
        $fileName = Carbon::now()->getTimestamp().'.'.$file->getExtension();
        $this->fileQiniu()->writeStream($fileName, $stream);
        fclose($stream);
        $adapter = $this->fileQiniuAdapter();
        if ($adapter instanceof QiniuAdapter) {
            return $adapter->getUrl($fileName);
        }else{
            throw new HyperfBaseException(ErrorCode::BUSINESS_CODE_NOT_SUCCESS);
        }
    }
}
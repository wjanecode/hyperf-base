<?php

declare(strict_types=1);
/**
 * @link     https://51coode.com
 * @contact  https://51coode.com
 */
namespace WJaneCode\HyperfBase\Controller;

use Hyperf\HttpMessage\Upload\UploadedFile;
use Hyperf\Utils\Str;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use WJaneCode\HyperfBase\Common\PublicFile;
use WJaneCode\HyperfBase\Constant\ErrorCode;
use WJaneCode\HyperfBase\Exception\HyperfBaseException;
use WJaneCode\HyperfBase\Request\Request;
use WJaneCode\HyperfBase\Response\Response;
use WJaneCode\HyperfBase\Service\CaptchaService;

/**
 * 控制器基类，集成请求的验证
 * 请求类型的识别等基础功能
 * 文件上传请求的相关处理
 * Class AbstractController.
 */
abstract class AbstractController
{
    protected ContainerInterface $container;

    protected Request $request;

    protected Response $response;

    protected ValidatorFactoryInterface $validatorFactory;

    protected PublicFile $publicFileService;

    protected CaptchaService $captchaService;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->request = $container->get(Request::class);
        $this->response = $container->get(Response::class);
        $this->validatorFactory = $container->get(ValidatorFactoryInterface::class);
        $this->publicFileService = $container->get(PublicFile::class);
        $this->captchaService = $container->get(CaptchaService::class);
    }

    /**
     * 指定规则检测.
     * @throws HyperfBaseException
     */
    public function validate($rules): array
    {
        $validator = $this->validatorFactory->make($this->request->getParams(), $rules);
        $validator->validate();
        if ($validator->fails()) {
            $errorMsg = $validator->errors()->first();
            throw new HyperfBaseException(ErrorCode::PARAM_ERROR, $errorMsg);
        }
        return $validator->validated();
    }

    /**
     * 通用的验证码校验逻辑,如果该接口需要验证验证码，应该固定在参数里面传递
     *  'captcha' => [
     *      'key' => 'xxxx',
     *      'code' => 'xxx'
     *  ].
     * @throws HyperfBaseException
     */
    protected function validateCaptcha()
    {
        $this->validate([
            'captcha.key' => 'string|required|min:1',
            'captcha.code' => 'string|required|min:1',
        ]);
        // 先校验验证码是否正确
        $key = $this->request->param('captcha.key');
        $code = $this->request->param('captcha.code');
        $this->captchaService->validate($key, $code);
    }

    /**
     * 获取当前请求用户的ID
     * 这个是通过Auth的token反查回来的.
     * @return mixed
     */
    protected function getUserId()
    {
        return $this->request->getUserId();
    }

    /**
     * 返回成功响应.
     */
    protected function success(array $result = []): ResponseInterface
    {
        return $this->response->success($result);
    }

    /**
     * 返回微信格式的成功响应.
     */
    protected function weChatSuccess(string $result = ''): ResponseInterface
    {
        return $this->response->toWeChatXml($result);
    }

    /**
     * 获取请求中的文件信息.
     * @return null|UploadedFile|UploadedFile[]
     */
    protected function file(string $fileName)
    {
        return $this->request->file($fileName);
    }

    /**
     * 请求中是否包含指定的文件信息.
     */
    protected function hasFile(string $fileName): bool
    {
        return $this->request->hasFile($fileName);
    }

    /**
     * 请求中的文件是否合法.
     */
    protected function isFileValid(string $fileName): bool
    {
        return $this->file($fileName)->isValid();
    }

    /**
     * 上传文件的临时路径.
     */
    protected function fileTmpPath(string $fileName): string
    {
        return $this->file($fileName)->getPath();
    }

    /**
     * 上传文件的扩展名.
     */
    protected function fileExtension(string $fileName): ?string
    {
        return $this->file($fileName)->getExtension();
    }

    /**
     * 将上传文件从临时目录移动到指定目录完成上传.
     */
    protected function moveFile(string $fileName, string $destination): bool
    {
        $file = $this->file($fileName);
        $file->moveTo($destination);
        $isMoved = $file->isMoved();
        if (! $isMoved) {
            return false;
        }
        return chmod($destination, 0744);
    }

    /**
     * 获取服务的公开可访问目录路径.
     * @return mixed
     */
    protected function publicRootPath()
    {
        return $this->publicFileService->publicRootPath();
    }

    /**
     * 如果公开目录不存在则创建出来.
     */
    protected function createPublicDirIfNotExist(): bool
    {
        return $this->publicFileService->createPublicDirIfNotExist();
    }

    /**
     * 在公开目录下面创建一个子目录.
     */
    protected function createPublicSubDirIfNotExist(string $subDir): bool
    {
        return $this->publicFileService->createPublicSubDirIfNotExist($subDir);
    }

    /**
     * 获取一个基于公开目录的子目录路径.
     */
    protected function publicPath(string $subPath): ?string
    {
        return $this->publicFileService->publicPath($subPath);
    }

    /**
     * 删除公开目录下的一个子目录.
     */
    protected function deletePublicPath(string $subPath): bool
    {
        return $this->publicFileService->deletePublicPath($subPath);
    }

    /**
     * 把指定文件移动到公开目录下指定的子目录.
     * @param bool $autoCreateDir
     */
    protected function moveFileToPublic(string $fileName, string $subDir = null, string $fileRename = null, $autoCreateDir = true): bool
    {
        if (! isset($fileRename)) {
            $fileRename = Str::random(6);
        }
        if (! isset($subDir)) {
            if ($autoCreateDir) {
                $result = $this->createPublicDirIfNotExist();
                if (! $result) {
                    return false;
                }
            }
            $destination = $this->publicRootPath() . DIRECTORY_SEPARATOR . $fileRename;
        } else {
            if ($autoCreateDir) {
                $result = $this->createPublicSubDirIfNotExist($subDir);
                if (! $result) {
                    return false;
                }
            }
            $destination = $this->publicPath($subDir) . DIRECTORY_SEPARATOR . $fileRename;
        }
        return $this->moveFile($fileName, $destination);
    }
}

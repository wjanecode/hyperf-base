<?php
declare(strict_types=1);
namespace WJaneCode\HyperfBase\Request;

use Hyperf\Contract\ContainerInterface;
use Hyperf\HttpMessage\Upload\UploadedFile;
use Hyperf\Utils\Arr;
use Hyperf\Validation\Request\FormRequest;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Qbhy\HyperfAuth\AuthManager;
use WJaneCode\HyperfBase\Constant\Constants;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\HeaderBag;
/**
 * 普通请求的封装
 * 可以实现按照请求规则的检查
 * Class Request
 */
class Request extends FormRequest
{
    protected AuthManager $auth;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->auth = $this->container->get(AuthManager::class);
    }

    /**
     * 是否ZGW协议
     * @return bool
     */
    public function isWgw(): bool
    {
        return !empty($this->getHeaderLine(Constants::WGW));
    }

    /**
     * 是不是上传请求
     * @return bool
     */
    public function isUpload(): bool
    {
        return !empty($this->getHeaderLine(Constants::UPLOAD_SYSTEM_TYPE_LOCAL));
    }

    /**
     * 获取请求参数
     * @return array|mixed
     */
    public function getParams()
    {
        if (!$this->isMethod('POST')) {
            return  $this->getQueryParams();
        }
        if ($this->isZgw()) {
            return $this->post("interface.param");
        }
        return $this->post();
    }

    /**
     * 是否有传某个参数
     * @param string $key
     * @return bool
     */
    public function hasParam(string $key): bool
    {
        return Arr::has($this->getParams(), $key);
    }

    /**
     * 取参数
     * @param string $key
     * @param null $default
     * @return mixed|null
     */
    public function param(string $key, $default = null)
    {
        if (!$this->hasParam($key)) {
            return $default;
        }
        return Arr::get($this->getParams(), $key);
    }

    public function getUserId()
    {
        return $this->auth->user()->getId();
    }

    public function getToken()
    {
        return $this->input("token");
    }

    public function isLogin(): bool
    {
        return $this->auth->check();
    }

    /**
     * 需要重写
     * @return bool
     */
    protected function isAdmin(): bool
    {
        return  false;
    }

    /**
     * 根据协议修改验证内容
     * @return array
     */
    protected function validationData(): array
    {
        return  $this->getParams();
    }

    /**
     * 将请求转成easyWeChat的请求
     */
    public function easyWeChatRequest(): SymfonyRequest
    {
        $get = $this->getQueryParams();
        $post = $this->getParsedBody();
        $cookie = $this->getCookieParams();
        $uploadFiles = $this->getUploadedFiles() ?? [];
        $server = $this->getServerParams();
        $xml = $this->getBody()->getContents();
        $files = [];
        /** @var UploadedFile $v */
        foreach ($uploadFiles as $k => $v) {
            $files[$k] = $v->toArray();
        }
        $request = new SymfonyRequest($get, $post, [], $cookie, $files, $server, $xml);
        $request->headers = new HeaderBag($this->getHeaders());
        return $request;
    }
}
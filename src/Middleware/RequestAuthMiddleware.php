<?php

declare(strict_types=1);
/**
 * @link     https://51coode.com
 * @contact  https://51coode.com
 */
namespace WJaneCode\HyperfBase\Middleware;
use Carbon\Carbon;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Utils\Arr;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use WJaneCode\HyperfBase\Constant\Constants;
use WJaneCode\HyperfBase\Constant\ErrorCode;
use WJaneCode\HyperfBase\Exception\HyperfBaseException;
use WJaneCode\HyperfBase\Log\Log;

/**
 * 请求签名鉴权插件
 * Class RequestAuthMiddleware
 */
class RequestAuthMiddleware implements MiddlewareInterface
{
    private $appIdSecretList;

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * @var ConfigInterface
     */
    private $config;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $this->container->get(ConfigInterface::class);
        $this->appIdSecretList = $this->config->get("hyperf-base.wgw.config_list", []);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        //不是zgw协议不处理签名信息
        if (empty($request->getHeaderLine(Constants::WGW))) {
            return $handler->handle($request);
        }

        //如果存在数据签名，需要校验数据签名
        $requestBody = json_decode($request->getBody()->getContents(), true);
        if (!$requestBody) {
            return $handler->handle($request);
        }
        //如果没有签名校验
        if (!isset($requestBody["auth"])) {
            return $handler->handle($request);
        }
        //签名校验
        $this->checkSign($requestBody);

        return $handler->handle($request);
    }

    private function checkSign(array $requestBody)
    {
        $auth = $requestBody["auth"];
        $timestamp = $auth["timestamp"];
        $ttl = $this->config->get("hyperf-common.zgw.sign_ttl", 10);
        $secondDidPass = Carbon::now()->diffInRealSeconds(Carbon::createFromTimestamp($timestamp));
        Log::info("sign time did pass $secondDidPass seconds!");
        if ($secondDidPass > $ttl) {
            Log::info("sign has expired!");
            throw new HyperfBaseException(ErrorCode::WGW_AUTH_SIGNATURE_ERROR, "sign expire!");
        }

        $appId = $auth["appId"];
        //能否找到对应的秘钥
        if (!isset($this->appIdSecretList[$appId])) {
            throw new HyperfBaseException(ErrorCode::WGW_AUTH_APP_ID_NOT_EXIST);
        }
        $appSecret = $this->appIdSecretList[$appId];

        $param = Arr::get($requestBody, 'interface.param');
        $interfaceName = Arr::get($requestBody, 'interface.name');
        $param["interfaceName"] = $interfaceName;
        ksort($param);
        $paramJson = json_encode($param, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        Log::info("param json:$paramJson");
        $paramString = md5($paramJson);

        $nonce = $auth["nonce"];
        $base = "appId=$appId&appSecret=$appSecret&nonce=$nonce&timestamp=$timestamp&$paramString";

        Log::info("sign base:".$base);
        $paramSignature = $auth["signature"];
        $signature = hash_hmac("sha256", $base, $appSecret);
        if ($signature != $paramSignature) {
            Log::error("signature check fail!");
            Log::info("server sign:$signature");
            Log::info("client sign:$paramSignature");

            throw new HyperfBaseException(ErrorCode::WGW_AUTH_SIGNATURE_ERROR);
        }
        Log::info("check signature($paramSignature) success!");
    }
}
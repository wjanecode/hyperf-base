<?php
/**
 * @link     https://51coode.com
 * @contact  https://51coode.com
 */
namespace WJaneCode\HyperfBase\Middleware;


use GuzzleHttp\Psr7\Uri;
use http\Url;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Str;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use WJaneCode\HyperfBase\Log\Log;


/**
 * 跨域控制插件
 * Class CrossOriginMiddleware
 */
class CrossOriginMiddleware implements MiddlewareInterface
{
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
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        //是不是跨域预判定请求
        if (! $this->isCorsRequest($request)) {
            return $handler->handle($request);
        }
        Log::debug('跨域中间件');

        //读取配置
        $enableCrossOrigin = $this->config->get('hyperf-base.cors.enable_cross_origin',false);
        if (!$enableCrossOrigin) {
            //不准跨域
            throw new HttpException(403, "not allowed cross origin switch off");
        }

        //读取允许跨域域名
        $origin = $request->getHeaderLine("Origin");
        $configOrigins = $this->config->get("hyperf-base.cors.allow_cross_origins");

        //配置http://*.xxx.xxx.com跨域域名匹配
        $isCorsMatched = false;
        array_map(function (string $item) use ($request, $origin, &$isCorsMatched) {
            $url = new Uri($item);
            $host = $url->getHost();
            if (Str::startsWith($host, '*')) {
                $subHost = Str::after($host, '*');
                if ($request->getUri()->getScheme() == $url->getScheme() && Str::endsWith($request->getUri()->getHost(), $subHost)) {
                    Log::info("request origin cors is matched by item:$item");
                    $isCorsMatched = true;
                }
            }else{
                if ($origin == $item) {
                    Log::info("request origin cors is matched by item:$item");
                    $isCorsMatched = true;
                }
            }
        }, $configOrigins);

        if (!$isCorsMatched) {
            //不准跨域
            throw new HttpException(403, "not allowed cross origin not in whitelist");
        }

        //允许跨域
        $response = Context::get(ResponseInterface::class);
        $response = $response->withAddedHeader("Access-Control-Allow-Origin", $origin);
        $response = $response->withAddedHeader("Access-Control-Allow-Credentials", "true");
        $response = $response->withAddedHeader('Access-Control-Allow-Headers', 'DNT,Keep-Alive,User-Agent,Cache-Control,Content-Type,hyperf-session-id');
        Context::set(ResponseInterface::class, $response);

        if ($request->getMethod() == 'OPTIONS') {
            return $response;
        }

        return  $handler->handle($request);
    }

    private function isCorsRequest(ServerRequestInterface $request)
    {
        return $request->hasHeader("Origin") && !$this->isSameHost($request);
    }

    private function isSameHost(ServerRequestInterface $request)
    {
        return $request->getHeaderLine("Origin") === $request->getUri()->getScheme()."://".$request->getUri()->getHost();
    }
}
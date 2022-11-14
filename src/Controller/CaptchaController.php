<?php

declare(strict_types=1);
/**
 * @link     https://51coode.com
 * @contact  https://51coode.com
 */
namespace WJaneCode\HyperfBase\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use Psr\Http\Message\ResponseInterface;

/**
 * 基础的验证码服务的集成.
 * @AutoController(prefix="/common/captcha")
 * Class CaptchaController
 */
class CaptchaController extends AbstractController
{
    /**
     * 获取一条验证码
     * 此接口访问名为common.captcha.get.
     * @return ResponseInterface
     */
    public function get()
    {
        return $this->success($this->captchaService->get());
    }

    /**
     * 刷新一条验证码
     * 此接口访问名为common.captcha.refresh.
     */
    public function refresh(): ResponseInterface
    {
        $this->validate([
            'key' => 'string|min:1',
        ]);
        $cacheKey = $this->request->param('key');
        return $this->success($this->captchaService->refresh($cacheKey));
    }
}

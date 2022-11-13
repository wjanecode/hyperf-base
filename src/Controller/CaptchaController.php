<?php
declare(strict_types=1);

namespace WJaneCode\HyperfBase\Controller;

use Psr\Http\Message\ResponseInterface;
use Hyperf\HttpServer\Annotation\AutoController;
/**
 * 基础的验证码服务的集成
 * @AutoController (prefix="/common/captcha")
 * Class CaptchaController
 * @package App\Controller\Common
 */
class CaptchaController extends AbstractController
{
    /**
     * 获取一条验证码
     * 此接口访问名为common.captcha.get
     * @return ResponseInterface
     */
    public function get()
    {
        return $this->success($this->captchaService->get());
    }

    /**
     * 刷新一条验证码
     * 此接口访问名为common.captcha.refresh
     * @return ResponseInterface
     */
    public function refresh(): ResponseInterface
    {
        $this->validate([
            'key' => 'string|min:1'
        ]);
        $cacheKey = $this->request->param('key');
        return $this->success($this->captchaService->refresh($cacheKey));
    }
}
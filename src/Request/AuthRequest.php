<?php

declare(strict_types=1);
/**
 * @link     https://51coode.com
 * @contact  https://51coode.com
 */
namespace WJaneCode\HyperfBase\Request;

/**
 * 需要登录态的请求继承这个基础即可
 * Class AuthRequest.
 */
class AuthRequest extends Request
{
    public function rules(): array
    {
        return [];
    }

    protected function authorize(): bool
    {
        return $this->isLogin();
    }
}

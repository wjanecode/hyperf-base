<?php

declare(strict_types=1);
/**
 * @link     https://51coode.com
 * @contact  https://51coode.com
 */
namespace WJaneCode\HyperfBase\Request;

/**
 * 管理员请求，可以通过重载
 * isAdmin()方式来决定是否管理员
 * Class AdminRequest.
 */
class AdminRequest extends AuthRequest
{
    public function rules(): array
    {
        return [];
    }

    protected function authorize(): bool
    {
        return $this->isAdmin();
    }
}

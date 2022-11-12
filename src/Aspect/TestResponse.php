<?php
declare(strict_types=1);

namespace WJaneCode\HyperfBase\Aspect;

use Hyperf\Utils\Codec\Json;
use PHPUnit\Framework\Assert;
use Qbhy\HyperfTesting\TestResponse as QTestResponse;
use WJaneCode\HyperfBase\Constants\ErrorCode;

/**
 * 断言返回状态是否正确
 */
class TestResponse extends QTestResponse
{
    public function assertOk()
    {
        parent::assertOk();
        //判断是否为JSON
        Assert::assertJson($this->getContent(),ErrorCode::getMessage(ErrorCode::NOT_JSON));
        //判断数据的code是否为0
        Assert::assertSame(0,Json::decode($this->getContent()),ErrorCode::getMessage(ErrorCode::BUSINESS_CODE_NOT_SUCCESS));
        return $this;
    }
}
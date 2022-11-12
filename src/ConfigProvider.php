<?php
declare(strict_types=1);

namespace WJaneCode\HyperfBase;

use Hyperf\HttpServer\CoreMiddleware;
use Hyperf\Validation\Middleware\ValidationMiddleware;
use Qbhy\HyperfTesting\TestResponse;
use WJaneCode\HyperfBase\Middleware\AppCoreMiddleware;
use WJaneCode\HyperfBase\Middleware\AppValidationMiddleware;

/**
 * 组件配置
 */
class ConfigProvider
{
    public function __invoke():array
    {
        return [
            //替换依赖类
            'dependencies' => [
                CoreMiddleware::class => AppCoreMiddleware::class,
                ValidationMiddleware::class => AppValidationMiddleware::class,
                TestResponse::class => \WJaneCode\HyperfBase\Aspect\TestResponse::class,
            ],
            'commands' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                    'ignore_annotations' => [
                        'mixin',
                    ],
                ],
            ],
            // 组件默认配置文件，即执行命令后会把 source 的对应的文件复制为 destination 对应的的文件
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'hyperf-base config.', // 描述
                    // 建议默认配置放在 publish 文件夹中，文件命名和组件名称相同
                    'source' => __DIR__ . '/Publish/hyperf-base.php',  // 对应的配置文件路径
                    'destination' => BASE_PATH . '/config/autoload/hyperf-base.php', // 复制为这个路径下的该文件
                ],
                [
                    'id' => 'shell',
                    'description' => 'hyperf-common server shell.', // 描述
                    // 建议默认配置放在 publish 文件夹中，文件命名和组件名称相同
                    'source' => __DIR__ . '/Publish/service.sh',  // 对应的配置文件路径
                    'destination' => BASE_PATH . '/bin/service.sh', // 复制为这个路径下的该文件
                ],
            ],
        ];
    }
}
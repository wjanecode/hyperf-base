<?php

declare(strict_types=1);
/**
 * @link     https://51coode.com
 * @contact  https://51coode.com
 */
namespace WJaneCode\HyperfBase\Facade;

use Hyperf\Contract\SessionInterface;
use Hyperf\Di\Exception\Exception;
use Hyperf\Utils\ApplicationContext;

/**
 * @method static void clear()
 * @method static bool start()
 * @method static string getId()
 * @method static void setId(string $id)
 * @method static string getName()
 * @method static void setName(string $name)
 * @method static bool invalidate(?int $lifetime = null)
 * @method static bool migrate(bool $destroy = false, ?int $lifetime = null)
 * @method static void save()
 * @method static bool has(string $name)
 * @method static mixed get(string $name, $default = null)
 * @method static void set(string $name, $value)
 * @method static void put($key, $value = null)
 * @method static array all()
 * @method static void replace(array $attributes)
 * @method static mixed remove(string $name)
 * @method static void forget($keys)
 * @method static bool isStarted()
 * @method static string previousUrl()
 * @method static void setPreviousUrl(string $url)
 */
class Session
{
    public static function __callStatic($method, $args)
    {
        if (method_exists(static::session(), $method)) {
            call([static::session(), $method], $args);
        } else {
            throw new Exception('no method for session', 404);
        }
    }

    public static function session()
    {
        return ApplicationContext::getContainer()->get(SessionInterface::class);
    }

    public static function token2SessionId($token): string
    {
        // Session插件要求长度为40
        $holder = 'wjanecode';
        return md5($token) . $holder;
    }
}

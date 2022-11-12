<?php
declare(strict_types=1);

namespace WJaneCode\HyperfBase\Log;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Coroutine;
use Monolog\Logger;
use Psr\Http\Message\ServerRequestInterface;
use WjaneCode\HyperfBase\Constant\Constants;

/**
 * 这个类是一个Log的简易使用接口封装
 * 可以将接口入口信息补充进入单行日志
 * 将日志划分成了几种日志
 * task日志为crontab日志
 * request日志为请求日志
 * system日志为框架std输出的日志
 * daily日志为框架的详细日志
 * Class Log
 * @package ZYProSoft\Log
 */
class Log
{
    /**
     * 从调用栈获取接口信息
     * @param array $backTrace
     * @return string|string[]
     */
    public static function requestEntry(array $backTrace)
    {
        $moduleName = '';
        foreach ($backTrace as $v) {
            if (isset($v['file']) && stripos($v['file'],'CoreMiddleware.php')) {
                $tmp = array_reverse(explode('\\',trim($v['class'])));
                if (substr(strtolower($tmp[0]),-10) == 'controller') {
                    $module = str_replace('controller','',strtolower($tmp[1]));
                    $class = str_replace('controller','',strtolower($tmp[0]));
                    $function = $v['function'];
                    $moduleName = $class.'.'.$function;
                    if ($module) {
                        $moduleName = $module.'.'.$moduleName;
                    }
                    break;
                }
            }
        }
        if (empty($moduleName)) {
            if (Context::has(ServerRequestInterface::class)) {
                if (ApplicationContext::getContainer()->has(RequestInterface::class)) {
                    $request = ApplicationContext::getContainer()->get(RequestInterface::class);
                    $uri = $request->getUri()->getPath();
                    $moduleName = str_replace('/','.',ltrim($uri,'/'));
                }
            }
        }
        $moduleName = $moduleName??'system';
        return $moduleName;
    }

    public static function logger($group = 'default')
    {
        $channel = '';
        if (Coroutine::inCoroutine()) {
            $backTrace = \Swoole\Coroutine::getBackTrace();
            $channel = self::requestEntry($backTrace);
        }
        return ApplicationContext::getContainer()->get(LoggerFactory::class)->get($channel, $group);
    }

    public static function log($message, $level = Logger::INFO, $group = 'default')
    {
        $message = self::getFlowId($message);

        $message = self::getLocationInfo($message);

        $logger = self::logger($group);

        switch ($level)
        {
            case Logger::INFO:
                $logger->info($message);
                break;
            case Logger::DEBUG:
                $logger->debug($message);
                break;
            case Logger::ERROR:
                $logger->error($message);
                break;
            case Logger::WARNING:
                $logger->warning($message);
                break;
            case Logger::NOTICE:
                $logger->notice($message);
                break;
            case Logger::ALERT:
                $logger->alert($message);
                break;
            case Logger::CRITICAL:
                $logger->critical($message);
                break;
            case Logger::EMERGENCY:
                $logger->emergency($message);
                break;
        }
    }

    /**
     *  获取追踪ID
     * @param $message
     * @return mixed|string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private static function getFlowId($message)
    {
        //不在协程，直接返回
        if (!Coroutine::inCoroutine() || !Context::has(ServerRequestInterface::class)) {
            return $message;
        }
        if (!ApplicationContext::getContainer()->has(RequestInterface::class)) {
            return $message;
        }
        $request = ApplicationContext::getContainer()->get(RequestInterface::class);
        if (!isset($request)) {
            return $message;
        }
        $coId = Coroutine::id();
        $reqId = $request->getHeaderLine(Constants::WJANE_REQ_ID);
        $combineKey = "co($coId)";
        if (isset($reqId) && !empty($reqId)) {
            $combineKey .= "-$reqId";
        }
        return sprintf("%s || %s", $combineKey, $message);
    }

    private static function getLocationInfo($message): string
    {
        $locationInfo = array();
        if (Coroutine::inCoroutine()) {
            $trace = \Swoole\Coroutine::getBackTrace();
        }else{
            $trace = debug_backtrace();
        }
        $prevHop = null;
        $hop = array_pop($trace);
        while($hop !== null) {
            if(isset($hop['class'])) {
                $parentClass = get_parent_class($hop['class']);
                if ($parentClass === false) {
                    $parentClass = '';
                }
                $className = strtolower($hop['class']);
                if(!empty($className) and ($className == 'wjanecode\hyperfbase\log\log' or
                        strtolower($parentClass) == 'wjanecode\hyperfbase\log\log')) {
                    $locationInfo['line'] = $hop['line'];
                    $locationInfo['file'] = $hop['file'];
                    break;
                }
            }
            $prevHop = $hop;
            $hop = array_pop($trace);
        }
        $locationInfo['class'] = isset($prevHop['class']) ? $prevHop['class'] : 'main';
        if(isset($prevHop['function']) and
            $prevHop['function'] !== 'include' and
            $prevHop['function'] !== 'include_once' and
            $prevHop['function'] !== 'require' and
            $prevHop['function'] !== 'require_once') {

            $locationInfo['function'] = $prevHop['function'];
        } else {
            $locationInfo['function'] = 'main';
        }

        return $locationInfo['file']."({$locationInfo['line']}) || {$locationInfo['function']}() || $message";
    }

    /**
     *  请求日记
     * @param $msg
     * @param $level
     * @return void
     */
    public static function req($msg, $level = Logger::INFO)
    {
        self::log($msg, $level, 'request');
    }

    /**
     * crontab日记
     * @param $msg
     * @param $level
     * @return void
     */
    public static function task($msg , $level = Logger::INFO)
    {
        self::log($msg, $level, 'task');
    }

    public static function debug($msg)
    {
        self::log($msg, Logger::DEBUG);
    }
    public static function info($msg)
    {
        self::log($msg,Logger::INFO);
    }
    public static function warning($msg)
    {
        self::log($msg,Logger::WARNING);
    }
    public static function notice($msg)
    {
        self::log($msg, Logger::NOTICE);
    }
    public static function error($msg)
    {
        self::log($msg,Logger::ERROR);
    }
    public static function critical($msg)
    {
        self::log($msg,Logger::CRITICAL);
    }
    public static function alert($msg)
    {
        self::log($msg,Logger::ALERT);
    }
    public static function emergency($msg)
    {
        self::log($msg,Logger::EMERGENCY);
    }
}
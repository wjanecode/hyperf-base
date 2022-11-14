<?php
/**
 * This file is part of ZYProSoft/Hyperf-Common.
 *
 * @see     http://zyprosoft.lulinggushi.com
 * @document http://zyprosoft.lulinggushi.com
 * @contact  1003081775@qq.com
 * @Company  泽湾普罗信息技术有限公司(ZYProSoft)
 * @license  GPL
 */
declare(strict_types=1);
/**
 * @link     https://51coode.com
 * @contact  https://51coode.com
 */
namespace WJaneCode\HyperfBase\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * 封装系统启动，停止，重新启动的命令，执行方法
 * 这个命令文件依赖bin目录下的server.sh脚本
 * php bin/hyperf.php server start/stop/restart.
 * @Command
 */
class ServerCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('server');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Server control command');
        $this->addArgument('action', InputArgument::REQUIRED, 'control action');
    }

    public function handle()
    {
        $action = $this->input->getArgument('action');
        if (empty($action) || ! in_array($action, ['stop', 'restart', 'start'])) {
            $this->line('action type require in (stop,restart,start)');
        }
        $this->executeAction($action);
    }

    private function executeAction($action)
    {
        $name = env('APP_NAME');
        $shellPath = BASE_PATH . '/bin';
        $command = "cd {$shellPath} && bash service.sh -t {$action} -n {$name}";
        $result = system($command);
        if (! $result) {
            $this->line("run action {$action} fail!");
        }
    }
}

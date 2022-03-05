<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Catt\ServerCommand\Command;


use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Container\ContainerInterface;
use Swoole\Process;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServerStopCommand extends \Symfony\Component\Console\Command\Command {

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct (ContainerInterface $container) {

        $this->container = $container;

        parent::__construct('server:stop');

        $this->setDescription('服务器停止');
    }

    protected function execute (InputInterface $input, OutputInterface $output) {

        $config = $this->container->get(ConfigInterface::class);
        $logger = $this->container->get(StdoutLoggerInterface::class);

        $pidFile = $config->get('server.settings.pid_file');

        if ((!file_exists($pidFile))) {
            return 1;
        }

        $masterPid = (int) file_get_contents($pidFile);

        if ($masterPid > 1) {

            if (Process::kill($masterPid, 0)) {

                if (!Process::kill($masterPid, 15)) {
                    $logger->error(sprintf('Fail to kill -%s %s', 15, $masterPid));
                    return 1;
                }

                while (Process::kill($masterPid, 0)) {
                    sleep(1);
                }
            }
        }

        return 0;
    }
}

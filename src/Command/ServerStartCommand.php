<?php

declare(strict_types=1);

namespace Catt\ServerCommand\Command;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Utils\Arr;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class ServerStartCommand extends \Symfony\Component\Console\Command\Command {

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct (ContainerInterface $container) {

        $this->container = $container;

        parent::__construct('server:start');

        $this->setDescription('服务器启动');

        $this->addOption('daemonize', 'd', InputOption::VALUE_NONE, '是否守护进程启动');
    }

    protected function execute (InputInterface $input, OutputInterface $output) {

        $config = $this->container->get(ConfigInterface::class);

        $serverConfig = $config->get('server', []);
        if (!$serverConfig) {
            throw new InvalidArgumentException('At least one server should be defined.');
        }

        if ($input->getOption('daemonize')) {
            Arr::set($serverConfig, 'settings.daemonize', true);
            $config->set('server', $serverConfig);
        }

        $this->getApplication()->find('start')->run(new StringInput('start'), $output);

        return 0;
    }

}

<?php

declare(strict_types=1);

namespace Catt\ServerCommand\Command;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class ServerRestartCommand extends \Symfony\Component\Console\Command\Command {

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct (ContainerInterface $container) {

        $this->container = $container;

        parent::__construct('server:restart');

        $this->setDescription('服务器重启');

        $this->addOption('daemonize', 'd', InputOption::VALUE_NONE, '是否守护进程启动');
    }

    protected function execute (InputInterface $input, OutputInterface $output) {


        $this->getApplication()->find('server:stop')->run(new StringInput('server:stop'), $output);

        $this->getApplication()->find('server:start')->run($input, $output);

        return 0;
    }
}

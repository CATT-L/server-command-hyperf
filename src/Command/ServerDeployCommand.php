<?php

declare(strict_types=1);

namespace Catt\ServerCommand\Command;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Database\Connectors\ConnectionFactory;
use Hyperf\Utils\Arr;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

class ServerDeployCommand extends \Symfony\Component\Console\Command\Command {

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @var OutputStyle
     */
    private $output;

    /**
     * @var string
     */
    private $projectRoot;

    /**
     * @var ConfigInterface
     */
    private $config;

    public function __construct (ContainerInterface $container) {

        $this->container   = $container;
        $this->config      = $this->container->get(ConfigInterface::class);
        $this->logger      = $this->container->get(StdoutLoggerInterface::class);
        $this->projectRoot = defined('BASE_PATH') ? BASE_PATH : null;

        parent::__construct('server:deploy');

        $this->setDescription('服务器部署');
    }

    protected function execute (InputInterface $input, OutputInterface $output) {

        $this->output = new SymfonyStyle($input, $output);

        if (!is_null($this->projectRoot)) {
            // 初始化目录 runtime
            $this->__setupRuntimeDir();
            // 初始化目录 logs
            $this->__setupLogsDir();
        }

        // 初始化数据库
        $this->__setupDatabase();

        // 运行数据库迁移文件
        $this->getApplication()->find('migrate')->run(new StringInput('migrate'), $output);

        $this->output->success('服务器部署完毕,输入 composer restart 重启或运行服务');

        return 0;
    }

    /**
     * 若数据库不存在,则创建
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function __setupDatabase () {

        $databases = $this->config->get('databases', []);

        foreach ($databases as $pool => $options) {

            $database  = Arr::get($options, 'database');
            $charset   = Arr::get($options, 'charset', 'utf8mb4');
            $collation = Arr::get($options, 'collation', 'utf8mb4_unicode_ci');

            if (empty($database)) {
                continue;
            }

            $conn = $this->container->get(ConnectionFactory::class)->make(array_merge($options, ['database' => null]));

            $re = $conn->select("SELECT information_schema.SCHEMATA.SCHEMA_NAME FROM information_schema.SCHEMATA where SCHEMA_NAME='{$database}';");

            if (empty($re)) {
                $conn->select("create database {$database} CHARACTER SET {$charset} COLLATE {$collation}");

                $this->logger->info(sprintf('连接池[%s]的数据库[%s]创建成功', $pool, $database));
            }

            $conn->disconnect();
        }

    }

    /**
     * 初始化 runtime
     *
     * @return bool
     */
    private function __setupRuntimeDir () {

        $runtimeDir = $this->projectRoot.'/runtime';

        if (!is_dir($runtimeDir)) {

            mkdir($runtimeDir, 0775, true);
            chmod($runtimeDir, 0775);

            $this->logger->info('初始化Runtime目录');
        }

        return true;
    }

    /**
     * 初始化 logs
     *
     * @return bool
     */
    private function __setupLogsDir () {

        $log_file = $this->config->get('server.settings.log_file');

        if (!empty($log_file)) {
            $path = dirname($log_file);

            if (!is_dir($path)) {

                mkdir($path, 0775, true);
                chmod($path, 0775);

                $this->logger->info('初始化Logs目录');
            }
        }

        return true;
    }

}

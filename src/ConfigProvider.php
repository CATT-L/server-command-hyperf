<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Catt\ServerCommand;


use Catt\ServerCommand\Command\ServerRestartCommand;
use Catt\ServerCommand\Command\ServerStartCommand;
use Catt\ServerCommand\Command\ServerStopCommand;

class ConfigProvider {
    public function __invoke (): array {
        return [
            'dependencies' => [],
            'commands'     => [
                ServerStartCommand::class,
                ServerStopCommand::class,
                ServerRestartCommand::class,
            ],
            'listeners'    => [],
            'annotations'  => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
        ];
    }
}

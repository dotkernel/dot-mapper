<?php
/**
 * @see https://github.com/dotkernel/dot-mapper/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-mapper/blob/master/LICENSE.md MIT License
 */

namespace Dot\Mapper;

use Dot\Mapper\Factory\MapperManagerAwareInitializer;
use Dot\Mapper\Factory\MapperManagerFactory;
use Dot\Mapper\Mapper\MapperManager;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencyConfig(),

            'dot_mapper' => [

                'mapper_manager' => [

                ],

                'options' => [

                ]

            ],
        ];
    }

    public function getDependencyConfig()
    {
        return [
            'factories' => [
                MapperManager::class => MapperManagerFactory::class,
            ],
            'initializers' => [
                MapperManagerAwareInitializer::class,
            ],
            'aliases' => [
                'MapperManager' => MapperManager::class,
            ]
        ];
    }
}

<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/16/2016
 * Time: 4:26 PM
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

            'dot_ems' => [

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

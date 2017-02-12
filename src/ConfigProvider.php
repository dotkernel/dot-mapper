<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/16/2016
 * Time: 4:26 PM
 */

namespace Dot\Ems;

use Dot\Ems\Factory\MapperManagerFactory;
use Dot\Ems\Mapper\MapperManager;

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
            'aliases' => [
                'MapperManager' => MapperManager::class,
            ]
        ];
    }
}

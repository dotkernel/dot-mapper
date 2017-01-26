<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/16/2016
 * Time: 4:26 PM
 */

namespace Dot\Ems;

use Dot\Ems\Factory\EntityServiceAbstractFactory;
use Dot\Ems\Factory\EntityServiceOptionsAbstractFactory;
use Dot\Ems\Factory\MapperPluginManagerFactory;
use Dot\Ems\Factory\RelationPluginManagerFactory;
use Dot\Ems\Mapper\MapperPluginManager;
use Dot\Ems\Mapper\Relation\RelationPluginManager;
use Dot\Ems\Paginator\Factory\AdapterPluginManagerDelegator;
use Zend\Paginator\AdapterPluginManager;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencyConfig(),

            'dot_ems' => [

                'services' => [],

                'mapper_manager' => [],

                'relation_manager' => [],
            ],
        ];
    }

    public function getDependencyConfig()
    {
        return [
            'factories' => [
                'MapperManager' => MapperPluginManagerFactory::class,
                'RelationManager' => RelationPluginManagerFactory::class,
            ],
            'aliases' => [
                MapperPluginManager::class => 'MapperManager',
                RelationPluginManager::class => 'RelationManager',
            ],
            'abstract_factories' => [
                EntityServiceAbstractFactory::class,
                EntityServiceOptionsAbstractFactory::class,
            ],

            'delegators' => [
                AdapterPluginManager::class => [
                    AdapterPluginManagerDelegator::class,
                ]
            ]
        ];
    }
}

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

            ],
        ];
    }

    public function getDependencyConfig()
    {
        return [

        ];
    }
}

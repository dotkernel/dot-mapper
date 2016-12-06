<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/29/2016
 * Time: 7:59 PM
 */

namespace Dot\Ems\Factory;

use Dot\Ems\Mapper\Relation\RelationPluginManager;
use Interop\Container\ContainerInterface;

/**
 * Class RelationPluginManagerFactory
 * @package Dot\Ems\Factory
 */
class RelationPluginManagerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new RelationPluginManager($container, $container->get('config')['dot_ems']['relation_manager']);
    }
}
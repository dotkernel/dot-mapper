<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/29/2016
 * Time: 7:25 PM
 */

namespace Dot\Ems\Factory;

use Dot\Ems\Mapper\MapperPluginManager;
use Interop\Container\ContainerInterface;

/**
 * Class MapperPluginManagerFactory
 * @package Dot\Ems\Factory
 */
class MapperPluginManagerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new MapperPluginManager($container, $container->get('config')['dot_ems']['mapper_manager']);
    }
}

<?php
/**
 * @copyright: DotKernel
 * @library: dot-ems
 * @author: n3vrax
 * Date: 2/11/2017
 * Time: 9:40 PM
 */

declare(strict_types = 1);

namespace Dot\Ems\Factory;

use Dot\Ems\Mapper\MapperManager;
use Interop\Container\ContainerInterface;

/**
 * Class MapperManagerFactory
 * @package Dot\Ems\Factory
 */
class MapperManagerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new MapperManager($container, $container->get('config')['dot_ems']);
    }
}

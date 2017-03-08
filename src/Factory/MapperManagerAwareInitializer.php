<?php
/**
 * @copyright: DotKernel
 * @library: dot-ems
 * @author: n3vrax
 * Date: 2/13/2017
 * Time: 5:37 PM
 */

declare(strict_types = 1);

namespace Dot\Mapper\Factory;

use Dot\Mapper\Mapper\MapperManager;
use Dot\Mapper\Mapper\MapperManagerAwareInterface;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Initializer\InitializerInterface;

/**
 * Class MapperManagerAwareInitializer
 * @package Dot\Ems\Factory
 */
class MapperManagerAwareInitializer implements InitializerInterface
{
    public function __invoke(ContainerInterface $container, $instance)
    {
        if ($instance instanceof MapperManagerAwareInterface) {
            $mm = $container->get(MapperManager::class);
            $instance->setMapperManager($mm);
        }
    }
}

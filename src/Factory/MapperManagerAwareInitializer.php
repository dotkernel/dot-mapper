<?php
/**
 * @copyright: DotKernel
 * @library: dot-ems
 * @author: n3vrax
 * Date: 2/13/2017
 * Time: 5:37 PM
 */

declare(strict_types = 1);

namespace Dot\Ems\Factory;

use Dot\Ems\Mapper\MapperManager;
use Dot\Ems\Mapper\MapperManagerAwareInterface;
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

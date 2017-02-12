<?php
/**
 * @copyright: DotKernel
 * @library: dot-ems
 * @author: n3vrax
 * Date: 2/11/2017
 * Time: 8:40 PM
 */

declare(strict_types = 1);

namespace Dot\Ems\Factory;

use Dot\Ems\Mapper\MapperManager;
use Interop\Container\ContainerInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\Hydrator\HydratorPluginManager;

/**
 * Class DbMapperFactory
 * @package Dot\Ems\Factory
 */
class DbMapperFactory
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return mixed
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = [])
    {
        if (isset($options['adapter']) && is_string($options['adapter'])) {
            $options['adapter'] = $container->get($options['adapter']);
        }

        if (isset($options['slave_adapter']) && is_string($options['slave_adapter'])) {
            $options['slave_adapter'] = $container->get($options['slave_adapter']);
        }

        if (isset($options['hydrator_manager']) && is_string($options['hydrator_manager'])) {
            $options['hydrator_manager'] = $container->get($options['hydrator_manager']);
        } else {
            $options['hydrator_manager'] = $container->has('HydratorManager')
                ? $container->get('HydratorManager')
                : new HydratorPluginManager($container, []);
        }

        if (isset($options['event_manager']) && is_string($options['event_manager'])) {
            $options['event_manager'] = $container->get($options['event_manager']);
        } else {
            $options['event_manager'] = $container->has(EventManagerInterface::class)
                ? $container->get(EventManagerInterface::class)
                : new EventManager();
        }

        $mapperManager = $container->get(MapperManager::class);

        return new $requestedName($mapperManager, $options);
    }
}

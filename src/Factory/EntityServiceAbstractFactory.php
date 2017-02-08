<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/29/2016
 * Time: 8:05 PM
 */

namespace Dot\Ems\Factory;

use Dot\Ems\Event\EntityServiceListenerAwareInterface;
use Dot\Ems\Event\EntityServiceListenerInterface;
use Dot\Ems\Exception\InvalidArgumentException;
use Dot\Ems\Exception\RuntimeException;
use Dot\Ems\Mapper\MapperPluginManager;
use Dot\Ems\Options\ServiceOptions;
use Dot\Ems\Service\EntityService;
use Dot\Ems\Service\ServiceInterface;
use Dot\Helpers\DependencyHelperTrait;
use Interop\Container\ContainerInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;

/**
 * Class EntityServiceAbstractFactory
 * @package Dot\Ems\Factory
 */
class EntityServiceAbstractFactory extends AbstractServiceFactory
{
    use DependencyHelperTrait;

    const SPECIFIC_PART = 'service';

    /** @var  ServiceOptions */
    protected $serviceOptions;

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return ServiceInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $specificServiceName = explode('.', $requestedName)[2];
        /** @var ServiceOptions $serviceOptions */
        $serviceOptions = $container->get(sprintf(
            '%s.%s.%s',
            self::DOT_EMS_PART,
            EntityServiceOptionsAbstractFactory::SPECIFIC_PART,
            $specificServiceName
        ));

        $this->serviceOptions = $serviceOptions;

        /** @var MapperPluginManager $mapperManager */
        $mapperManager = $container->get(MapperPluginManager::class);

        $service = null;
        $serviceClass = $serviceOptions->getType() ?: EntityService::class;
        if ($container->has($serviceClass)) {
            $service = $container->get($serviceClass);
        } elseif (is_string($serviceClass) && class_exists($serviceClass)) {
            $service = new $serviceClass;
        }

        if (!$service instanceof ServiceInterface) {
            throw new RuntimeException('Could not load entity service '
                . $requestedName . '. Make sure the defined type is implementing ' . ServiceInterface::class);
        }

        $mapperOptions = $serviceOptions->getMapper();
        $mapper = $mapperManager->get(key($mapperOptions), current($mapperOptions));

        $service->setMapper($mapper);
        $service->setAtomicOperations($serviceOptions->isAtomicOperations());

        $eventManager = $container->has(EventManagerInterface::class)
            ? $container->get(EventManagerInterface::class)
            : new EventManager();
        $service->setEventManager($eventManager);

        //set the name after setting the event manager, because the name will be added as identifier
        $service->setName($requestedName);
        $service->setEnableEvents($serviceOptions->isEnableEvents());

        $this->attachServiceListeners($service, $container);

        return $service;
    }

    /**
     * @param EntityServiceListenerAwareInterface $service
     * @param ContainerInterface $container
     */
    protected function attachServiceListeners(
        EntityServiceListenerAwareInterface $service,
        ContainerInterface $container
    ) {
        $listeners = $this->serviceOptions->getEventListeners();
        foreach ($listeners as $listener) {
            $listener = $this->getDependencyObject($container, $listener);

            if (!$listener instanceof EntityServiceListenerInterface) {
                throw new InvalidArgumentException(sprintf(
                    'Provided entity service listener of type "%s" is not valid. Expected string or %s',
                    is_object($listener) ? get_class($listener) : gettype($listener),
                    EntityServiceListenerInterface::class
                ));
            }

            $service->attachListener($listener);
        }
    }
}

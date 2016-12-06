<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/29/2016
 * Time: 8:05 PM
 */

namespace Dot\Ems\Factory;

use Dot\Ems\Exception\RuntimeException;
use Dot\Ems\Mapper\MapperPluginManager;
use Dot\Ems\Options\ServiceOptions;
use Dot\Ems\Service\EntityService;
use Dot\Ems\Service\ServiceInterface;
use Interop\Container\ContainerInterface;

/**
 * Class EntityServiceAbstractFactory
 * @package Dot\Ems\Factory
 */
class EntityServiceAbstractFactory extends AbstractServiceFactory
{
    const SPECIFIC_PART = 'service';

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
            '%s.%s.%s', self::DOT_EMS_PART, EntityServiceOptionsAbstractFactory::SPECIFIC_PART, $specificServiceName
        ));

        /** @var MapperPluginManager $mapperManager */
        $mapperManager = $container->get(MapperPluginManager::class);

        $service = null;
        $serviceClass = $serviceOptions->getType() ?: EntityService::class;
        if($container->has($serviceClass)) {
            $service = $container->get($serviceClass);
        }
        elseif(is_string($serviceClass) && class_exists($serviceClass)) {
            $service = new $serviceClass;
        }

        if(!$service instanceof ServiceInterface) {
            throw new RuntimeException('Could not load entity service '
                . $requestedName . '. Make sure the defined type is implementing ' . ServiceInterface::class);
        }

        $mapperOptions = $serviceOptions->getMapper();
        $mapper = $mapperManager->get(key($mapperOptions), current($mapperOptions));
        $service->setMapper($mapper);
        $service->setAtomicOperations($serviceOptions->isAtomicOperations());

        return $service;

    }
}
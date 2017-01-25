<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/29/2016
 * Time: 8:05 PM
 */

namespace Dot\Ems\Factory;

use Dot\Ems\Event\EntityServiceListenerInterface;
use Dot\Ems\Exception\RuntimeException;
use Dot\Ems\Mapper\MapperPluginManager;
use Dot\Ems\Options\ServiceOptions;
use Dot\Ems\Service\EntityService;
use Dot\Ems\Service\ServiceInterface;
use Dot\Helpers\DependencyHelperTrait;
use Interop\Container\ContainerInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

/**
 * Class EntityServiceAbstractFactory
 * @package Dot\Ems\Factory
 */
class EntityServiceAbstractFactory implements AbstractFactoryInterface
{
    use DependencyHelperTrait;

    const PREFIX = 'dot-ems.service';

    /** @var  array */
    protected $config;

    /** @var string  */
    protected $configKey = 'dot_ems';

    /** @var string  */
    protected $servicesConfigKey = 'services';

    /** @var  ServiceOptions */
    protected $serviceOptions;

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $parts = explode('.', $requestedName);
        if (count($parts) !== 3) {
            return false;
        }

        if (($parts[0] . '.' . $parts[1]) !== static::PREFIX) {
            return false;
        }

        $config = $this->getConfig($container);
        if (empty($config)) {
            return false;
        }

        return isset($config[$parts[2]]);
    }

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return ServiceInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $requestedName = explode('.', $requestedName)[2];
        $config = $this->getConfig($container)[$requestedName];

        $type = isset($config['type']) ? $config['type'] : EntityService::class;
        $this->processConfig($config, $container);

        $config['name'] = $requestedName;
        $config['event_manager'] = $container->has(EventManagerInterface::class)
            ? $container->get(EventManagerInterface::class)
            : new EventManager();

        $service = new $type($config);

        if (!$service instanceof ServiceInterface) {
            throw new RuntimeException('Could not load entity service '
                . $requestedName . '. Make sure the defined type is implementing ' . ServiceInterface::class);
        }

        return $service;
    }

    /**
     * @param $config
     * @param ContainerInterface $container
     */
    protected function processConfig(&$config, ContainerInterface $container)
    {
        /** @var MapperPluginManager $mapperManager */
        $mapperManager = $container->get('MapperManager');

        if (isset($config['mapper']) && is_array($config['mapper'])) {
            $mapperType = key($config['mapper']);
            $mapperConfig = current($config['mapper']);
            $config['mapper'] = $mapperManager->get($mapperType, $mapperConfig);
        }

        if (isset($config['service_listeners']) && is_array($config['service_listeners'])) {
            foreach ($config['service_listeners'] as $k => $listener) {
                if (is_string($listener)) {
                    $listener = $this->getDependencyObject($container, $listener);
                }

                if (! $listener instanceof EntityServiceListenerInterface) {
                    throw new RuntimeException(sprintf(
                        "Entity service listener must be an instance of %s. %s was given",
                        EntityServiceListenerInterface::class,
                        is_object($listener) ? get_class($listener) : gettype($listener)
                    ));
                }

                $config['service_listeners'][$k] = $listener;
            }
        }
    }

    /**
     * @param ContainerInterface $container
     * @return array
     */
    protected function getConfig(ContainerInterface $container)
    {
        if ($this->config) {
            return $this->config;
        }

        if (! $container->has('config')) {
            return $this->config = [];
        }

        $config = $container->get('config');
        if (isset($config[$this->configKey][$this->servicesConfigKey])
            && is_array($config[$this->configKey][$this->servicesConfigKey])) {
            return $this->config = $config[$this->configKey][$this->servicesConfigKey];
        }

        return $this->config = [];
    }
}

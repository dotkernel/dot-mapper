<?php
/**
 * @copyright: DotKernel
 * @library: dot-ems
 * @author: n3vrax
 * Date: 2/11/2017
 * Time: 1:03 PM
 */

declare(strict_types = 1);

namespace Dot\Ems\Mapper;

use Dot\Ems\Entity\EntityInterface;
use Dot\Ems\Event\MapperEventListenerInterface;
use Dot\Ems\Exception\InvalidArgumentException;
use Dot\Ems\Exception\RuntimeException;
use Dot\Ems\Factory\DbMapperFactory;
use Zend\Db\Metadata\MetadataInterface;
use Zend\Db\Metadata\Source\Factory;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\ServiceManager\AbstractPluginManager;

/**
 * Class MapperManager
 * @package Dot\Ems\Mapper
 */
class MapperManager extends AbstractPluginManager
{
    protected $instanceOf = MapperInterface::class;

    protected $factories = [
        EntityDbMapper::class => DbMapperFactory::class,
    ];

    /** @var array */
    protected $mappers = [];

    /** @var array */
    protected $metadata = [];

    /** @var  array */
    protected $options = [];

    /** @var  string */
    protected $defaultAdapterName;

    /**
     * MapperManager constructor.
     * @param null $configInstanceOrParentLocator
     * @param array $config
     */
    public function __construct($configInstanceOrParentLocator = null, array $config = [])
    {
        if (isset($config['options']) && is_array($config['options'])) {
            $this->options = $config['options'];
        }

        if (isset($config['default_adapter']) && is_string($config['default_adapter'])) {
            $this->defaultAdapterName = $config['default_adapter'];
        }

        parent::__construct($configInstanceOrParentLocator, $config['mapper_manager'] ?? []);
    }

    /**
     * @param string $name
     * @param array|null $options
     * @return mixed
     */
    public function get($name, array $options = null)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid mapper name `%s` given',
                is_object($name) ? get_class($name) : gettype($name)
            ));
        }
        if (isset($this->mappers[$name])) {
            return $this->mappers[$name];
        }

        $entity = $name;
        if ($this->creationContext->has($entity)) {
            $entity = $this->creationContext->get($entity);
        }

        if (is_string($entity) && class_exists($entity)) {
            $entity = new $entity();
        }

        if (!$entity instanceof EntityInterface) {
            throw new RuntimeException(sprintf('Entity `%s` is not a valid EntityInterface instance', $name));
        }

        $mapperOptions = [];
        if (isset($this->options[$name])
            && isset($this->options[$name]['mapper'])
            && is_array($this->options[$name]['mapper'])
        ) {
            $mapperOptions += $this->options[$name]['mapper'];
        }

        $mapperOptions += ['adapter' => $this->defaultAdapterName];

        $options = $options ?? [];
        $options += $mapperOptions;

        $adapterName = $options['adapter'];
        $options['adapter'] = $this->creationContext->get($options['adapter']);
        if (!isset($this->metadata[$adapterName])) {
            $this->metadata[$adapterName] = Factory::createSourceFromAdapter($options['adapter']);
        }

        /** @var MetadataInterface $metadata */
        $metadata = $this->metadata[$adapterName];
        $options['metadata'] = $metadata;
        $options['prototype'] = $entity;

        $mapper = parent::get($name, $options);
        $this->mappers[$name] = $mapper;

        if (isset($options['event_listeners']) && is_array($options['event_listeners'])) {
            $this->attachMapperListeners($mapper, $options['event_listeners']);
        }

        return $mapper;
    }

    /**
     * @param EventManagerAwareInterface $mapper
     * @param array $listenersConfig
     */
    protected function attachMapperListeners(EventManagerAwareInterface $mapper, array $listenersConfig)
    {
        foreach ($listenersConfig as $listener) {
            if (is_string($listener)) {
                $l = $this->getListenerObject($listener);
                $p = 1;
                $l->attach($mapper->getEventManager(), $p);
            } elseif (is_array($listener)) {
                $l = $listener['type'] ?? '';
                $p = $listener['priority'] ?? 1;

                $l = $this->getListenerObject($l);
                $l->attach($mapper->getEventManager(), $p);
            }
        }
    }

    /**
     * @param string $listener
     * @return MapperEventListenerInterface
     */
    protected function getListenerObject(string $listener): MapperEventListenerInterface
    {
        $container = $this->creationContext;
        if ($container->has($listener)) {
            $listener = $container->get($listener);
        }

        if (is_string($listener) && class_exists($listener)) {
            $listener = new $listener();
        }

        if (!$listener instanceof MapperEventListenerInterface) {
            throw new RuntimeException('Mapper event listener is not an instance of '
                . MapperEventListenerInterface::class);
        }

        return $listener;
    }
}

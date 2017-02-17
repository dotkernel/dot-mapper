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
use Dot\Ems\Exception\RuntimeException;
use Dot\Ems\Factory\DbMapperFactory;
use Zend\Db\Metadata\MetadataInterface;
use Zend\Db\Metadata\Source\Factory;
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
        if (isset($this->mappers[$name])) {
            return $this->mappers[$name];
        }

        if (!class_exists($name)) {
            throw new RuntimeException(sprintf('Entity `%s` is not a valid class'));
        }

        $entityOptions = [];
        if (isset($this->options[$name])
            && isset($this->options[$name]['entity'])
            && is_array($this->options[$name]['entity'])
        ) {
            $entityOptions = array_merge($entityOptions, $this->options[$name]['entity']);
        }

        $entityInject = [];
        if (isset($entityOptions['services']) && is_array($entityOptions['services'])) {
            $entityInject = $entityOptions['services'];
            unset($entityOptions['services']);
        }

        foreach ($entityInject as $k => $v) {
            $entityOptions[$k] = $this->creationContext->get($v);
        }

        /** @var EntityInterface $entity */
        $entity = new $name($entityOptions);

        $mapperOptions = [
            'adapter' => $this->defaultAdapterName,
        ];
        if (isset($this->options[$name])
            && isset($this->options[$name]['mapper'])
            && is_array($this->options[$name]['mapper'])
        ) {
            $mapperOptions = array_merge($mapperOptions, $this->options[$name]['mapper']);
        }

        $options = array_merge($mapperOptions, $options);

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

        $entity->setMapper($mapper);
        $this->mappers[$name] = $mapper;

        return $mapper;
    }
}

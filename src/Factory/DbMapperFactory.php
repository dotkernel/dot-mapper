<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/29/2016
 * Time: 7:50 PM
 */

namespace Dot\Ems\Factory;

use Dot\Ems\Exception\RuntimeException;
use Dot\Ems\Mapper\DbMapper;
use Dot\Helpers\DependencyHelperTrait;
use Interop\Container\ContainerInterface;
use Zend\Hydrator\ClassMethods;
use Zend\Hydrator\HydratorInterface;
use Zend\Paginator\AdapterPluginManager;

/**
 * Class DbMapperFactory
 * @package Dot\Ems\Factory
 */
class DbMapperFactory
{
    use DependencyHelperTrait;

    public function __invoke(ContainerInterface $container, $requestedName, $config = [])
    {
        if (!isset($config['adapter']) || isset($config['adapter']) && !is_string($config['adapter'])) {
            throw new RuntimeException('No db adapter specified');
        }

        if (!isset($config['table']) || isset($config['table']) && !is_string($config['table'])) {
            throw new RuntimeException('No table name specified');
        }

        if (!isset($config['entity_prototype'])) {
            throw new RuntimeException('No entity prototype specified');
        }

        $hydratorName = isset($config['entity_hydrator']) && is_string($config['entity_hydrator'])
            ? $config['entity_hydrator'] : '';

        $entityPrototype = $this->getDependencyObject($container, $config['entity_prototype']);
        $hydrator = $this->getDependencyObject($container, $hydratorName);

        if (!is_object($entityPrototype)) {
            throw new RuntimeException('Entity prototype is not an object');
        }
        if (!$hydrator instanceof HydratorInterface) {
            $hydrator = new ClassMethods(false);
        }

        /** @var DbMapper $mapper */
        $mapper = new $requestedName(
            $config['table'],
            $container->get($config['adapter']),
            $entityPrototype, $hydrator);

        if (!$mapper instanceof DbMapper) {
            throw new RuntimeException('Requested mapper is not an instance of ' . DbMapper::class);
        }

        $mapper->setPaginatorAdapterManager($container->get(AdapterPluginManager::class));
        if (isset($config['paginator_adapter']) && is_string($config['paginator_adapter'])) {
            $mapper->setPaginatorAdapterName($config['paginator_adapter']);
        }

        if (isset($config['identifier_name']) && is_string($config['identifier_name'])) {
            $mapper->setIdentifierName($config['identifier_name']);
        }

        return $mapper;
    }
}

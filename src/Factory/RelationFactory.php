<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/29/2016
 * Time: 11:44 PM
 */

namespace Dot\Ems\Factory;

use Dot\Ems\Exception\RuntimeException;
use Dot\Ems\Mapper\MapperPluginManager;
use Dot\Ems\Mapper\Relation\RelationInterface;
use Interop\Container\ContainerInterface;

class RelationFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, $config = [])
    {
        if(!isset($config['field_name']) || isset($config['field_name']) && !is_string($config['field_name'])) {
            throw new RuntimeException('Relation field name must be a string');
        }

        if(!isset($config['ref_name']) || isset($config['ref_name']) && !is_string($config['ref_name'])) {
            throw new RuntimeException('Relation ref name must be a string');
        }

        if(!isset($config['mapper']) || isset($config['mapper']) && !is_array($config['mapper'])) {
            throw new RuntimeException('Invalid relation mapper config');
        }

        /** @var MapperPluginManager $mapperManager */
        $mapperManager = $container->get(MapperPluginManager::class);
        $mapper = $mapperManager->get(key($config['mapper']), current($config['mapper']));

        /** @var RelationInterface $relation */
        $relation = new $requestedName;
        if(!$relation instanceof RelationInterface) {
            throw new RuntimeException('Relation object must implement ' . RelationInterface::class);
        }

        $relation->setMapper($mapper);
        $relation->setRefName($config['ref_name']);
        $relation->setFieldName($config['field_name']);

        return $relation;
    }
}
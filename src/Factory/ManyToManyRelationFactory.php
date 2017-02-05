<?php
/**
 * Created by PhpStorm.
 * User: n3vra
 * Date: 12/13/2016
 * Time: 5:07 PM
 */

declare(strict_types=1);

namespace Dot\Ems\Factory;

use Dot\Ems\Exception\RuntimeException;
use Dot\Ems\Mapper\MapperPluginManager;
use Dot\Ems\Mapper\Relation\ManyToManyRelation;
use Interop\Container\ContainerInterface;

/**
 * Class ManyToManyRelationFactory
 * @package Dot\Ems\Factory
 */
class ManyToManyRelationFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, $config = [])
    {
        if (!isset($config['field_name']) || isset($config['field_name'])
            && !is_string($config['field_name'])
        ) {
            throw new RuntimeException('Relation field name must be a string');
        }

        if (!isset($config['ref_name']) || isset($config['ref_name'])
            && !is_string($config['ref_name'])
        ) {
            throw new RuntimeException('Config `ref_name` must be a string');
        }

        if (!isset($config['target_ref_name']) || isset($config['target_ref_name'])
            && !is_string($config['target_ref_name'])
        ) {
            throw new RuntimeException('Config `target_ref_name` must be a string');
        }

        if (!isset($config['intersection_mapper']) || isset($config['intersection_mapper'])
            && !is_array($config['intersection_mapper'])
        ) {
            throw new RuntimeException('Invalid intersection mapper config');
        }

        if (!isset($config['target_mapper']) || isset($config['target_mapper'])
            && !is_array($config['target_mapper'])
        ) {
            throw new RuntimeException('Invalid target mapper config');
        }

        /** @var MapperPluginManager $mapperManager */
        $mapperManager = $container->get(MapperPluginManager::class);

        $intersectionMapper = $mapperManager->get(
            key($config['intersection_mapper']),
            current($config['intersection_mapper'])
        );

        $targetMapper = $mapperManager->get(
            key($config['target_mapper']),
            current($config['target_mapper'])
        );

        $relation = new ManyToManyRelation(
            $intersectionMapper,
            $config['ref_name'],
            $targetMapper,
            $config['target_ref_name'],
            $config['field_name']
        );

        $relation->setChangeRefs(isset($config['change_refs']) ? (bool)$config['change_refs'] : true);
        $relation->setDeleteRefs(isset($config['delete_refs']) ? (bool)$config['delete_refs'] : false);
        $relation->setCreateTargetRefs(
            isset($config['create_target_refs'])
                ? (bool)$config['create_target_refs']
                : true
        );

        return $relation;
    }
}

<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 12/7/2016
 * Time: 7:27 PM
 */

namespace Dot\Ems\Mapper;

use Dot\Ems\Mapper\Relation\RelationInterface;

/**
 * Interface RelationalMapperInterface
 * @package Dot\Ems\Mapper
 */
interface RelationalMapperInterface extends MapperInterface
{
    /**
     * @return array
     */
    public function getRelations();

    /**
     * @param $relations
     * @return mixed
     */
    public function setRelations($relations);

    /**
     * @param RelationInterface $relation
     * @return mixed
     */
    public function addRelation(RelationInterface $relation);
}

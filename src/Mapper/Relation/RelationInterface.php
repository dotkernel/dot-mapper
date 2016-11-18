<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/17/2016
 * Time: 10:50 PM
 */

namespace Dot\Ems\Mapper\Relation;

use Dot\Ems\Mapper\MapperInterface;

/**
 * Interface RelationInterface
 * @package Dot\Ems\Mapper\Relation
 */
interface RelationInterface
{
    const ONE_TO_ONE = 1;
    const ONE_TO_MANY = 2;
    const MANY_TO_MANY = 3;

    /**
     * @return mixed
     */
    public function getType();

    /**
     * @return mixed
     */
    public function getRefName();

    /**
     * @param $refValue
     * @return mixed
     */
    public function fetchRef($refValue);

    /**
     * @param $entity
     * @param $refValue
     * @return mixed
     */
    public function saveRef($entity, $refValue);

    /**
     * @param $entity
     * @return mixed
     */
    public function deleteRef($entity);

    /**
     * @return MapperInterface
     */
    public function getMapper();
}
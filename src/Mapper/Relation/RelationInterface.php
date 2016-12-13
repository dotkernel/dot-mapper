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
     * @param $refName
     * @return mixed
     */
    public function setRefName($refName);

    /**
     * @return string
     */
    public function getFieldName();

    /**
     * @param $fieldName
     * @return mixed
     */
    public function setFieldName($fieldName);

    /**
     * @param $refValue
     * @return mixed
     */
    public function fetchRef($refValue);

    /**
     * @param $ref
     * @param $refValue
     * @return mixed
     */
    public function saveRef($ref, $refValue);

    /**
     * @param $ref
     * @param $refValue
     * @return mixed
     */
    public function deleteRef($ref, $refValue = null);

    /**
     * @return MapperInterface
     */
    public function getMapper();

    /**
     * @param MapperInterface $mapper
     * @return mixed
     */
    public function setMapper(MapperInterface $mapper);
}
<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/15/2016
 * Time: 10:24 PM
 */

namespace Dot\Ems\Mapper;

use Zend\Hydrator\HydratorInterface;

/**
 * Interface MapperInterface
 * @package Dot\Ems\Mapper
 */
interface MapperInterface
{
    /**
     * Gets the last inserted id value
     * @return mixed
     */
    public function lastInsertValue();

    /**
     * Begins a backend transaction, may vary between backend types
     * @return mixed
     */
    public function beginTransaction();

    /**
     * Saves the operations from a transaction
     * @return mixed
     */
    public function commit();

    /**
     * In case of error, revert changes to backend
     * @return mixed
     */
    public function rollback();

    /**
     * @param $where
     * @return mixed
     */
    public function fetch($where);

    /**
     * @param array $where
     * @param array $filters
     * @param bool $paginated
     * @return mixed
     */
    public function fetchAll($where = [], $filters = [], $paginated = false);

    /**
     * @param $entity
     * @return mixed
     */
    public function create($entity);

    /**
     * @param $entity
     * @return mixed
     */
    public function update($entity);

    /**
     * @param $where
     * @return mixed
     */
    public function delete($where);

    /**
     * @return object
     */
    public function getPrototype();

    /**
     * @return HydratorInterface
     */
    public function getHydrator();

    /**
     * @return mixed
     */
    public function getIdentifierName();

    /**
     * @param $name
     * @return mixed
     */
    public function setIdentifierName($name);
}

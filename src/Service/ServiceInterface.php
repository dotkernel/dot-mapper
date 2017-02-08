<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/15/2016
 * Time: 10:03 PM
 */

namespace Dot\Ems\Service;

use Dot\Ems\Mapper\MapperInterface;
use Zend\EventManager\EventManagerInterface;

/**
 * Interface ServiceInterface
 * @package Dot\Ems\Service
 */
interface ServiceInterface
{
    /**
     * @param $where
     * @return mixed
     */
    public function find($where);

    /**
     * @param array $where
     * @param array $filters
     * @param bool $paginated
     * @return mixed
     */
    public function findAll($where = [], $filters = [], $paginated = false);

    /**
     * @param $entity
     * @return mixed
     */
    public function save($entity);

    /**
     * @param $where
     * @return mixed
     */
    public function delete($where);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param $name
     * @return mixed
     */
    public function setName($name);

    /**
     * @param bool $value
     * @return mixed
     */
    public function setAtomicOperations($value);

    /**
     * @return bool
     */
    public function isAtomicOperations();

    /**
     * @param bool $value
     * @return mixed
     */
    public function setEnableEvents($value);

    /**
     * @return bool
     */
    public function isEnableEvents();

    /**
     * @return MapperInterface
     */
    public function getMapper();

    /**
     * @param MapperInterface $mapper
     * @return mixed
     */
    public function setMapper(MapperInterface $mapper);

    /**
     * @param EventManagerInterface $eventManager
     * @return mixed
     */
    public function setEventManager(EventManagerInterface $eventManager);
}

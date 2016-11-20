<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/18/2016
 * Time: 3:01 AM
 */

namespace Dot\Ems\Service;

use Dot\Ems\Mapper\MapperInterface;
use Dot\Ems\ObjectPropertyTrait;

/**
 * Class EntityService
 * @package Dot\Ems\Service
 */
class EntityService implements ServiceInterface
{
    use ObjectPropertyTrait;

    /** @var bool  */
    protected $atomicOperations = true;

    /** @var  MapperInterface */
    protected $mapper;

    /**
     * EntityService constructor.
     * @param MapperInterface $mapper
     */
    public function __construct(MapperInterface $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * @param $where
     * @return mixed
     */
    public function find($where)
    {
        return $this->mapper->fetch($where);
    }

    /**
     * @param array $where
     * @param array $filters
     * @param bool $paginated
     * @return mixed
     */
    public function findAll($where = [], $filters = [], $paginated = false)
    {
        return $this->mapper->fetchAll($where, $filters, $paginated);
    }

    /**
     * @param $entity
     * @return mixed
     * @throws \Exception
     */
    public function save($entity)
    {
        try {
            if($this->atomicOperations) {
                $this->mapper->beginTransaction();
            }

            $id = $this->getProperty($entity, $this->mapper->getIdentifierName());
            if($id) {
                $result = $this->mapper->update($entity);
            }
            else {
                $result = $this->mapper->create($entity);
            }

            if($this->atomicOperations) {
                $this->mapper->commit();
            }

            return $result;

        } catch (\Exception $e) {
            if($this->atomicOperations) {
                $this->mapper->rollback();
            }

            throw $e;
        }
    }

    /**
     * @param $entity
     * @throws \Exception
     * @return void
     */
    public function delete($entity)
    {
        try {
            if ($this->atomicOperations) {
                $this->mapper->beginTransaction();
            }

            $this->mapper->delete($entity);

            if($this->atomicOperations) {
                $this->mapper->commit();
            }

        } catch (\Exception $e) {
            if($this->atomicOperations) {
                $this->mapper->rollback();
            }

            throw $e;
        }
    }

    /**
     * @return MapperInterface
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * @param MapperInterface $mapper
     * @return EntityService
     */
    public function setMapper(MapperInterface $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isAtomicOperations()
    {
        return $this->atomicOperations;
    }

    /**
     * @param boolean $atomicOperations
     * @return EntityService
     */
    public function setAtomicOperations($atomicOperations)
    {
        $this->atomicOperations = $atomicOperations;
        return $this;
    }

}
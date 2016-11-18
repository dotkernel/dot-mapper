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
     */
    public function save($entity)
    {
        $id = $this->getProperty($entity, $this->mapper->getIdentifierName());
        if($id) {
            return $this->mapper->update($entity);
        }
        else {
            return $this->mapper->create($entity);
        }
    }

    /**
     * @param $entity
     * @return void
     */
    public function delete($entity)
    {
        $this->mapper->delete($entity);
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



}
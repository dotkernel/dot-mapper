<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/18/2016
 * Time: 3:01 AM
 */

namespace Dot\Ems\Service;

use Dot\Ems\Event\EntityEvent;
use Dot\Ems\Mapper\MapperInterface;
use Dot\Ems\ObjectPropertyTrait;
use Zend\EventManager\EventManagerAwareTrait;

/**
 * Class EntityService
 * @package Dot\Ems\Service
 */
class EntityService implements ServiceInterface
{
    use ObjectPropertyTrait;
    use EventManagerAwareTrait;

    /** @var bool  */
    protected $atomicOperations = true;

    /** @var  MapperInterface */
    protected $mapper;

    /**
     * EntityService constructor.
     * @param MapperInterface|null $mapper
     */
    public function __construct(MapperInterface $mapper = null)
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
     * @return int
     * @throws \Exception
     */
    public function save($entity)
    {
        $type = 0;
        try {
            if($this->atomicOperations) {
                $this->mapper->beginTransaction();
            }

            $id = $this->getProperty($entity, $this->mapper->getIdentifierName());
            if($id) {
                $type = 1;

                $this->getEventManager()->triggerEvent($this->createEntityEvent(
                    EntityEvent::EVENT_ENTITY_UPDATE_PRE, $entity
                ));

                $result = $this->mapper->update($entity);

                $this->getEventManager()->triggerEvent($this->createEntityEvent(
                    EntityEvent::EVENT_ENTITY_UPDATE_POST, $entity
                ));
            }
            else {
                $type = 2;

                $this->getEventManager()->triggerEvent($this->createEntityEvent(
                    EntityEvent::EVENT_ENTITY_CREATE_PRE, $entity
                ));

                $result = $this->mapper->create($entity);

                $this->getEventManager()->triggerEvent($this->createEntityEvent(
                    EntityEvent::EVENT_ENTITY_CREATE_POST, $entity
                ));
            }

            if($this->atomicOperations) {
                $this->mapper->commit();
            }

            return $result;

        } catch (\Exception $e) {
            if($this->atomicOperations) {
                $this->mapper->rollback();
            }

            $name = $type === 1 ? EntityEvent::EVENT_ENTITY_UPDATE_ERROR : EntityEvent::EVENT_ENTITY_CREATE_ERROR;
            $this->getEventManager()->triggerEvent($this->createEntityEvent(
                $name, $entity, $e
            ));


            throw $e;
        }
    }

    /**
     * @param $where
     * @throws \Exception
     * @return int
     */
    public function delete($where)
    {
        try {
            if ($this->atomicOperations) {
                $this->mapper->beginTransaction();
            }

            $this->getEventManager()->triggerEvent($this->createEntityEvent(
                EntityEvent::EVENT_ENTITY_DELETE_PRE, $where
            ));

            $result = $this->mapper->delete($where);

            $this->getEventManager()->triggerEvent($this->createEntityEvent(
                EntityEvent::EVENT_ENTITY_DELETE_POST, $where
            ));

            if($this->atomicOperations) {
                $this->mapper->commit();
            }

            return $result;

        } catch (\Exception $e) {
            if($this->atomicOperations) {
                $this->mapper->rollback();
            }

            $this->getEventManager()->triggerEvent($this->createEntityEvent(
                EntityEvent::EVENT_ENTITY_DELETE_ERROR, $where, $e
            ));

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

    protected function createEntityEvent($name, $data = null, $errors = null, $params = null)
    {
        $event = new EntityEvent($name, $this, $params);

        if($data) {
            $event->setData($data);
        }

        if($errors) {
            $event->setErrors($errors);
        }

        return $event;
    }

}
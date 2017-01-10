<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/18/2016
 * Time: 3:01 AM
 */

namespace Dot\Ems\Service;

use Dot\Ems\Event\EntityServiceEvent;
use Dot\Ems\Event\EntityServiceListenerAwareInterface;
use Dot\Ems\Event\EntityServiceListenerAwareTrait;
use Dot\Ems\Mapper\MapperInterface;
use Dot\Ems\ObjectPropertyTrait;

/**
 * Class EntityService
 * @package Dot\Ems\Service
 */
class EntityService implements ServiceInterface, EntityServiceListenerAwareInterface
{
    use ObjectPropertyTrait;
    use EntityServiceListenerAwareTrait;

    /** @var  string */
    protected $name;

    /** @var bool */
    protected $atomicOperations = true;

    /** @var bool  */
    protected $enableEvents = true;

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
            if ($this->atomicOperations) {
                $this->mapper->beginTransaction();
            }

            $id = $this->getProperty($entity, $this->mapper->getIdentifierName());
            if ($id) {
                $type = 1;

                //trigger pre event
                if ($this->isEnableEvents()) {
                    $this->getEventManager()->triggerEvent($this->createEntityServiceEvent(
                        EntityServiceEvent::EVENT_ENTITY_PRE_UPDATE,
                        $entity
                    ));
                }

                //do the actual operation
                $result = $this->mapper->update($entity);

                //trigger post event
                if ($this->isEnableEvents()) {
                    $this->getEventManager()->triggerEvent($this->createEntityServiceEvent(
                        EntityServiceEvent::EVENT_ENTITY_POST_UPDATE,
                        $entity
                    ));
                }
            } else {
                $type = 2;

                if ($this->isEnableEvents()) {
                    $this->getEventManager()->triggerEvent($this->createEntityServiceEvent(
                        EntityServiceEvent::EVENT_ENTITY_PRE_CREATE,
                        $entity
                    ));
                }

                $result = $this->mapper->create($entity);

                if ($this->isEnableEvents()) {
                    $this->getEventManager()->triggerEvent($this->createEntityServiceEvent(
                        EntityServiceEvent::EVENT_ENTITY_POST_CREATE,
                        $entity
                    ));
                }
            }

            if ($this->atomicOperations) {
                $this->mapper->commit();
            }

            return $result;
        } catch (\Exception $e) {
            if ($this->atomicOperations) {
                $this->mapper->rollback();
            }

            if ($this->isEnableEvents()) {
                $name = ($type === 1)
                    ? EntityServiceEvent::EVENT_ENTITY_UPDATE_ERROR
                    : EntityServiceEvent::EVENT_ENTITY_CREATE_ERROR;

                $this->getEventManager()->triggerEvent($this->createEntityServiceEvent(
                    $name,
                    $entity,
                    $e
                ));
            }

            throw $e;
        }
    }

    protected function createEntityServiceEvent($name, $data = null, $error = null, $params = null)
    {
        $event = new EntityServiceEvent($name, $this, $params);

        if ($data) {
            $event->setData($data);
        }

        if ($error) {
            $event->setError($error);
        }

        return $event;
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

            if ($this->isEnableEvents()) {
                $this->getEventManager()->triggerEvent($this->createEntityServiceEvent(
                    EntityServiceEvent::EVENT_ENTITY_PRE_DELETE,
                    $where
                ));
            }

            $result = $this->mapper->delete($where);

            if ($this->isEnableEvents()) {
                $this->getEventManager()->triggerEvent($this->createEntityServiceEvent(
                    EntityServiceEvent::EVENT_ENTITY_POST_DELETE,
                    $where
                ));
            }

            if ($this->atomicOperations) {
                $this->mapper->commit();
            }

            return $result;
        } catch (\Exception $e) {
            if ($this->atomicOperations) {
                $this->mapper->rollback();
            }

            if ($this->isEnableEvents()) {
                $this->getEventManager()->triggerEvent($this->createEntityServiceEvent(
                    EntityServiceEvent::EVENT_ENTITY_DELETE_ERROR,
                    $where,
                    $e
                ));
            }

            throw $e;
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->getEventManager()->addIdentifiers([$name]);

        return $this;
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

    /**
     * @return boolean
     */
    public function isEnableEvents()
    {
        return $this->enableEvents;
    }

    /**
     * @param boolean $enableEvents
     * @return EntityService
     */
    public function setEnableEvents($enableEvents)
    {
        $this->enableEvents = $enableEvents;
        return $this;
    }
}

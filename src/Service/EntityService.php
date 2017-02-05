<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/18/2016
 * Time: 3:01 AM
 */

declare(strict_types=1);

namespace Dot\Ems\Service;

use Dot\Ems\Event\EntityServiceEvent;
use Dot\Ems\Event\EntityServiceListenerAwareInterface;
use Dot\Ems\Event\EntityServiceListenerAwareTrait;
use Dot\Ems\Exception\RuntimeException;
use Dot\Ems\Mapper\MapperInterface;
use Dot\Ems\ObjectPropertyTrait;
use Dot\Ems\Result\DeleteResult;
use Dot\Ems\Result\FindResult;
use Dot\Ems\Result\SaveResult;

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
    protected $enableEvents = true;

    /** @var  MapperInterface */
    protected $mapper;

    /**
     * EntityService constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        if (isset($options['mapper']) && $options['mapper'] instanceof MapperInterface) {
            $this->setMapper($options['mapper']);
        }

        if (isset($options['enable_events'])) {
            $this->setEnableEvents((bool) $options['enable_events']);
        }

        if (isset($options['name']) && is_string($options['name'])) {
            $this->setName($options['name']);
        }

        if (! $this->mapper) {
            throw new RuntimeException('Mapper object is required and was not set');
        }
    }

    /**
     * @param array $options
     * @return FindResult
     */
    public function find(array $options = []): FindResult
    {
        try {
            $data = $this->mapper->find($options);
            return new FindResult($this, $data);
        } catch (\Exception $e) {
            return new FindResult($this, null, $e);
        }
    }

    /**
     * @param array $options
     * @return FindResult
     */
    public function findAll(array $options = []): FindResult
    {
        try {
            $data = $this->mapper->findAll($options);
            return new FindResult($this, $data);
        } catch (\Exception $e) {
            return new FindResult($this, [], $e);
        }
    }

    /**
     * @param $entity
     * @param array $options
     * @return SaveResult
     * @throws \Exception
     */
    public function save(object $entity, array $options = []): SaveResult
    {
        $type = '';
        try {
            $result = new SaveResult($this, $entity);

            $id = $this->getProperty($entity, $this->mapper->getIdentifierName());
            if ($id) {
                $type = 'update';

                //trigger pre event
                if ($this->isEnableEvents()) {
                    $this->getEventManager()->triggerEvent($this->createEntityServiceEvent(
                        EntityServiceEvent::EVENT_ENTITY_PRE_UPDATE,
                        $entity
                    ));
                }

                //do the actual operation
                $affectedRows = $this->mapper->update($entity, $options);

                //trigger post event
                if ($this->isEnableEvents()) {
                    $this->getEventManager()->triggerEvent($this->createEntityServiceEvent(
                        EntityServiceEvent::EVENT_ENTITY_POST_UPDATE,
                        $entity
                    ));
                }
            } else {
                $type = 'create';

                if ($this->isEnableEvents()) {
                    $this->getEventManager()->triggerEvent($this->createEntityServiceEvent(
                        EntityServiceEvent::EVENT_ENTITY_PRE_CREATE,
                        $entity
                    ));
                }

                $affectedRows = $this->mapper->create($entity, $options);

                if ($this->isEnableEvents()) {
                    $this->getEventManager()->triggerEvent($this->createEntityServiceEvent(
                        EntityServiceEvent::EVENT_ENTITY_POST_CREATE,
                        $entity
                    ));
                }
            }

            $result->setAffectedRows($affectedRows);
            $result->setData($entity);

            return $result;
        } catch (\Exception $e) {
            if ($this->isEnableEvents()) {
                $name = ($type === 'update')
                    ? EntityServiceEvent::EVENT_ENTITY_UPDATE_ERROR
                    : EntityServiceEvent::EVENT_ENTITY_CREATE_ERROR;

                $this->getEventManager()->triggerEvent($this->createEntityServiceEvent(
                    $name,
                    $entity,
                    $e
                ));
            }

            return new SaveResult($this, $entity, $e);
        }
    }

    /**
     * @param object|null $entity
     * @param array $options
     * @return DeleteResult
     */
    public function deleteEntity(object $entity, array $options = []): DeleteResult
    {
        $id = $this->getProperty($entity, $this->mapper->getIdentifierName());
        $options['condition'] = [];
        if ($id) {
            $options['condition'] = [$this->mapper->getIdentifierName() => $id];
        }

        $result = $this->delete($options);
        $result->setData($entity);

        return $result;
    }

    public function delete(array $options = []): DeleteResult
    {
        try {
            $result = new DeleteResult($this, $options);

            if ($this->isEnableEvents()) {
                $this->getEventManager()->triggerEvent($this->createEntityServiceEvent(
                    EntityServiceEvent::EVENT_ENTITY_PRE_DELETE,
                    $options
                ));
            }

            $affectedRows = $this->mapper->delete($options);

            if ($this->isEnableEvents()) {
                $this->getEventManager()->triggerEvent($this->createEntityServiceEvent(
                    EntityServiceEvent::EVENT_ENTITY_POST_DELETE,
                    $options
                ));
            }

            $result->setAffectedRows($affectedRows);
            return $result;
        } catch (\Exception $e) {
            if ($this->isEnableEvents()) {
                $this->getEventManager()->triggerEvent($this->createEntityServiceEvent(
                    EntityServiceEvent::EVENT_ENTITY_DELETE_ERROR,
                    $options,
                    $e
                ));
            }

            return new DeleteResult($this, $options, $e);
        }
    }

    /**
     * @return boolean
     */
    public function isEnableEvents(): bool
    {
        return $this->enableEvents;
    }

    /**
     * @param boolean $enableEvents
     */
    public function setEnableEvents(bool $enableEvents)
    {
        $this->enableEvents = $enableEvents;
    }

    /**
     * @param $name
     * @param string $data
     * @param mixed $error
     * @param array $params
     * @return EntityServiceEvent
     */
    protected function createEntityServiceEvent(
        string $name,
        $data = null,
        $error = null,
        array $params = null
    ): EntityServiceEvent {
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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
        $this->getEventManager()->addIdentifiers([$name]);
    }

    /**
     * @return MapperInterface
     */
    public function getMapper(): MapperInterface
    {
        return $this->mapper;
    }

    /**
     * @param MapperInterface $mapper
     */
    public function setMapper(MapperInterface $mapper)
    {
        $this->mapper = $mapper;
    }
}

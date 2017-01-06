<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 1/6/2017
 * Time: 2:47 AM
 */

namespace Dot\Ems\Event;


use Dot\Event\Event;

/**
 * Class EntityEvent
 * @package Dot\Ems\Event
 */
class EntityEvent extends Event
{
    const EVENT_ENTITY_CREATE_PRE = 'event.entity.create.pre';
    const EVENT_ENTITY_CREATE_POST = 'event.entity.create.post';
    const EVENT_ENTITY_CREATE_ERROR = 'event.entity.create.error';

    const EVENT_ENTITY_UPDATE_PRE = 'event.entity.update.pre';
    const EVENT_ENTITY_UPDATE_POST = 'event.entity.update.post';
    const EVENT_ENTITY_UPDATE_ERROR = 'event.entity.update.error';

    const EVENT_ENTITY_DELETE_PRE = 'event.entity.delete.pre';
    const EVENT_ENTITY_DELETE_POST = 'event.entity.delete.post';
    const EVENT_ENTITY_DELETE_ERROR = 'event.entity.delete.error';

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var mixed
     */
    protected $errors;

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     * @return EntityEvent
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param mixed $errors
     * @return EntityEvent
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
        return $this;
    }

}
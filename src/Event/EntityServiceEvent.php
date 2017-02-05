<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 1/6/2017
 * Time: 2:47 AM
 */

declare(strict_types=1);

namespace Dot\Ems\Event;

use Dot\Event\Event;

/**
 * Class EntityEvent
 * @package Dot\Ems\Event
 */
class EntityServiceEvent extends Event
{
    const EVENT_ENTITY_PRE_CREATE = 'event.entity.pre.create';
    const EVENT_ENTITY_POST_CREATE = 'event.entity.post.create';
    const EVENT_ENTITY_CREATE_ERROR = 'event.entity.create.error';

    const EVENT_ENTITY_PRE_UPDATE = 'event.entity.pre.update';
    const EVENT_ENTITY_POST_UPDATE = 'event.entity.post.update';
    const EVENT_ENTITY_UPDATE_ERROR = 'event.entity.update.error';

    const EVENT_ENTITY_PRE_DELETE = 'event.entity.pre.delete';
    const EVENT_ENTITY_POST_DELETE = 'event.entity.post.delete';
    const EVENT_ENTITY_DELETE_ERROR = 'event.entity.delete.error';

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var mixed
     */
    protected $error;

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return $this->error !== null;
    }

    /**
     * @return bool
     */
    public function hasException(): bool
    {
        return $this->error instanceof \Exception;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param mixed $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }
}

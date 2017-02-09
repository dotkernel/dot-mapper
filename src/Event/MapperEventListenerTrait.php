<?php
/**
 * @copyright: DotKernel
 * @library: dot-ems
 * @author: n3vrax
 * Date: 2/9/2017
 * Time: 8:30 PM
 */

declare(strict_types = 1);

namespace Dot\Ems\Event;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateTrait;

/**
 * Class MapperEventListenerTrait
 * @package Dot\Ems\Event
 */
trait MapperEventListenerTrait
{
    use ListenerAggregateTrait;

    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(
            MapperEvent::EVENT_MAPPER_BEFORE_FIND,
            [$this, 'onBeforeFind'],
            $priority
        );
        $this->listeners[] = $events->attach(
            MapperEvent::EVENT_MAPPER_BEFORE_LOAD,
            [$this, 'onBeforeLoad'],
            $priority
        );
        $this->listeners[] = $events->attach(
            MapperEvent::EVENT_MAPPER_AFTER_LOAD,
            [$this, 'onAfterLoad'],
            $priority
        );
        $this->listeners[] = $events->attach(
            MapperEvent::EVENT_MAPPER_BEFORE_SAVE,
            [$this, 'onBeforeSave'],
            $priority
        );
        $this->listeners[] = $events->attach(
            MapperEvent::EVENT_MAPPER_AFTER_SAVE,
            [$this, 'onAfterSave'],
            $priority
        );
        $this->listeners[] = $events->attach(
            MapperEvent::EVENT_MAPPER_AFTER_SAVE_COMMIT,
            [$this, 'onAfterSaveCommit'],
            $priority
        );
        $this->listeners[] = $events->attach(
            MapperEvent::EVENT_MAPPER_BEFORE_DELETE,
            [$this, 'onBeforeDelete'],
            $priority
        );
        $this->listeners[] = $events->attach(
            MapperEvent::EVENT_MAPPER_AFTER_DELETE,
            [$this, 'onAfterDelete'],
            $priority
        );
        $this->listeners[] = $events->attach(
            MapperEvent::EVENT_MAPPER_AFTER_DELETE_COMMIT,
            [$this, 'onAfterDeleteCommit'],
            $priority
        );
    }

    public function onBeforeFind(MapperEvent $e)
    {
        // NO-OP: Implement onBeforeFind() method.
    }

    public function onBeforeLoad(MapperEvent $e)
    {
        // NO-OP: Implement onBeforeLoad() method.
    }

    public function onAfterLoad(MapperEvent $e)
    {
        // NO-OP: Implement onAfterLoad() method.
    }

    public function onBeforeSave(MapperEvent $e)
    {
        // NO-OP: Implement onBeforeSave() method.
    }

    public function onAfterSave(MapperEvent $e)
    {
        // NO-OP: Implement onAfterSave() method.
    }

    public function onAfterSaveCommit(MapperEvent $e)
    {
        // NO-OP: Implement onAfterSaveCommit() method.
    }

    public function onBeforeDelete(MapperEvent $e)
    {
        // NO-OP: Implement onBeforeDelete() method.
    }

    public function onAfterDelete(MapperEvent $e)
    {
        // NO-OP: Implement onAfterDelete() method.
    }

    public function onAfterDeleteCommit(MapperEvent $e)
    {
        // NO-OP: Implement onAfterDeleteCommit() method.
    }
}

<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 1/10/2017
 * Time: 8:08 PM
 */

declare(strict_types=1);

namespace Dot\Ems\Event;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;

/**
 * Class AbstractEntityServiceListener
 * @package Dot\Ems\Event
 */
abstract class AbstractEntityServiceListener extends AbstractListenerAggregate implements
    EntityServiceListenerInterface
{
    /**
     * @param EventManagerInterface $events
     * @param int $priority
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(
            EntityServiceEvent::EVENT_ENTITY_PRE_CREATE,
            [$this, 'onPreCreate'],
            $priority
        );

        $this->listeners[] = $events->attach(
            EntityServiceEvent::EVENT_ENTITY_POST_CREATE,
            [$this, 'onPostCreate'],
            $priority
        );

        $this->listeners[] = $events->attach(
            EntityServiceEvent::EVENT_ENTITY_CREATE_ERROR,
            [$this, 'onCreateError'],
            $priority
        );

        $this->listeners[] = $events->attach(
            EntityServiceEvent::EVENT_ENTITY_PRE_UPDATE,
            [$this, 'onPreUpdate'],
            $priority
        );

        $this->listeners[] = $events->attach(
            EntityServiceEvent::EVENT_ENTITY_POST_UPDATE,
            [$this, 'onPostUpdate'],
            $priority
        );

        $this->listeners[] = $events->attach(
            EntityServiceEvent::EVENT_ENTITY_UPDATE_ERROR,
            [$this, 'onUpdateError'],
            $priority
        );

        $this->listeners[] = $events->attach(
            EntityServiceEvent::EVENT_ENTITY_PRE_DELETE,
            [$this, 'onPreDelete'],
            $priority
        );

        $this->listeners[] = $events->attach(
            EntityServiceEvent::EVENT_ENTITY_POST_DELETE,
            [$this, 'onPostDelete'],
            $priority
        );

        $this->listeners[] = $events->attach(
            EntityServiceEvent::EVENT_ENTITY_DELETE_ERROR,
            [$this, 'onDeleteError'],
            $priority
        );
    }

    public function onPreCreate(EntityServiceEvent $e)
    {
        // noop - left for implementors
    }

    public function onPostCreate(EntityServiceEvent $e)
    {
        // noop - left for implementors
    }

    public function onCreateError(EntityServiceEvent $e)
    {
        // noop - left for implementors
    }

    public function onPreUpdate(EntityServiceEvent $e)
    {
        // noop - left for implementors
    }

    public function onPostUpdate(EntityServiceEvent $e)
    {
        // noop - left for implementors
    }

    public function onUpdateError(EntityServiceEvent $e)
    {
        // noop - left for implementors
    }

    public function onPreDelete(EntityServiceEvent $e)
    {
        // noop - left for implementors
    }

    public function onPostDelete(EntityServiceEvent $e)
    {
        // noop - left for implementors
    }

    public function onDeleteError(EntityServiceEvent $e)
    {
        // noop - left for implementors
    }
}

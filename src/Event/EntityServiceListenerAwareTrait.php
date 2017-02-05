<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 1/10/2017
 * Time: 8:03 PM
 */

declare(strict_types=1);

namespace Dot\Ems\Event;

use Zend\EventManager\EventManagerAwareTrait;

/**
 * Class EntityServiceListenerAwareTrait
 * @package Dot\Ems\Event
 */
trait EntityServiceListenerAwareTrait
{
    use EventManagerAwareTrait;

    /** @var EntityServiceListenerInterface[] */
    protected $listeners = [];

    /**
     * @param EntityServiceListenerInterface $listener
     * @param int $priority
     * @return $this
     */
    public function attachListener(EntityServiceListenerInterface $listener, $priority = 1)
    {
        $listener->attach($this->getEventManager(), $priority);
        $this->listeners[] = $listener;
        return $this;
    }

    /**
     * @param EntityServiceListenerInterface $listener
     * @return $this
     */
    public function detachListener(EntityServiceListenerInterface $listener)
    {
        $listener->detach($this->getEventManager());

        $idx = 0;
        foreach ($this->listeners as $l) {
            if ($l === $listener) {
                break;
            }

            $idx++;
        }

        unset($this->listeners[$idx]);
        return $this;
    }

    /**
     * @return $this
     */
    public function clearListeners()
    {
        foreach ($this->listeners as $listener) {
            $listener->detach($this->getEventManager());
        }

        $this->listeners = [];
        return $this;
    }
}

<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 1/10/2017
 * Time: 8:02 PM
 */

declare(strict_types=1);

namespace Dot\Ems\Event;

/**
 * Interface EntityServiceListenerAwareInterface
 * @package Dot\Ems\Event
 */
interface EntityServiceListenerAwareInterface
{
    /**
     * @param EntityServiceListenerInterface $listener
     * @param int $priority
     * @return mixed
     */
    public function attachListener(EntityServiceListenerInterface $listener, $priority = 1);

    /**
     * @param EntityServiceListenerInterface $listener
     * @return mixed
     */
    public function detachListener(EntityServiceListenerInterface $listener);

    /**
     * @return mixed
     */
    public function clearListeners();
}

<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 1/10/2017
 * Time: 7:58 PM
 */

namespace Dot\Ems\Event;

use Zend\EventManager\ListenerAggregateInterface;

/**
 * Interface EntityServiceListenerInterface
 * @package Dot\Ems\Event
 */
interface EntityServiceListenerInterface extends ListenerAggregateInterface
{
    /**
     * @param EntityServiceEvent $e
     * @return mixed
     */
    public function onPreCreate(EntityServiceEvent $e);

    /**
     * @param EntityServiceEvent $e
     * @return mixed
     */
    public function onPostCreate(EntityServiceEvent $e);

    /**
     * @param EntityServiceEvent $e
     * @return mixed
     */
    public function onCreateError(EntityServiceEvent $e);

    /**
     * @param EntityServiceEvent $e
     * @return mixed
     */
    public function onPreUpdate(EntityServiceEvent $e);

    /**
     * @param EntityServiceEvent $e
     * @return mixed
     */
    public function onPostUpdate(EntityServiceEvent $e);

    /**
     * @param EntityServiceEvent $e
     * @return mixed
     */
    public function onUpdateError(EntityServiceEvent $e);

    /**
     * @param EntityServiceEvent $e
     * @return mixed
     */
    public function onPreDelete(EntityServiceEvent $e);

    /**
     * @param EntityServiceEvent $e
     * @return mixed
     */
    public function onPostDelete(EntityServiceEvent $e);

    /**
     * @param EntityServiceEvent $e
     * @return mixed
     */
    public function onDeleteError(EntityServiceEvent $e);
}

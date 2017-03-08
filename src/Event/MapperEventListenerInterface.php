<?php
/**
 * @copyright: DotKernel
 * @library: dot-ems
 * @author: n3vrax
 * Date: 2/9/2017
 * Time: 8:13 PM
 */

declare(strict_types = 1);

namespace Dot\Mapper\Event;

use Zend\EventManager\ListenerAggregateInterface;

/**
 * Interface MapperEventListenerInterface
 * @package Dot\Ems\Event
 */
interface MapperEventListenerInterface extends ListenerAggregateInterface
{
    public function onBeforeFind(MapperEvent $e);

    public function onAfterFind(MapperEvent $e);

    public function onBeforeLoad(MapperEvent $e);

    public function onAfterLoad(MapperEvent $e);

    public function onBeforeSave(MapperEvent $e);

    public function onAfterSave(MapperEvent $e);

    public function onAfterSaveCommit(MapperEvent $e);

    public function onBeforeDelete(MapperEvent $e);

    public function onAfterDelete(MapperEvent $e);

    public function onAfterDeleteCommit(MapperEvent $e);
}

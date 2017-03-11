<?php
/**
 * @see https://github.com/dotkernel/dot-mapper/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-mapper/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Mapper\Event;

use Zend\EventManager\ListenerAggregateInterface;

/**
 * Interface MapperEventListenerInterface
 * @package Dot\Mapper\Event
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

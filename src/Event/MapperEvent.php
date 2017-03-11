<?php
/**
 * @see https://github.com/dotkernel/dot-mapper/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-mapper/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Mapper\Event;

use Dot\Event\Event;

/**
 * Class MapperEvent
 * @package Dot\Mapper\Event
 */
class MapperEvent extends Event
{
    const EVENT_MAPPER_BEFORE_FIND = 'event.mapper.beforeFind';
    const EVENT_MAPPER_AFTER_FIND = 'event.mapper.afterFind';
    const EVENT_MAPPER_BEFORE_LOAD = 'event.mapper.beforeLoad';
    const EVENT_MAPPER_AFTER_LOAD = 'event.mapper.afterLoad';
    const EVENT_MAPPER_BEFORE_SAVE = 'event.mapper.beforeSave';
    const EVENT_MAPPER_AFTER_SAVE = 'event.mapper.afterSave';
    const EVENT_MAPPER_AFTER_SAVE_COMMIT = 'event.mapper.afterSaveCommit';
    const EVENT_MAPPER_BEFORE_DELETE = 'event.mapper.beforeDelete';
    const EVENT_MAPPER_AFTER_DELETE = 'event.mapper.afterDelete';
    const EVENT_MAPPER_AFTER_DELETE_COMMIT = 'event.mapper.afterDeleteCommit';
}

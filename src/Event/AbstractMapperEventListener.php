<?php
/**
 * @see https://github.com/dotkernel/dot-mapper/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-mapper/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Mapper\Event;

use Zend\EventManager\AbstractListenerAggregate;

/**
 * Class AbstractMapperEventListener
 * @package Dot\Mapper\Event
 */
abstract class AbstractMapperEventListener extends AbstractListenerAggregate implements MapperEventListenerInterface
{
    use MapperEventListenerTrait;
}

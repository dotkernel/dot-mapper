<?php
/**
 * @see https://github.com/dotkernel/dot-mapper/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-mapper/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Mapper\Event;

use Dot\Mapper\Exception\RuntimeException;
use Dot\Mapper\Mapper\MapperInterface;
use Laminas\EventManager\EventManagerAwareTrait;

/**
 * Class DispatchMapperEventsTrait
 * @package Dot\Mapper\Event
 */
trait DispatchMapperEventsTrait
{
    use EventManagerAwareTrait;

    /**
     * @param string $name
     * @param array $data
     * @param mixed $target
     * @return \Laminas\EventManager\ResponseCollection
     */
    public function dispatchEvent(string $name, array $data = [], $target = null)
    {
        if (!$this instanceof MapperInterface) {
            throw new RuntimeException('Only a MapperInterface instance can dispatch mapper events');
        }

        if ($target === null) {
            $target = $this;
        }

        $event = new MapperEvent($name, $target, $data);
        return $this->getEventManager()->triggerEventUntil(function ($r) {
            return $r !== null;
        }, $event);
    }
}

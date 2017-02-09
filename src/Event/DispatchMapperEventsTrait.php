<?php
/**
 * @copyright: DotKernel
 * @library: dot-ems
 * @author: n3vrax
 * Date: 2/9/2017
 * Time: 7:57 PM
 */

declare(strict_types = 1);

namespace Dot\Ems\Event;

use Dot\Ems\Exception\RuntimeException;
use Dot\Ems\Mapper\MapperInterface;
use Zend\EventManager\EventManagerAwareTrait;

/**
 * Class DispatchMapperEventsTrait
 * @package Dot\Ems\Event
 */
trait DispatchMapperEventsTrait
{
    use EventManagerAwareTrait;

    /**
     * @param string $name
     * @param array $data
     * @param mixed $target
     * @return \Zend\EventManager\ResponseCollection
     */
    public function dispatchEvent(string $name, array $data = [], $target = null)
    {
        if (!$this instanceof MapperInterface) {
            throw new RuntimeException('Only MapperInterface can dispatch mapper events');
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

<?php
/**
 * @copyright: DotKernel
 * @library: dot-ems
 * @author: n3vrax
 * Date: 2/9/2017
 * Time: 8:16 PM
 */

declare(strict_types = 1);

namespace Dot\Mapper\Event;

use Zend\EventManager\AbstractListenerAggregate;

/**
 * Class AbstractMapperEventListener
 * @package Dot\Ems\Event
 */
abstract class AbstractMapperEventListener extends AbstractListenerAggregate implements MapperEventListenerInterface
{
    use MapperEventListenerTrait;
}

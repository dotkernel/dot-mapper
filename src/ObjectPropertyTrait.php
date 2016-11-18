<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/18/2016
 * Time: 1:39 AM
 */

namespace Dot\Ems;

/**
 * Class ObjectPropertyTrait
 * @package Dot\Ems
 */
trait ObjectPropertyTrait
{
    /**
     * @param $object
     * @param $property
     * @return mixed
     */
    protected function getProperty($object, $property)
    {
        return call_user_func([$object, $this->getter($property)]);
    }

    /**
     * @param $object
     * @param $property
     * @param $value
     */
    protected function setProperty($object, $property, $value)
    {
        call_user_func([$object, $this->setter($property)], $value);
    }

    /**
     * @param $propertyName
     * @return string
     */
    protected function setter($propertyName)
    {
        return 'set' . ucfirst($propertyName);
    }

    /**
     * @param $propertyName
     * @return string
     */
    protected function getter($propertyName)
    {
        return 'get' . ucfirst($propertyName);
    }
}
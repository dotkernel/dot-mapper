<?php
/**
 * @copyright: DotKernel
 * @library: dot-ems
 * @author: n3vrax
 * Date: 2/8/2017
 * Time: 4:48 PM
 */

declare(strict_types = 1);

namespace Dot\Ems\Entity;

use Dot\Hydrator\ClassMethodsCamelCase;

/**
 * Class Entity
 * @package Dot\Ems\Entity
 */
abstract class Entity implements EntityInterface
{
    /** @var string */
    protected $hydrator = ClassMethodsCamelCase::class;

    /**
     * @return string
     */
    public function hydrator(): string
    {
        return $this->hydrator;
    }

    /**
     * @param array $properties
     */
    public function unsetProperties(array $properties)
    {
        foreach ($properties as $property) {
            if (property_exists($this, $property)) {
                $this->{$properties} = null;
            }
        }
    }

    /**
     * @param array $properties
     * @return bool
     */
    public function hasProperties(array $properties): bool
    {
        $has = true;
        foreach ($properties as $property) {
            if (!property_exists($this, $property)) {
                $has = false;
                break;
            }
        }
        return $has;
    }

    /**
     * @param array $properties
     * @return array
     */
    public function extractProperties(array $properties): array
    {
        $result = [];
        foreach ($properties as $property) {
            if (!property_exists($this, $property)) {
                $result[$property] = $this->{$property};
            }
        }
        return $result;
    }
}

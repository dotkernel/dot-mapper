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
    /** @var array */
    protected $ignoreProperties = ['hydrator'];

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
    public function unsetProperties(array $properties = [])
    {
        if (empty($properties)) {
            $properties = array_diff(array_keys(get_object_vars($this)), $this->ignoreProperties);
        }

        foreach ($properties as $property) {
            if (property_exists($this, $property)) {
                $this->{$properties} = null;
            }
        }
    }

    /**
     * @param string $property
     */
    public function unsetProperty(string $property)
    {
        $this->unsetProperties([$property]);
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
     * @param string $property
     * @return bool
     */
    public function hasProperty(string $property): bool
    {
        return $this->hasProperties([$property]);
    }

    /**
     * @param array $properties
     * @return array
     */
    public function extractProperties(array $properties = []): array
    {
        $result = [];
        if (empty($properties)) {
            $properties = array_diff(array_keys(get_object_vars($this)), $this->ignoreProperties);
        }

        foreach ($properties as $property) {
            if (property_exists($this, $property)) {
                $result[$property] = $this->{$property};
            }
        }
        return $result;
    }

    /**
     * @param string $property
     * @return mixed|null
     */
    public function extractProperty(string $property)
    {
        if ($this->hasProperty($property)) {
            return $this->extractProperties([$property])[$property];
        }

        return null;
    }

    /**
     * @return array
     */
    public function getIgnoredProperties(): array
    {
        return $this->ignoreProperties;
    }

    /**
     * @param array $ignoredProperties
     */
    public function setIgnoredProperties(array $ignoredProperties)
    {
        $this->ignoreProperties = $ignoredProperties;
    }

    /**
     * @param array $ignoredProperties
     */
    public function addIgnoredProperties(array $ignoredProperties)
    {
        $this->ignoreProperties = array_merge($this->ignoreProperties, $ignoredProperties);
    }
}

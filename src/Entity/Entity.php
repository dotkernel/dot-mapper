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

use Dot\Ems\Mapper\MapperInterface;
use Dot\Hydrator\ClassMethodsCamelCase;

/**
 * Class Entity
 * @package Dot\Ems\Entity
 */
abstract class Entity implements EntityInterface
{
    /** @var array */
    protected $ignoreProperties = ['hydrator', 'mapper'];

    /** @var string */
    protected $hydrator = ClassMethodsCamelCase::class;

    /** @var  MapperInterface */
    protected $mapper;

    /**
     * Entity constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        if (isset($options['mapper']) && $options['mapper'] instanceof MapperInterface) {
            $this->setMapper($options['mapper']);
        }
    }

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
    public function extractProperties(array $properties = []): array
    {
        $result = [];
        if (empty($properties)) {
            $properties = array_diff(array_keys(get_object_vars($this)), $this->ignoreProperties);
        }

        foreach ($properties as $property) {
            if (!property_exists($this, $property)) {
                $result[$property] = $this->{$property};
            }
        }
        return $result;
    }

    /**
     * @param array $options
     * @return mixed
     */
    public function save(array $options = [])
    {
        return $this->getMapper()->save($this, $options);
    }

    /**
     * @param array $options
     * @return mixed
     */
    public function delete(array $options = [])
    {
        return $this->getMapper()->delete($this, $options);
    }

    /**
     * @return MapperInterface
     */
    public function getMapper(): MapperInterface
    {
        return $this->mapper;
    }

    /**
     * @param MapperInterface $mapper
     */
    public function setMapper(MapperInterface $mapper)
    {
        $this->mapper = $mapper;
    }
}

<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/17/2016
 * Time: 10:57 PM
 */

namespace Dot\Ems\Mapper\Relation;

use Dot\Ems\Mapper\MapperInterface;
use Dot\Ems\ObjectPropertyTrait;

/**
 * Class AbstractRelation
 * @package Dot\Ems\Mapper\Relation
 */
abstract class AbstractRelation implements RelationInterface
{
    use ObjectPropertyTrait;

    /** @var  MapperInterface */
    protected $mapper;

    /** @var  string */
    protected $refName;

    /** @var  int */
    protected $type;

    /** @var  string */
    protected $fieldName;

    /** @var bool */
    protected $deleteRefs = false;

    /** @var bool */
    protected $changeRefs = true;

    /**
     * AbstractRelation constructor.
     * @param MapperInterface|null $mapper
     * @param null $refName
     * @param null $fieldName
     */
    public function __construct(MapperInterface $mapper, $refName, $fieldName)
    {
        $this->mapper = $mapper;
        $this->refName = $refName;
        $this->fieldName = $fieldName;
    }

    /**
     * @return MapperInterface
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * @param MapperInterface $mapper
     * @return $this
     */
    public function setMapper(MapperInterface $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * @return string
     */
    public function getRefName()
    {
        return $this->refName;
    }

    /**
     * @param $refName
     * @return $this
     */
    public function setRefName($refName)
    {
        $this->refName = $refName;
        return $this;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @param string $fieldName
     * @return AbstractRelation
     */
    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDeleteRefs()
    {
        return $this->deleteRefs;
    }

    /**
     * @param boolean $deleteRefs
     * @return AbstractRelation
     */
    public function setDeleteRefs($deleteRefs)
    {
        $this->deleteRefs = $deleteRefs;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isChangeRefs()
    {
        return $this->changeRefs;
    }

    /**
     * @param boolean $changeRefs
     * @return AbstractRelation
     */
    public function setChangeRefs($changeRefs)
    {
        $this->changeRefs = $changeRefs;
        return $this;
    }
}

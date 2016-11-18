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

    /**
     * AbstractRelationMapper constructor.
     * @param MapperInterface $mapper
     * @param $refName
     */
    public function __construct(MapperInterface $mapper, $refName)
    {
        $this->mapper = $mapper;
        $this->refName = $refName;
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
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }
}
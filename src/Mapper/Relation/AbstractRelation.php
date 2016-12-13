<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/17/2016
 * Time: 10:57 PM
 */

namespace Dot\Ems\Mapper\Relation;

use Dot\Ems\Exception\InvalidArgumentException;
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

    /**
     * AbstractRelation constructor.
     * @param MapperInterface|null $mapper
     * @param null $refName
     * @param null $fieldName
     */
    public function __construct(MapperInterface $mapper = null, $refName = null, $fieldName = null)
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
     * @param $data
     * @param $refValue
     * @return int|mixed
     */
    public function saveRef($data, $refValue)
    {
        $affectedRows = 0;
        if(is_object($data)) {
            $id = $this->getProperty($data, $this->getMapper()->getIdentifierName());
            if(!$id) {
                $this->setProperty($data, $this->getRefName(), $refValue);
                $affectedRows = $this->getMapper()->create($data);
            }
            else {
                $affectedRows = $this->getMapper()->update($data);
            }
        }
        elseif(is_array($data)) {
            $toDelete = [];
            $originalRefs = $this->fetchRef($refValue);
            foreach ($originalRefs as $ref) {
                $id = $this->getProperty($ref, $this->getMapper()->getIdentifierName());
                $toDelete[$id] = $ref;
            }

            foreach ($data as $entity) {
                if(!is_object($entity)) {
                    throw new InvalidArgumentException('Entity collection contains invalid entities');
                }

                $id = $this->getProperty($entity, $this->getMapper()->getIdentifierName());
                if(!$id) {
                    $this->setProperty($entity, $this->getRefName(), $refValue);
                    $affectedRows += $this->getMapper()->create($entity);
                }
                else {
                    if(isset($toDelete[$id])) {
                        unset($toDelete[$id]);
                    }
                    $affectedRows += $this->getMapper()->update($entity);
                }
            }

            $affectedRows += $this->deleteRef($toDelete);
        }
        else {
            throw new InvalidArgumentException('Invalid parameter. Must be an object or an array of objects to save');
        }

        return $affectedRows;
    }

    /**
     * @param $data
     * @param null $parentId
     * @return int|mixed
     */
    public function deleteRef($data, $parentId = null)
    {
        $affectedRows = 0;
        if(is_scalar($data)) {
            //we delete all entities bulk, consider $data as the refValue to delete
            $affectedRows = $this->getMapper()->delete([$this->getRefName() => $data]);
        }
        elseif(is_array($data)) {
            foreach ($data as $entity) {
                if (!is_object($entity)) {
                    throw new InvalidArgumentException('Entity collection contains invalid entities');
                }

                $id = $this->getProperty($entity, $this->getMapper()->getIdentifierName());
                if($id) {
                    $affectedRows += $this->getMapper()->delete($entity);
                }
            }
        }
        elseif(is_object($data)) {
            $id = $this->getProperty($data, $this->getMapper()->getIdentifierName());
            if($id) {
                $affectedRows += $this->getMapper()->delete($data);
            }
        }
        else {
            throw new InvalidArgumentException('Invalid parameter entity to delete.');
        }

        return $affectedRows;
    }

}
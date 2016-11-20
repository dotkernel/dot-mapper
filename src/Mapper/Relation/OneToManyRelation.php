<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/17/2016
 * Time: 11:01 PM
 */

namespace Dot\Ems\Mapper\Relation;

use Dot\Ems\Exception\InvalidArgumentException;

/**
 * Class OneToManyRelation
 * @package Dot\Ems\Mapper\Relation
 */
class OneToManyRelation extends AbstractRelation
{
    protected $refType = RelationInterface::ONE_TO_MANY;

    /**
     * @param $refValue
     * @return mixed
     */
    public function fetchRef($refValue)
    {
        $refs = $this->getMapper()->fetchAll([$this->getRefName() => $refValue]);
        if($refs) {
            return $refs;
        }

        return null;
    }

    /**
     * @param $entities
     * @param $refValue
     * @return void
     */
    public function saveRef($entities, $refValue)
    {
        if(!is_array($entities)) {
            throw new InvalidArgumentException('Entity must be an array of objects');
        }

        foreach ($entities as $entity) {
            if(!is_object($entity)) {
                throw new InvalidArgumentException('Entity collection contains invalid entities');
            }

            $this->setProperty($entity, $this->getRefName(), $refValue);
            $id = $this->getProperty($entity, $this->getMapper()->getIdentifierName());
            if(!$id) {
                $id = $this->getMapper()->create($entity);
                $this->setProperty($entity, $this->getMapper()->getIdentifierName(), $id);
            }
            else {
                $this->getMapper()->update($entity);
            }
        }
    }

    /**
     * @param $entities
     * @return void
     */
    public function deleteRef($entities)
    {
        if(!is_array($entities)) {
            throw new InvalidArgumentException('Entity must be an array of objects');
        }

        foreach ($entities as $entity) {
            if (!is_object($entity)) {
                throw new InvalidArgumentException('Entity collection contains invalid entities');
            }

            $id = $this->getProperty($entity, $this->getMapper()->getIdentifierName());
            if($id) {
                $this->getMapper()->delete($entity);
            }
        }
    }
}
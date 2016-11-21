<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/17/2016
 * Time: 10:56 PM
 */

namespace Dot\Ems\Mapper\Relation;

use Dot\Ems\Exception\InvalidArgumentException;

/**
 * Class OneToOneRelation
 * @package Dot\Ems\Mapper\Relation
 */
class OneToOneRelation extends AbstractRelation
{
    protected $type = RelationInterface::ONE_TO_ONE;

    /**
     * @param $refValue
     * @return mixed
     */
    public function fetchRef($refValue)
    {
        $ref = $this->getMapper()->fetch([$this->getRefName() => $refValue]);
        if($ref) {
            return $ref;
        }

        return null;
    }

    /**
     * @param $entity
     * @param $refValue
     * @return int
     */
    public function saveRef($entity, $refValue)
    {
        if(!is_object($entity)) {
            throw new InvalidArgumentException('Entity must be an object value');
        }

        $id = $this->getProperty($entity, $this->getMapper()->getIdentifierName());
        if(!$id) {
            $this->setProperty($entity, $this->getRefName(), $refValue);
            return $this->getMapper()->create($entity);
        }
        else {
            return $this->getMapper()->update($entity);
        }
    }

    /**
     * @param $entity
     * @return int
     */
    public function deleteRef($entity)
    {
        if(!is_object($entity)) {
            throw new InvalidArgumentException('Entity must be an object value');
        }

        $id = $this->getProperty($entity, $this->getMapper()->getIdentifierName());
        if($id) {
            return $this->getMapper()->delete($entity);
        }

        return 0;
    }
}
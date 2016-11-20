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
     * @return void
     */
    public function saveRef($entity, $refValue)
    {
        if(!is_object($entity)) {
            throw new InvalidArgumentException('Entity must be an object value');
        }

        $id = $this->getProperty($entity, $this->getMapper()->getIdentifierName());
        if(!$id) {
            $this->setProperty($entity, $this->getRefName(), $refValue);

            $id = $this->getMapper()->create($entity);
            $this->setProperty($entity, $this->getMapper()->getIdentifierName(), $id);
        }
        else {
            $this->getMapper()->update($entity);
        }
    }

    /**
     * @param $entity
     * @return void
     */
    public function deleteRef($entity)
    {
        if(!is_object($entity)) {
            throw new InvalidArgumentException('Entity must be an object value');
        }

        $id = $this->getProperty($entity, $this->getMapper()->getIdentifierName());
        if($id) {
            $this->getMapper()->delete($entity);
        }
    }
}
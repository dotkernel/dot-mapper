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
     * @return int
     */
    public function saveRef($entities, $refValue)
    {
        $affectedRows = 0;
        if(!is_array($entities)) {
            throw new InvalidArgumentException('Entity must be an array of objects');
        }

        foreach ($entities as $entity) {
            if(!is_object($entity)) {
                throw new InvalidArgumentException('Entity collection contains invalid entities');
            }

            $id = $this->getProperty($entity, $this->getMapper()->getIdentifierName());
            if(!$id) {
                $this->setProperty($entity, $this->getRefName(), $refValue);
                $r = $this->getMapper()->create($entity);
                if($r) {
                    $affectedRows += $r;
                }
            }
            else {
                $r = $this->getMapper()->update($entity);
                if($r) {
                    $affectedRows += $r;
                }
            }
        }
        return $affectedRows;
    }

    /**
     * @param $entities
     * @return int
     */
    public function deleteRef($entities)
    {
        $affectedRows = 0;
        if(!is_array($entities)) {
            throw new InvalidArgumentException('Entity must be an array of objects');
        }

        foreach ($entities as $entity) {
            if (!is_object($entity)) {
                throw new InvalidArgumentException('Entity collection contains invalid entities');
            }

            $id = $this->getProperty($entity, $this->getMapper()->getIdentifierName());
            if($id) {
                $r = $this->getMapper()->delete($entity);
                if($r) {
                    $affectedRows += $r;
                }
            }
        }
        return $affectedRows;
    }
}
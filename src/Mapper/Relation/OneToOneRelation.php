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
     * @param $ref
     * @param $refValue
     * @return mixed
     */
    public function saveRef($ref, $refValue)
    {
        if(is_object($ref)) {
            $id = $this->getProperty($ref, $this->getMapper()->getIdentifierName());
            if(!$id) {
                $this->setProperty($ref, $this->getRefName(), $refValue);
                $affectedRows = $this->getMapper()->create($ref);
            }
            else {
                $affectedRows = $this->getMapper()->update($ref);
            }
        }
        else {
            throw new InvalidArgumentException('Entity parameter must be of type object');
        }

        return $affectedRows;
    }

    /**
     * @param $ref
     * @param null $refValue
     * @return int|mixed
     */
    public function deleteRef($ref, $refValue = null)
    {
        $affectedRows = 0;
        if(is_scalar($ref)) {
            //we delete all entities bulk, consider $data as the refValue to delete
            $affectedRows += $this->getMapper()->delete([$this->getRefName() => $ref]);
        }
        elseif(is_object($ref)) {
            $id = $this->getProperty($ref, $this->getMapper()->getIdentifierName());
            if($id) {
                $affectedRows += $this->getMapper()->delete($ref);
            }
        }
        else {
            throw new InvalidArgumentException('Invalid parameter entity to delete.');
        }

        return $affectedRows;
    }
}
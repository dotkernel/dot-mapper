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
use Dot\Ems\Exception\RuntimeException;

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
     * @param $refs
     * @param $refValue
     * @return int|mixed
     */
    public function saveRef($refs, $refValue)
    {
        if(!$this->changeRefs) {
            return 0;
        }

        $affectedRows = 0;
        if(is_array($refs)) {
            $toDelete = [];
            $hasUniqueId = true;

            if($this->getMapper()->getIdentifierName() === $this->getRefName()) {
                //in order to update sub-entities, we need to delete them first, as they don't have unique ID
                $hasUniqueId = false;
                $this->deleteRef($refValue);
            }
            else {
                //store original sub-entities to serve as comparison for what was deleted in the updated entity
                $originalRefs = $this->fetchRef($refValue);
                if($originalRefs && is_array($originalRefs)) {
                    foreach ($originalRefs as $ref) {
                        $id = $this->getProperty($ref, $this->getMapper()->getIdentifierName());
                        $toDelete[$id] = $ref;
                    }
                }
            }

            foreach ($refs as $ref) {
                if(!is_object($ref)) {
                    throw new InvalidArgumentException('Entity collection contains invalid entities');
                }

                $id = $this->getProperty($ref, $this->getMapper()->getIdentifierName());
                if(!$id || !$hasUniqueId) {
                    $this->setProperty($ref, $this->getRefName(), $refValue);
                    $affectedRows += $this->getMapper()->create($ref);
                }
                else {
                    if(isset($toDelete[$id])) {
                        unset($toDelete[$id]);
                    }
                    $affectedRows += $this->getMapper()->update($ref);
                }
            }

            //in case we have something to delete(depending on the sub-entities)
            $affectedRows += $this->deleteRef($toDelete);
        }
        else {
            throw new InvalidArgumentException('Invalid parameter entities to save');
        }

        return $affectedRows;
    }

    /**
     * @param $refs
     * @param null $refValue
     * @return int|mixed
     */
    public function deleteRef($refs, $refValue = null)
    {
        if(!$this->deleteRefs) {
            return 0;
        }

        $affectedRows = 0;
        if(is_scalar($refs)) {
            //we delete all entities bulk, consider $data as the refValue to delete
            $affectedRows = $this->getMapper()->delete([$this->getRefName() => $refs]);
        }
        elseif(is_array($refs)) {
            foreach ($refs as $ref) {
                if (!is_object($ref)) {
                    throw new InvalidArgumentException('References to delete contain invalid entities');
                }

                if($this->getMapper()->getIdentifierName() === $this->getRefName()) {
                    //sub-entities don't have a separate ID, we cannot delete selectively
                    throw new RuntimeException('Cannot delete entities of type '
                        . get_class($ref) . ' selectively. No unique ID defined');
                }

                $id = $this->getProperty($ref, $this->getMapper()->getIdentifierName());
                if($id) {
                    $affectedRows += $this->getMapper()->delete($ref);
                }
            }
        }
        else {
            throw new InvalidArgumentException('Invalid parameter entity to delete.');
        }

        return $affectedRows;
    }
}
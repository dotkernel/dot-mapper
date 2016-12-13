<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 12/12/2016
 * Time: 6:57 PM
 */

namespace Dot\Ems\Mapper\Relation;

use Dot\Ems\Mapper\MapperInterface;


/**
 * Class ManyToManyRelation
 * @package Dot\Ems\Mapper\Relation
 */
class ManyToManyRelation extends OneToManyRelation
{
    protected $refType = RelationInterface::MANY_TO_MANY;

    /** @var  MapperInterface */
    protected $targetMapper;

    /** @var  string */
    protected $targetRefName;

    /**
     * ManyToManyRelation constructor.
     * @param MapperInterface $mapper
     * @param null $refName
     * @param MapperInterface $targetMapper
     * @param $targetRefName
     * @param $fieldName
     */
    public function __construct(
        MapperInterface $mapper, $refName,
        MapperInterface $targetMapper, $targetRefName,
        $fieldName)
    {
        parent::__construct($mapper, $refName, $fieldName);
        $this->targetMapper = $targetMapper;
        $this->targetRefName = $targetRefName;
    }

    /**
     * @param $refValue
     * @return array
     */
    public function fetchRef($refValue)
    {
        $refs = [];
        $linkEntities = parent::fetchRef($refValue);
        if($linkEntities) {
            foreach ($linkEntities as $linkEntity) {
                $targetRefValue = $this->getProperty($linkEntity, $this->targetRefName);
                $ref = $this->targetMapper->fetch([$this->targetMapper->getIdentifierName() => $targetRefValue]);
                if($ref) {
                    $refs[] = $ref;
                }
            }
        }

        return $refs;
    }

    public function saveRef($refs, $refValue)
    {
        if(is_array($refs)) {
            foreach ($refs as $ref) {
                $id = $this->getProperty($ref, $this->targetMapper->getIdentifierName());
                
            }
        }
        else {

        }
    }

    /**
     * It deletes only the intersection table entries, the target entities should be managed in its own mapper-service
     * @param $refs
     * @param null $parentId
     * @return int|mixed
     * @throws \Exception
     */
    public function deleteRef($refs, $parentId = null)
    {
        $affectedRows = 0;
        if(is_scalar($refs)) {
            $affectedRows = $this->getMapper()->delete([$this->getRefName() => $refs]);
        }
        elseif(is_array($refs) && $parentId !== null) {
            foreach ($refs as $ref) {
                $id = $this->getProperty($ref, $this->targetMapper->getIdentifierName());
                if($id) {
                    $affectedRows += $this->getMapper()->delete([$this->getRefName() => $parentId, $this->targetRefName => $id]);
                }
            }
        }
        else {
            throw new \Exception('Invalid parameter refs to delete');
        }

        return $affectedRows;
    }

}
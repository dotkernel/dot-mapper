<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/17/2016
 * Time: 11:01 PM
 */

namespace Dot\Ems\Mapper\Relation;


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
}
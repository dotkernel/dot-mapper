<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/17/2016
 * Time: 10:56 PM
 */

namespace Dot\Ems\Mapper\Relation;


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
}
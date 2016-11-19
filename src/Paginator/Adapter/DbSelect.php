<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/19/2016
 * Time: 2:51 AM
 */

namespace Dot\Ems\Paginator\Adapter;


use Zend\Stdlib\ArrayUtils;

class DbSelect extends \Zend\Paginator\Adapter\DbSelect
{
    public function getItems($offset, $itemCountPerPage)
    {
        $items = parent::getItems($offset, $itemCountPerPage);
        return ArrayUtils::iteratorToArray($items, false);
    }
}
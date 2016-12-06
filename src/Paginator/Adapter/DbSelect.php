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

/**
 * Class DbSelect
 * @package Dot\Ems\Paginator\Adapter
 */
class DbSelect extends \Zend\Paginator\Adapter\DbSelect
{
    /**
     * @param int $offset
     * @param int $itemCountPerPage
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $items = parent::getItems($offset, $itemCountPerPage);
        return ArrayUtils::iteratorToArray($items, false);
    }
}
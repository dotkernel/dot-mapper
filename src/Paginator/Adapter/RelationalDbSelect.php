<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/19/2016
 * Time: 2:30 AM
 */

namespace Dot\Ems\Paginator\Adapter;

use Dot\Ems\Exception\RuntimeException;
use Dot\Ems\Mapper\RelationalDbMapper;

/**
 * Class RelationalDbSelect
 * @package Dot\Ems\Paginator\Adapter
 */
class RelationalDbSelect extends DbSelect
{
    /** @var  RelationalDbMapper */
    protected $relationalDbMapper;

    /**
     * @param int $offset
     * @param int $itemCountPerPage
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        if(!$this->relationalDbMapper instanceof RelationalDbMapper) {
            throw new RuntimeException('No RelationalDbMapper was set in paginator adapter');
        }

        $items = parent::getItems($offset, $itemCountPerPage);
        foreach ($items as $item) {
            $this->relationalDbMapper->buildEntity($item);
        }

        return $items;
    }

    /**
     * @param RelationalDbMapper $relationalDbMapper
     * @return $this
     */
    public function setRelationalMapper(RelationalDbMapper $relationalDbMapper)
    {
        $this->relationalDbMapper = $relationalDbMapper;
        return $this;
    }
}
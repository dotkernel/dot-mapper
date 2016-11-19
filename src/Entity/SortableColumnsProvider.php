<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/19/2016
 * Time: 1:26 AM
 */

namespace Dot\Ems\Entity;


interface SortableColumnsProvider
{
    /**
     * @return string[]
     */
    public function sortableColumns();
}
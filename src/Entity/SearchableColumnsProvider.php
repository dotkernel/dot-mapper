<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/19/2016
 * Time: 1:35 AM
 */

namespace Dot\Ems\Entity;


interface SearchableColumnsProvider
{
    /**
     * @return array
     */
    public function searchableColumns();
}
<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/18/2016
 * Time: 2:03 AM
 */

declare(strict_types=1);

namespace Dot\Ems\Entity;

/**
 * Interface IgnorePropertyProvider
 * @package Dot\Ems\Entity
 */
interface IgnorePropertyProvider
{
    /**
     * @return string[]
     */
    public function ignoredProperties();
}

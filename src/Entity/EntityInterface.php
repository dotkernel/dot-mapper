<?php
/**
 * @copyright: DotKernel
 * @library: dot-ems
 * @author: n3vra
 * Date: 2/8/2017
 * Time: 4:04 PM
 */

declare(strict_types = 1);

namespace Dot\Ems\Entity;

/**
 * Interface EntityInterface
 * @package Dot\Ems\Entity
 */
interface EntityInterface
{
    /**
     * @return string
     */
    public function hydrator(): string;
}

<?php
/**
 * @copyright: DotKernel
 * @library: dot-ems
 * @author: n3vrax
 * Date: 2/8/2017
 * Time: 4:04 PM
 */

declare(strict_types = 1);

namespace Dot\Ems\Entity;

use Dot\Ems\Mapper\MapperInterface;

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

    /**
     * @param array $properties
     */
    public function unsetProperties(array $properties);

    /**
     * @param array $properties
     * @return bool
     */
    public function hasProperties(array $properties): bool;

    /**
     * @param array $properties
     * @return array
     */
    public function extractProperties(array $properties): array;
}

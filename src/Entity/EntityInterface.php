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

/**
 * Interface EntityInterface
 * @package Dot\Ems\Entity
 */
interface EntityInterface
{
    /**
     * @param array $options
     */
    public function save(array $options = []);

    /**
     * @param array $options
     */
    public function delete(array $options = []);

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

<?php
/**
 * @see https://github.com/dotkernel/dot-mapper/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-mapper/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Mapper\Entity;

/**
 * Interface EntityInterface
 * @package Dot\Mapper\Entity
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
     * @param string $property
     */
    public function unsetProperty(string $property);

    /**
     * @param array $properties
     * @return bool
     */
    public function hasProperties(array $properties): bool;

    /**
     * @param string $property
     * @return bool
     */
    public function hasProperty(string $property): bool;

    /**
     * @param array $properties
     * @return array
     */
    public function extractProperties(array $properties): array;

    /**
     * @param string $property
     * @return mixed
     */
    public function extractProperty(string $property);

    /**
     * @return array
     */
    public function getIgnoredProperties(): array;

    /**
     * @param array $ignoredProperties
     */
    public function setIgnoredProperties(array $ignoredProperties);

    /**
     * @param array $ignoredProperties
     */
    public function addIgnoredProperties(array $ignoredProperties);
}

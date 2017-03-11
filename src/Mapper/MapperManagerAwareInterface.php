<?php
/**
 * @see https://github.com/dotkernel/dot-mapper/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-mapper/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Mapper\Mapper;

/**
 * Interface MapperManagerAwareInterface
 * @package Dot\Mapper\Mapper
 */
interface MapperManagerAwareInterface
{
    /**
     * @param MapperManager $mm
     */
    public function setMapperManager(MapperManager $mm);

    /**
     * @return MapperManager|null
     */
    public function getMapperManager(): ?MapperManager;
}

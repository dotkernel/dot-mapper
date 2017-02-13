<?php
/**
 * @copyright: DotKernel
 * @library: dot-ems
 * @author: n3vrax
 * Date: 2/13/2017
 * Time: 5:34 PM
 */

declare(strict_types = 1);

namespace Dot\Ems\Mapper;

/**
 * Interface MapperManagerAwareInterface
 * @package Dot\Ems\Mapper
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

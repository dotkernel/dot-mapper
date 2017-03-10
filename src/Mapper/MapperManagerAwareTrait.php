<?php
/**
 * @copyright: DotKernel
 * @library: dot-ems
 * @author: n3vrax
 * Date: 2/13/2017
 * Time: 5:35 PM
 */

declare(strict_types = 1);

namespace Dot\Mapper\Mapper;

/**
 * Class MapperManagerAwareTrait
 * @package Dot\Mapper\Mapper
 */
trait MapperManagerAwareTrait
{
    /** @var  MapperManager */
    protected $mapperManager;

    /**
     * @param MapperManager $mm
     */
    public function setMapperManager(MapperManager $mm)
    {
        $this->mapperManager = $mm;
    }

    /**
     * @return MapperManager|null
     */
    public function getMapperManager(): ?MapperManager
    {
        return $this->mapperManager;
    }
}

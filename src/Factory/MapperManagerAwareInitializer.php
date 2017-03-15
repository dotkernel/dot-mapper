<?php
/**
 * @see https://github.com/dotkernel/dot-mapper/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-mapper/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Mapper\Factory;

use Dot\Mapper\Mapper\MapperManager;
use Dot\Mapper\Mapper\MapperManagerAwareInterface;
use Psr\Container\ContainerInterface;

/**
 * Class MapperManagerAwareInitializer
 * @package Dot\Mapper\Factory
 */
class MapperManagerAwareInitializer
{
    public function __invoke(ContainerInterface $container, $instance)
    {
        if ($instance instanceof MapperManagerAwareInterface) {
            $mm = $container->get(MapperManager::class);
            $instance->setMapperManager($mm);
        }
    }
}

<?php
/**
 * @see https://github.com/dotkernel/dot-mapper/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-mapper/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Mapper\Factory;

use Dot\Mapper\Mapper\MapperManager;
use Psr\Container\ContainerInterface;

/**
 * Class MapperManagerFactory
 * @package Dot\Mapper\Factory
 */
class MapperManagerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new MapperManager($container, $container->get('config')['dot_mapper']);
    }
}

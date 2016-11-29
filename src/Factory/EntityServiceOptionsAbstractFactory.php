<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/29/2016
 * Time: 9:11 PM
 */

namespace Dot\Ems\Factory;

use Dot\Ems\Options\ServiceOptions;
use Interop\Container\ContainerInterface;

/**
 * Class EntityServiceOptionsAbstractFactory
 * @package Dot\Ems\Factory
 */
class EntityServiceOptionsAbstractFactory extends AbstractServiceFactory
{
    const SPECIFIC_PART = 'options';

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $specificServiceName = explode('.', $requestedName)[2];

        $config = $this->getConfig($container);
        $specificConfig = $config[$specificServiceName];
        if (!is_array($specificConfig)) {
            $specificConfig = [];
        }

        return new ServiceOptions($specificConfig);
    }
}
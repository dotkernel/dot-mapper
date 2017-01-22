<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/29/2016
 * Time: 8:09 PM
 */

namespace Dot\Ems\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

/**
 * Class AbstractServiceFactory
 * @package Dot\Ems\Factory
 */
abstract class AbstractServiceFactory implements AbstractFactoryInterface
{
    const DOT_EMS_PART = 'dot-ems';
    const SPECIFIC_PART = '';

    /** @var string */
    protected $configKey = 'dot_ems';

    /** @var string  */
    protected $subConfigKey = 'services';

    /** @var array */
    protected $config = [];

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $parts = explode('.', $requestedName);
        if (count($parts) !== 3) {
            return false;
        }

        if ($parts[0] !== self::DOT_EMS_PART || $parts[1] !== static::SPECIFIC_PART) {
            return false;
        }

        $specificServiceName = $parts[2];
        $config = $this->getConfig($container);
        return array_key_exists($specificServiceName, $config);
    }

    /**
     * @param ContainerInterface $container
     * @return array
     */
    protected function getConfig(ContainerInterface $container)
    {
        $config = $container->get('config');
        if (isset($config[$this->configKey][$this->subConfigKey])
            && is_array($config[$this->configKey][$this->subConfigKey])) {
            $this->config = $config[$this->configKey][$this->subConfigKey];
        }

        return $this->config;
    }
}

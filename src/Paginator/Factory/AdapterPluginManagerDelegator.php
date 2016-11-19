<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/19/2016
 * Time: 3:10 AM
 */

namespace Dot\Ems\Paginator\Factory;


use Dot\Ems\Paginator\Adapter\DbSelect;
use Dot\Ems\Paginator\Adapter\RelationalDbSelect;
use Interop\Container\ContainerInterface;
use Zend\Paginator\AdapterPluginManager;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Factory\DelegatorFactoryInterface;

class AdapterPluginManagerDelegator implements DelegatorFactoryInterface
{
    public function __invoke(ContainerInterface $container, $name, callable $callback, array $options = null)
    {
        /** @var AdapterPluginManager $pluginManager */
        $pluginManager = $callback();

        $pluginManager->setFactory(DbSelect::class, [$this, 'dbSelectFactory']);
        $pluginManager->setFactory(RelationalDbSelect::class, [$this, 'dbSelectFactory']);

        return $pluginManager;
    }

    protected function dbSelectFactory(ContainerInterface $container, $requestedName, array $options = null)
    {
        if (null === $options || empty($options)) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires a minimum of zend-db Sql\Select and Adapter instance',
                DbSelect::class
            ));
        }
        return new $requestedName(
            $options[0],
            $options[1],
            isset($options[2]) ? $options[2] : null
        );
    }

}
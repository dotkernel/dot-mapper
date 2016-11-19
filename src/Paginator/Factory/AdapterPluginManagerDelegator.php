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
use Zend\Paginator\Adapter\Service\DbSelectFactory;
use Zend\Paginator\AdapterPluginManager;
use Zend\ServiceManager\Factory\DelegatorFactoryInterface;

class AdapterPluginManagerDelegator implements DelegatorFactoryInterface
{
    public function __invoke(ContainerInterface $container, $name, callable $callback, array $options = null)
    {
        /** @var AdapterPluginManager $pluginManager */
        $pluginManager = $callback();

        $pluginManager->setFactory(DbSelect::class, DbSelectFactory::class);
        $pluginManager->setFactory(RelationalDbSelect::class, DbSelectFactory::class);

        return $pluginManager;
    }
}
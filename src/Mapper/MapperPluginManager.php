<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/29/2016
 * Time: 7:23 PM
 */

declare(strict_types=1);

namespace Dot\Ems\Mapper;

use Dot\Ems\Factory\DbMapperFactory;
use Dot\Ems\Factory\RelationalDbMapperFactory;
use Zend\ServiceManager\AbstractPluginManager;

/**
 * Class MapperPluginManager
 * @package Dot\Ems\Mapper
 */
class MapperPluginManager extends AbstractPluginManager
{
    protected $instanceOf = MapperInterface::class;

    //default mappers
    protected $factories = [
        DbMapper::class => DbMapperFactory::class,
        RelationalDbMapper::class => RelationalDbMapperFactory::class,
    ];

    protected $aliases = [
        'DbMapper' => DbMapper::class,
        'RelationalDbMapper' => RelationalDbMapper::class,
    ];
}

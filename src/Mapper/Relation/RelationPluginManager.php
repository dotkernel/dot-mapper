<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/29/2016
 * Time: 7:53 PM
 */

namespace Dot\Ems\Mapper\Relation;

use Dot\Ems\Factory\ManyToManyRelationFactory;
use Dot\Ems\Factory\RelationFactory;
use Zend\ServiceManager\AbstractPluginManager;

/**
 * Class RelationPluginManager
 * @package Dot\Ems\Mapper\Relation
 */
class RelationPluginManager extends AbstractPluginManager
{
    protected $instanceOf = RelationInterface::class;

    protected $factories = [
        OneToOneRelation::class => RelationFactory::class,
        OneToManyRelation::class => RelationFactory::class,
        ManyToManyRelation::class => ManyToManyRelationFactory::class,
    ];
}

<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/29/2016
 * Time: 7:53 PM
 */

namespace Dot\Ems\Mapper\Relation;


use Dot\Ems\Factory\OneToManyRelationFactory;
use Dot\Ems\Factory\OneToOneRelationFactory;
use Zend\ServiceManager\AbstractPluginManager;

class RelationPluginManager extends AbstractPluginManager
{
    protected $instanceOf = RelationInterface::class;

    protected $factories = [
        OneToOneRelation::class => OneToOneRelationFactory::class,
        OneToManyRelation::class => OneToManyRelationFactory::class
    ];
}
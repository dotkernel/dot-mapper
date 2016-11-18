<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/17/2016
 * Time: 10:48 PM
 */

namespace Dot\Ems\Mapper;

use Dot\Ems\Mapper\Relation\RelationInterface;
use Dot\Ems\ObjectPropertyTrait;

/**
 * Class AggregateDbMapper
 * @package Dot\Ems\Mapper
 */
class RelationalDbMapper extends AbstractDbMapper
{
    use ObjectPropertyTrait;

    /** @var RelationInterface[] */
    protected $relations = [];

    /** @var bool  */
    protected $deleteCascade = false;

    /**
     * @param $where
     * @return null|object
     */
    public function fetch($where)
    {
        $entity = parent::fetch($where);
        if($entity) {
            $this->buildEntity($entity);
        }

        return $entity;
    }

    /**
     * @param array $where
     * @param array $filters
     * @param bool $paginated
     * @return array|null|\Zend\Paginator\Paginator
     */
    public function fetchAll($where = [], $filters = [], $paginated = false)
    {
        $entities = parent::fetchAll($where, $filters, $paginated);
        if($entities) {
            foreach ($entities as $entity) {
                $this->buildEntity($entity);
            }
        }

        return $entities;
    }

    /**
     * @param $entity
     * @return int
     */
    public function create($entity)
    {
        $this->ignoredProperties = array_keys($this->relations);
        $id = parent::create($entity);

        $this->saveSubEntities($entity, $id);
        return $id;
    }

    /**
     * @param $entity
     * @return void
     */
    public function update($entity)
    {
        $this->ignoredProperties = array_keys($this->relations);
        parent::update($entity);

        $this->saveSubEntities($entity, $this->getProperty($entity, $this->getIdentifierName()));
    }

    /**
     * @param $entity
     * @return void
     */
    public function delete($entity)
    {
        parent::delete($entity);
        if($this->deleteCascade) {
            $this->deleteSubEntities($entity);
        }
    }

    /**
     * @param $entity
     * @param $id
     */
    protected function saveSubEntities($entity, $id)
    {
        foreach (array_keys($this->relations) as $property) {
            $relation = $this->relations[$property];

            $subEntity = $this->getProperty($entity, $property);
            if(!empty($subEntity)) {
                $relation->saveRef($subEntity, $id);
            }
        }
    }

    /**
     * @param $entity
     */
    protected function deleteSubEntities($entity)
    {
        foreach (array_keys($this->relations) as $property) {
            $relation = $this->relations[$property];

            $subEntity = $this->getProperty($entity, $property);
            if(!empty($subEntity)) {
                $relation->deleteRef($subEntity);
            }
        }
    }

    /**
     * @param $entity
     */
    protected function buildEntity($entity)
    {
        if(empty($this->relations)) {
            return;
        }

        $id = $this->getProperty($entity, $this->getIdentifierName());
        foreach ($this->relations as $property => $relation) {
            $subEntity = $relation->fetchRef($id);
            $this->setProperty($entity, $property, $subEntity);
        }
    }

    /**
     * @return Relation\RelationInterface[]
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * @param Relation\RelationInterface[] $relations
     * @return RelationalDbMapper
     */
    public function setRelations($relations)
    {
        $this->relations = $relations;
        return $this;
    }

    /**
     * @param $property
     * @param RelationInterface $relation
     * @return $this
     */
    public function addRelation($property, RelationInterface $relation)
    {
        $this->relations[$property] = $relation;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDeleteCascade()
    {
        return $this->deleteCascade;
    }

    /**
     * @param boolean $deleteCascade
     * @return RelationalDbMapper
     */
    public function setDeleteCascade($deleteCascade)
    {
        $this->deleteCascade = $deleteCascade;
        return $this;
    }

}
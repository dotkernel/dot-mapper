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
use Dot\Ems\Paginator\Adapter\RelationalDbSelect;

/**
 * Class AggregateDbMapper
 * @package Dot\Ems\Mapper
 */
class RelationalDbMapper extends AbstractDbMapper implements RelationalMapperInterface
{
    use ObjectPropertyTrait;

    /** @var  string */
    protected $paginatorAdapterName = RelationalDbSelect::class;

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
        if($entities && !$paginated) {
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
        $affectedRows = parent::create($entity);
        if($affectedRows) {
            $affectedRows += $this->saveSubEntities($entity, $this->lastInsertValue());
        }

        return $affectedRows;
    }

    /**
     * @param $entity
     * @return int
     */
    public function update($entity)
    {
        $affectedRows = parent::update($entity);
        $affectedRows += $this->saveSubEntities($entity, $this->getProperty($entity, $this->getIdentifierName()));
        return $affectedRows;
    }

    /**
     * @param $where
     * @return int
     */
    public function delete($where)
    {
        $affectedRows = parent::delete($where);
        if(is_object($where) && is_a($where, get_class($this->getPrototype()))) {
            if($this->deleteCascade && $affectedRows) {
                $affectedRows += $this->deleteSubEntities($where, true);
            }
        }

        return $affectedRows;
    }

    /**
     * @param $entity
     * @param $id
     * @return int
     */
    public function saveSubEntities($entity, $id)
    {
        $affectedRows = 0;
        foreach (array_keys($this->relations) as $property) {
            $relation = $this->relations[$property];

            $subEntity = $this->getProperty($entity, $property);
            if(!empty($subEntity)) {
                $affectedRows += $relation->saveRef($subEntity, $id);
            }
        }
        return $affectedRows;
    }

    /**
     * @param $entity
     * @param bool $bulk
     * @return int|mixed
     */
    public function deleteSubEntities($entity, $bulk = true)
    {
        $affectedRows = 0;
        $id = $this->getProperty($entity, $this->getIdentifierName());

        foreach (array_keys($this->relations) as $property) {
            $relation = $this->relations[$property];

            if($bulk) {
                $affectedRows += $relation->deleteRef($id);
            }
            else {
                $subEntity = $this->getProperty($entity, $property);
                if(!empty($subEntity)) {
                    $affectedRows += $relation->deleteRef($subEntity, $id);
                }
            }
        }
        return $affectedRows;
    }

    /**
     * @param $entity
     */
    public function buildEntity($entity)
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
     * @param RelationInterface $relation
     * @return $this
     */
    public function addRelation(RelationInterface $relation)
    {
        $this->relations[$relation->getFieldName()] = $relation;
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

    public function getPaginatorAdapterName()
    {
        if(!$this->paginatorAdapterName) {
            $this->paginatorAdapterName = RelationalDbSelect::class;
        }
        return $this->paginatorAdapterName;
    }

    protected function getPaginatorAdapter()
    {
        /** @var RelationalDbSelect $paginatorAdapter */
        $paginatorAdapter = parent::getPaginatorAdapter();
        $paginatorAdapter->setRelationalMapper($this);
        return $paginatorAdapter;
    }

    /**
     * @param $entity
     * @param bool $removeNulls
     * @return array
     */
    protected function entityToArray($entity, $removeNulls = true)
    {
        $data = parent::entityToArray($entity, $removeNulls);

        $ignoreProperties = array_keys($this->relations);
        foreach ($ignoreProperties as $prop) {
            if(isset($data[$prop])) {
                unset($data[$prop]);
            }
        }

        return $data;
    }

}
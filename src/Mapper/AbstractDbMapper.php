<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/17/2016
 * Time: 7:56 PM
 */

namespace Dot\Ems\Mapper;

use Dot\Ems\Entity\IgnorePropertyProvider;
use Dot\Ems\Exception\InvalidArgumentException;
use Dot\Ems\ObjectPropertyTrait;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Metadata\MetadataInterface;
use Zend\Db\Metadata\Source\Factory;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\Feature\FeatureSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Hydrator\ClassMethods;
use Zend\Hydrator\HydratorInterface;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\AdapterPluginManager;
use Zend\Paginator\Paginator;

/**
 * Class AbstractDbMapper
 * @package Dot\Ems\Mapper
 */
abstract class AbstractDbMapper implements MapperInterface
{
    use ObjectPropertyTrait;

    /** @var  Adapter */
    protected $adapter;

    /** @var  TableGateway */
    protected $tableGateway;

    /** @var  object */
    protected $prototype;

    /** @var  HydratorInterface */
    protected $hydrator;

    /** @var  string */
    protected $identifier = 'id';

    /** @var  string */
    protected $paginatorAdapterName;

    /** @var  AdapterPluginManager */
    protected $paginatorAdapterManager;

    /** @var  MetadataInterface */
    protected $metadata;

    /** @var  string[] */
    protected $ignoredProperties = [];

    /**
     * AbstractDbMapper constructor.
     * @param $table
     * @param Adapter $adapter
     * @param $prototype
     * @param HydratorInterface|null $hydrator
     * @param FeatureSet|null $features
     */
    public function __construct(
        $table,
        Adapter $adapter,
        $prototype,
        HydratorInterface $hydrator = null,
        FeatureSet $features = null)
    {
        $this->adapter = $adapter;
        $this->prototype = $prototype;
        $this->hydrator = $hydrator;

        $this->metadata = Factory::createSourceFromAdapter($adapter);

        if (!is_object($this->prototype)) {
            throw new InvalidArgumentException('Entity prototype must be an object');
        }

        if (!$this->hydrator instanceof HydratorInterface) {
            $this->hydrator = new ClassMethods(false);
        }

        $resultSetPrototype = new HydratingResultSet($this->hydrator, $this->prototype);
        $this->tableGateway = new TableGateway($table, $adapter, $features, $resultSetPrototype);
    }

    /**
     * @return int
     */
    public function lastInsertValue()
    {
        return $this->tableGateway->getLastInsertValue();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction()
    {
        $connection = $this->tableGateway->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit()
    {
        $connection = $this->tableGateway->getAdapter()->getDriver()->getConnection();
        $connection->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback()
    {
        $connection = $this->tableGateway->getAdapter()->getDriver()->getConnection();
        $connection->rollback();
    }

    /**
     * @param $where
     * @return null|object
     */
    public function fetch($where)
    {
        $entity = null;
        /** @var HydratingResultSet $resultSet */
        $resultSet = $this->tableGateway->select($where);
        if ($resultSet && $resultSet->valid()) {
            $entity = $resultSet->current();
        }

        return $entity;
    }

    /**
     * @param array $where
     * @param array $filters
     * @param bool $paginated
     * @return array|null|Paginator
     */
    public function fetchAll($where = [], $filters = [], $paginated = false)
    {
        $entities = null;

        $select = $this->tableGateway->getSql()->select();
        if (!empty($where)) {
            $select->where($where);
        }

        $select = $this->applyFilters($select, $filters);

        if (!$paginated) {
            /** @var HydratingResultSet $resultSet */
            $resultSet = $this->tableGateway->selectWith($select);
            if ($resultSet && $resultSet->valid()) {
                $entities = $resultSet->toArray();
            }

            return $entities;
        } else {
            $resultSetPrototype = $this->tableGateway->getResultSetPrototype();
            $paginatorAdapter = ($this->paginatorAdapterManager instanceof AdapterPluginManager)
                ? $this->paginatorAdapterManager->get($this->getPaginatorAdapterName(),
                    [$select, $this->adapter, $resultSetPrototype])
                : new DbSelect($select, $this->adapter, $resultSetPrototype);

            return new Paginator($paginatorAdapter);
        }
    }

    /**
     * @param $entity
     * @return int
     */
    public function create($entity)
    {
        $data = $this->entityToArray($entity, false);
        $this->tableGateway->insert($data);
        $this->setProperty($entity, $this->getIdentifierName(), $this->lastInsertValue());
        return $this->lastInsertValue();
    }

    /**
     * @param $entity
     * @return void
     */
    public function update($entity)
    {
        $data = $this->entityToArray($entity);

        if(!isset($data[$this->identifier])) {
            throw new InvalidArgumentException('Cannot update entity without and identifier');
        }

        $id = $data[$this->identifier];
        unset($data[$this->identifier]);

        $this->tableGateway->update($data, [$this->identifier => $id]);
    }

    /**
     * @param $entity
     * @return void
     */
    public function delete($entity)
    {
        if(!is_object($entity)) {
            throw new InvalidArgumentException('Entity must be an object');
        }

        $id = $this->getProperty($entity, $this->getIdentifierName());
        if(!$id) {
            throw new InvalidArgumentException('Cannot delete an entity without an identifier');
        }
        $this->tableGateway->delete([$this->getIdentifierName() => $id]);
    }

    /**
     * @return object
     */
    public function getPrototype()
    {
        return $this->prototype;
    }

    /**
     * @return ClassMethods|HydratorInterface
     */
    public function getHydrator()
    {
        if (!$this->hydrator instanceof HydratorInterface) {
            $this->hydrator = new ClassMethods(false);
        }
        return $this->hydrator;
    }

    /**
     * @return string
     */
    public function getIdentifierName()
    {
        return $this->identifier;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setIdentifierName($name)
    {
        $this->identifier = $name;
        return $this;
    }

    /**
     * @return TableGateway
     */
    public function getTableGateway()
    {
        return $this->tableGateway;
    }

    /**
     * @param TableGateway $tableGateway
     * @return AbstractDbMapper
     */
    public function setTableGateway($tableGateway)
    {
        $this->tableGateway = $tableGateway;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaginatorAdapterName()
    {
        if(!$this->paginatorAdapterName) {
            $this->paginatorAdapterName = DbSelect::class;
        }
        return $this->paginatorAdapterName;
    }

    /**
     * @param string $paginatorAdapterName
     * @return AbstractDbMapper
     */
    public function setPaginatorAdapterName($paginatorAdapterName)
    {
        $this->paginatorAdapterName = $paginatorAdapterName;
        return $this;
    }

    /**
     * @return AdapterPluginManager
     */
    public function getPaginatorAdapterManager()
    {
        return $this->paginatorAdapterManager;
    }

    /**
     * @param AdapterPluginManager $paginatorAdapterManager
     * @return AbstractDbMapper
     */
    public function setPaginatorAdapterManager(AdapterPluginManager $paginatorAdapterManager)
    {
        $this->paginatorAdapterManager = $paginatorAdapterManager;
        return $this;
    }

    /**
     * @param $entity
     * @param bool $removeNulls
     * @param array $ignoreProperties
     * @return array
     */
    protected function entityToArray($entity, $removeNulls = true, $ignoreProperties = [])
    {
        if(!is_object($entity)) {
            throw new InvalidArgumentException('Entity must be and object');
        }

        $data = $this->hydrator->extract($entity);
        if($entity instanceof IgnorePropertyProvider) {
            $ignoreProperties = $entity->ignoredProperties();
        }

        if($removeNulls) {
            $data = array_filter($data);
        }

        $ignoreProperties = array_merge($ignoreProperties, $this->ignoredProperties);
        foreach ($ignoreProperties as $prop) {
            if(isset($data[$prop])) {
                unset($data[$prop]);
            }
        }

        return $data;
    }

    /**
     * @param Select $select
     * @param array $filters
     * @return Select
     */
    protected function applyFilters(Select $select, $filters = [])
    {
        if(empty($filters)) {
            return $select;
        }

        //sorting options
        $sort = isset($filters['sort']) ? $filters['sort'] : '';
        $order = isset($filters['order']) ? strtoupper($filters['order']) : 'ASC';

        //make sure order param is just the allowed ones
        if(!in_array($order, ['ASC', 'DESC'])) {
            $order = 'ASC';
        }

        if(!empty($sort) && in_array($sort, $this->metadata->getColumnNames($this->tableGateway->getTable()))) {
            $column = $this->metadata->getColumn($sort, $this->tableGateway->getTable());
            if($column->getDataType() == 'ENUM' || $column->getDataType() == 'SET') {
                $select->order(new Expression('CAST(' . $this->adapter->getPlatform()->quoteIdentifier($sort)
                    . ' as CHAR) ' . $order));
            }
            else {
                $select->order([$sort => $order]);
            }
        }

        return $select;

    }

}
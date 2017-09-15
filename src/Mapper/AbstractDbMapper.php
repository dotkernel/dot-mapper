<?php
/**
 * @see https://github.com/dotkernel/dot-mapper/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-mapper/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Mapper\Mapper;

use Dot\Hydrator\ClassMethodsCamelCase;
use Dot\Mapper\Entity\EntityInterface;
use Dot\Mapper\Event\DispatchMapperEventsTrait;
use Dot\Mapper\Event\MapperEvent;
use Dot\Mapper\Event\MapperEventListenerInterface;
use Dot\Mapper\Event\MapperEventListenerTrait;
use Dot\Mapper\Exception\BadMethodCallException;
use Dot\Mapper\Exception\InvalidArgumentException;
use Dot\Mapper\Exception\RolledbackTransactionException;
use Dot\Mapper\Exception\RuntimeException;
use Dot\Mapper\Utility;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\AbstractConnection;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Metadata\MetadataInterface;
use Zend\Db\Metadata\Object\ColumnObject;
use Zend\Db\Metadata\Object\ConstraintObject;
use Zend\Db\Metadata\Object\TableObject;
use Zend\Db\Metadata\Source\Factory;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\ResponseCollection;
use Zend\Hydrator\HydratorInterface;
use Zend\Hydrator\HydratorPluginManager;
use Zend\ServiceManager\ServiceManager;

/**
 * Class AbstractDbMapper
 * @package Dot\Mapper\Mapper
 */
abstract class AbstractDbMapper implements MapperInterface, MapperEventListenerInterface, EventManagerAwareInterface
{
    use DispatchMapperEventsTrait;
    use MapperEventListenerTrait;

    /** @var array */
    protected $identityMap = [];

    /** @var  MapperManager */
    protected $mapperManager;

    /** @var  HydratorPluginManager */
    protected $hydratorPluginManager;

    /** @var  MetadataInterface */
    protected $metadata;

    /** @var  Adapter */
    protected $adapter;

    /** @var  AbstractConnection */
    protected $connection;

    /** @var  Adapter */
    protected $slaveAdapter;

    /** @var  Sql */
    protected $sql;

    /** @var  Sql */
    protected $slaveSql;

    /** @var  string */
    protected $table;

    /** @var  TableObject */
    protected $schema;

    /** @var  array */
    protected $primaryKey;

    /** @var  string[] */
    protected $columns;

    /** @var  string */
    protected $alias;

    /** @var  HydratorInterface */
    protected $hydrator;

    /** @var  EntityInterface */
    protected $prototype;

    /**
     * AbstractDbMapper constructor.
     * @param MapperManager $mapperManager
     * @param array $options
     */
    public function __construct(MapperManager $mapperManager, array $options = [])
    {
        $this->mapperManager = $mapperManager;

        if (isset($options['adapter']) && $options['adapter'] instanceof Adapter) {
            $this->setAdapter($options['adapter']);
        }

        if (isset($options['slave_adapter']) && $options['slave_adapter'] instanceof Adapter) {
            $this->setSlaveAdapter($options['slave_adapter']);
        }

        if (isset($options['table']) && is_string($options['table'])) {
            $this->setTable($options['table']);
        }

        if (isset($options['alias']) && is_string($options['alias'])) {
            $this->setAlias($options['alias']);
        }

        if (isset($options['prototype']) && $options['prototype'] instanceof EntityInterface) {
            $this->setPrototype($options['prototype']);
        }

        if (isset($options['hydrator_manager']) && $options['hydrator_manager'] instanceof HydratorPluginManager) {
            $this->setHydratorPluginManager($options['hydrator_manager']);
        }

        if (isset($options['metadata']) && $options['metadata'] instanceof MetadataInterface) {
            $this->setMetadata($options['metadata']);
        }

        if (isset($options['schema']) && $options['schema'] instanceof TableObject) {
            $this->setSchema($options['schema']);
        }

        if (!$this->adapter instanceof Adapter) {
            throw new RuntimeException('Db adapter is required and was not set');
        }

        if (!$this->prototype instanceof EntityInterface) {
            throw new RuntimeException('Entity prototype is required and was not set');
        }

        // the mapper is a listener for itself, for callbacks
        $this->attach($this->getEventManager(), 1000);
    }

    /**
     * @param string $type
     * @param array $options
     * @return array
     */
    public function find(string $type = 'all', array $options = []): array
    {
        $select = $this->getSlaveSql()->select()->from([$this->getAlias() => $this->getTable()]);
        $select = $this->callFinder($type, $select, $options);

        $event = $this->dispatchEvent(
            MapperEvent::EVENT_MAPPER_BEFORE_FIND,
            ['select' => $select, 'type' => $type, 'options' => $options]
        );
        if ($event->stopped()) {
            return $event->last();
        }

        $stmt = $this->getSlaveSql()->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet(ResultSet::TYPE_ARRAY);
            $resultSet->initialize($result);
            $entities = $this->loadAll($resultSet, $options);

            $this->dispatchEvent(
                MapperEvent::EVENT_MAPPER_AFTER_FIND,
                ['entities' => $entities, 'type' => $type, 'options' => $options]
            );

            return $entities;
        }

        return [];
    }

    /**
     * @param string $type
     * @param array $options
     * @return int
     */
    public function count(string $type = 'all', array $options = []): int
    {
        $select = $this->getSlaveSql()->select()->from([$this->getAlias() => $this->getTable()]);
        $select = $this->callFinder($type, $select, $options);

        $select->reset(Select::LIMIT);
        $select->reset(Select::OFFSET);
        $select->reset(Select::ORDER);

        $select->columns(['count' => new Expression('COUNT(1)')]);

        $stmt = $this->getSlaveSql()->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if ($result->valid()) {
            return (int)$result->current()['count'];
        }

        return -1;
    }

    /**
     * @param $primaryKey
     * @param array $options
     * @return mixed
     */
    public function get($primaryKey, array $options = [])
    {
        $primaryKey = (array)$primaryKey;

        /*$mapKey = implode(',', $primaryKey);
        if (isset($this->identityMap[$mapKey])) {
            return $this->identityMap[$mapKey];
        }*/

        $keys = (array)$this->getPrimaryKey();
        foreach ($keys as $index => $keyname) {
            $keys[$index] = $this->aliasField($keyname);
        }

        if (count($primaryKey) !== count($keys)) {
            throw new InvalidArgumentException(
                'Invalid primary key given. Provide all primary keys that identifies the object'
            );
        }

        $options['conditions'] = $options['conditions'] ?? [];
        $options['conditions'] += array_combine($keys, $primaryKey);

        $finder = (string)($options['finder'] ?? 'all');
        $result = $this->find($finder, $options);
        if (!empty($result) && isset($result[0])) {
            return $result[0];
        }

        return null;
    }

    /**
     * @param EntityInterface $entity
     * @param array $options
     * @return bool|EntityInterface
     * @throws \Exception
     */
    public function save(EntityInterface $entity, array $options = [])
    {
        $options += [
            'atomic' => true,
            'force_nulls' => false,
        ];

        if ($options['atomic']) {
            try {
                $this->getConnection()->beginTransaction();
                $success = $this->processSave($entity, $options);
                $this->getConnection()->commit();
            } catch (\Exception $e) {
                $this->getConnection()->rollback();
                throw $e;
            }
        } else {
            $success = $this->processSave($entity, $options);
        }

        if ($success) {
            if ($options['atomic']) {
                $this->dispatchEvent(
                    MapperEvent::EVENT_MAPPER_AFTER_SAVE_COMMIT,
                    ['entity' => $entity, 'options' => $options]
                );
            }
        }

        return $success;
    }

    /**
     * @param EntityInterface $entity
     * @param array $options
     * @return bool|EntityInterface
     */
    protected function processSave(EntityInterface $entity, array $options)
    {
        $primaryColumns = $this->getPrimaryKey();
        $tableColumns = $this->getColumns();

        $data = $this->getHydrator()->extract($entity);
        $data = array_intersect_key($data, array_flip($tableColumns));

        $primaryKey = array_intersect_key($data, array_flip($primaryColumns));
        $primaryKey = array_filter($primaryKey);

        $isNew = empty($primaryKey);

        /** @var ResponseCollection $event */
        $event = $this->dispatchEvent(
            MapperEvent::EVENT_MAPPER_BEFORE_SAVE,
            ['entity' => $entity, 'options' => $options, 'isNew' => $isNew]
        );

        if ($event->stopped()) {
            return $event->last();
        }

        if (isset($options['force_nulls']) && $options['force_nulls'] === false) {
            $data = array_filter($data);
        }

        if ($isNew) {
            $success = $this->insert($entity, $data);
        } else {
            $success = $this->update($entity, $data);
        }

        if ($success) {
            $success = $this->onSaveSuccess($entity, $isNew, $options);
        }

        if (!$success & $isNew) {
            $entity->unsetProperties($primaryColumns);
        }

        if ($success && $isNew) {
            $primaryKey = $entity->extractProperties($primaryColumns);
            $mapKey = implode(',', $primaryKey);
            $this->identityMap[$mapKey] = $entity;
        }

        return $success ? $entity : false;
    }

    /**
     * @param EntityInterface $entity
     * @param bool $isNew
     * @param array $options
     * @return bool
     * @throws RolledbackTransactionException
     */
    protected function onSaveSuccess(EntityInterface $entity, bool $isNew, array $options)
    {
        //TODO: implement save associations in the future

        $this->dispatchEvent(
            MapperEvent::EVENT_MAPPER_AFTER_SAVE,
            ['entity' => $entity, 'options' => $options, 'isNew' => $isNew]
        );

        if ($options['atomic'] && !$this->getConnection()->inTransaction()) {
            throw new RolledbackTransactionException(
                sprintf(
                    'The afterSave event in `%s` is aborting the transaction before the save process is done',
                    get_class($this)
                )
            );
        }

        return true;
    }

    /**
     * @param EntityInterface $entity
     * @param array $data
     * @return bool|EntityInterface
     */
    protected function insert(EntityInterface $entity, array $data)
    {
        $primary = $this->getPrimaryKey();
        if (empty($primary)) {
            throw new RuntimeException('Cannot insert entity into table. It does not have a primary key');
        }

        $data = array_diff_key($data, array_flip($primary));
        $success = false;
        if (empty($data)) {
            return $success;
        }

        $insert = $this->getSql()->insert()->into($this->getTable())
            ->columns(array_keys($data))
            ->values($data);

        $stmt = $this->getSql()->prepareStatementForSqlObject($insert);
        $result = $stmt->execute();

        if ($result->getAffectedRows() !== 0) {
            //populate entity with generated values
            $ids = [];
            foreach ($primary as $column) {
                if (!isset($data[$column])) {
                    $ids[] = $this->getConnection()->getLastGeneratedValue($column);
                }
            }

            $ids = array_combine($primary, $ids);
            /** @var EntityInterface $entity */
            $entity = $this->getHydrator()->hydrate($ids, $entity);
            $success = $entity;
        }

        return $success;
    }

    /**
     * @param EntityInterface $entity
     * @param array $data
     * @return bool|EntityInterface
     */
    protected function update(EntityInterface $entity, array $data)
    {
        $primaryColumns = $this->getPrimaryKey();
        $primaryKey = array_intersect_key($data, array_flip($primaryColumns));

        $data = array_diff_key($data, $primaryKey);
        if (empty($data)) {
            return $entity;
        }

        $filteredKeys = array_filter($primaryKey);
        if (count($filteredKeys) !== count($primaryKey)) {
            throw new RuntimeException('Entity cannot be updated. All primary keys must be given');
        }

        $update = $this->getSql()->update()->table($this->getTable())
            ->set($data)
            ->where($primaryKey);

        $stmt = $this->getSql()->prepareStatementForSqlObject($update);
        $result = $stmt->execute();

        $success = false;
        if ($result->valid()) {
            $success = $entity;
        }

        return $success;
    }

    /**
     * @param EntityInterface $entity
     * @param array $options
     * @return bool
     * @throws \Exception
     */
    public function delete(EntityInterface $entity, array $options = [])
    {
        $options += [
            'atomic' => true,
        ];

        if ($options['atomic']) {
            try {
                $this->getConnection()->beginTransaction();
                $success = $this->processDelete($entity, $options);
                $this->getConnection()->commit();
            } catch (\Exception $e) {
                $this->getConnection()->rollback();
                throw $e;
            }
        } else {
            $success = $this->processDelete($entity, $options);
        }

        if ($success) {
            if ($options['atomic']) {
                $this->dispatchEvent(
                    MapperEvent::EVENT_MAPPER_AFTER_DELETE_COMMIT,
                    ['entity' => $entity, 'options' => $options]
                );
            }
        }

        return $success;
    }

    /**
     * @param EntityInterface $entity
     * @param array $options
     * @return bool
     */
    protected function processDelete(EntityInterface $entity, array $options)
    {
        $primaryColumns = $this->getPrimaryKey();

        $data = $this->getHydrator()->extract($entity);

        $primaryKey = array_intersect_key($data, array_flip($primaryColumns));
        $primaryKey = array_filter($primaryKey);
        $mapKey = implode(',', $primaryKey);

        if (count($primaryKey) !== count($primaryColumns)) {
            throw new RuntimeException('Could not delete and entity without all primary keys specified');
        }

        /** @var ResponseCollection $event */
        $event = $this->dispatchEvent(
            MapperEvent::EVENT_MAPPER_BEFORE_DELETE,
            ['entity' => $entity, 'options' => $options]
        );

        if ($event->stopped()) {
            return $event->last();
        }

        $delete = $this->getSql()->delete($this->getTable())
            ->where($primaryKey);

        $stmt = $this->getSql()->prepareStatementForSqlObject($delete);
        $result = $stmt->execute();

        $success = $result->getAffectedRows() > 0;
        if (!$success) {
            return $success;
        } else {
            //remove from identity map
            unset($this->identityMap[$mapKey]);
        }

        $this->dispatchEvent(MapperEvent::EVENT_MAPPER_AFTER_DELETE, ['entity' => $entity, 'options' => $options]);

        return $success;
    }

    /**
     * @param array $conditions
     * @return int
     */
    public function deleteAll(array $conditions)
    {
        $delete = $this->getSql()->delete($this->getTable());
        $delete->where($conditions);

        $stmt = $this->getSql()->prepareStatementForSqlObject($delete);
        $result = $stmt->execute();

        return $result->getAffectedRows();
    }

    /**
     * @param array $fields
     * @param array $conditions
     * @return int
     */
    public function updateAll(array $fields, array $conditions)
    {
        $update = $this->getSql()->update($this->getTable());
        $update->set($fields)->where($conditions);

        $stmt = $this->getSql()->prepareStatementForSqlObject($update);
        return $stmt->execute()->getAffectedRows();
    }

    /**
     * Get all results finder, returns the select object unmodified
     * @param Select $select
     * @return Select
     */
    public function findAll(Select $select): Select
    {
        return $select;
    }

    /**
     * @param array $data
     * @param array $options
     * @return mixed
     */
    public function load(array $data, array $options = [])
    {
        // gives the possibility to output raw data arrays
        // note that it won't trigger load events
        if (isset($options['output']) && $options['output'] === 'array') {
            return $data;
        }

        //extract primary keys from entity
        $primaryColumns = $this->getPrimaryKey();
        $primaryKey = array_intersect_key($data, array_flip($primaryColumns));
        $primaryKey = array_filter($primaryKey);

        if (count($primaryKey) !== count($primaryColumns)) {
            throw new RuntimeException('Could not load entity due to primary key mismatch');
        }

        $mapKey = implode(',', $primaryKey);

        /** @var ResponseCollection $event */
        $event = $this->dispatchEvent(
            MapperEvent::EVENT_MAPPER_BEFORE_LOAD,
            ['data' => $data, 'options' => $options]
        );

        if ($event->stopped()) {
            return $event->last();
        }

        if (isset($this->identityMap[$mapKey])) {
            $entity = $this->identityMap[$mapKey];
        } else {
            $entity = clone $this->getPrototype();
        }

        /** @var EntityInterface $entity */
        $entity = $this->getHydrator()->hydrate($data, $entity);

        $this->dispatchEvent(
            MapperEvent::EVENT_MAPPER_AFTER_LOAD,
            ['entity' => $entity, 'data' => $data, 'options' => $options]
        );

        $this->identityMap[$mapKey] = $entity;
        return $entity;
    }

    /**
     * @param ResultSet $resultSet
     * @param array $options
     * @return array
     */
    protected function loadAll(ResultSet $resultSet, array $options = []): array
    {
        $entities = [];

        //$primaryColumns = array_flip($this->getPrimaryKey());

        /*foreach ($resultSet as $row) {
            $key = array_diff_key($row, $primaryColumns);
            $key = array_filter($key);
            $key = implode(',', $key);

            $row = Utility::arrayInflate($row);
            $row = $this->cleanJoinData($row, $options);

            if (isset($this->identityMap[$key])) {
                $entity = $this->identityMap[$key];
            } else {
                $entity = $this->load($row, $options);
                $entities[] = $entity;
            }

            // TODO: load additional data
        }*/

        $resultSet->next();
        while ($resultSet->valid()) {
            $data = $resultSet->current();
            $data = Utility::arrayInflate($data);

            $entities[] = $this->load($data, $options);
            $resultSet->next();
        }

        return $entities;
    }

    /*protected function cleanJoinData(array $data, array $options = [])
    {
        $joins = $options['joins'] ?? [];
        foreach ($joins as $alias => $join) {
            if (is_numeric($alias)) {
                continue;
            }

            if (isset($join['joins']) && is_array($join['joins'])) {
                $data[$alias] = $this->cleanJoinData($data[$alias], $join);
            }
        }
    }*/

    /**
     * @param $type
     * @param Select $select
     * @param array $options
     * @return Select
     */
    protected function callFinder($type, Select $select, array $options): Select
    {
        $select = $this->applyOptions($select, $options);
        $finder = 'find' . ucfirst($type);
        if (method_exists($this, $finder)) {
            return $this->{$finder}($select, $options);
        }

        throw new BadMethodCallException(sprintf('Unknown finder method `%s`', $type));
    }

    /**
     * @param Select $select
     * @param array $options
     * @return Select
     */
    protected function applyOptions(Select $select, array $options): Select
    {
        if (isset($options['fields']) && is_array($options['fields'])) {
            $select->columns($options['fields']);
        }

        if (isset($options['conditions']) && is_array($options['conditions'])) {
            $select->where($options['conditions']);
        }

        if (isset($options['group']) && is_array($options['group'])) {
            $select->group($options['group']);
        }

        if (isset($options['having']) && is_array($options['having'])) {
            $select->having($options['having']);
        }

        if (isset($options['order']) && is_array($options['order'])) {
            $select->order($options['order']);
        }

        if (isset($options['limit'])) {
            $select->limit((int)$options['limit']);
        }

        if (isset($options['offset'])) {
            $select->offset((int)$options['offset']);
        }

        if (isset($options['page'])) {
            $limit = 25;
            if (isset($options['limit'])) {
                $limit = (int)$options['limit'];
            }

            $offset = ((int)$options['page'] - 1) * $limit;
            if (PHP_INT_MAX <= $offset) {
                $offset = PHP_INT_MAX;
            }

            $select->offset((int)$offset);
        }

        if (isset($options['joins']) && is_array($options['joins'])) {
            $select = $this->applyJoins($select, $options['joins']);
        }

        return $select;
    }

    /**
     * @return AbstractConnection
     */
    public function getConnection(): AbstractConnection
    {
        if (!$this->connection) {
            $this->connection = $this->getAdapter()->getDriver()->getConnection();
        }

        return $this->connection;
    }

    /**
     * @return Adapter
     */
    public function getAdapter(): Adapter
    {
        return $this->adapter;
    }

    /**
     * @param Adapter $adapter
     */
    public function setAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @return Adapter
     */
    public function getSlaveAdapter(): Adapter
    {
        return $this->slaveAdapter ?? $this->adapter;
    }

    /**
     * @param Adapter $slaveAdapter
     */
    public function setSlaveAdapter(Adapter $slaveAdapter)
    {
        $this->slaveAdapter = $slaveAdapter;
    }

    /**
     * @return Sql
     */
    public function getSql(): Sql
    {
        if (!$this->sql instanceof Sql) {
            $this->sql = new Sql($this->getAdapter());
        }
        return $this->sql;
    }

    /**
     * @param Sql $sql
     */
    public function setSql(Sql $sql)
    {
        $this->sql = $sql;
    }

    /**
     * @return Sql
     */
    public function getSlaveSql(): Sql
    {
        if (!$this->slaveSql instanceof Sql) {
            $this->slaveSql = new Sql($this->getSlaveAdapter());
        }
        return $this->slaveSql;
    }

    /**
     * @param Sql $slaveSql
     */
    public function setSlaveSql(Sql $slaveSql)
    {
        $this->slaveSql = $slaveSql;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        if (is_null($this->table)) {
            //try to get table name from entity name
            $entityClass = get_class($this->getPrototype());

            $table = explode('\\', $entityClass);
            $table = end($table);

            if (strpos($table, 'Entity') !== false) {
                $table = substr($table, 0, -6);
            }

            if (empty($table) || $table === 'Entity') {
                $table = explode('\\', get_class($this));
                $table = end($table);

                $offset = -6;
                if (strpos($table, 'DbMapper') !== false) {
                    $offset = 8;
                }

                $table = substr($table, 0, $offset);
                if (empty($table) || $table === 'Entity') {
                    throw new RuntimeException(
                        'Could not generate table name from class names. Try setting the table name explicitly'
                    );
                }

                if (!in_array($table, $this->getMetadata()->getTableNames())) {
                    throw new RuntimeException(sprintf('Table `%s` does not exist'));
                }
            }

            $this->table = Utility::underscore($table);
        }

        return $this->table;
    }

    /**
     * @param string $table
     */
    public function setTable(string $table)
    {
        $this->table = $table;
    }

    /**
     * @return mixed
     */
    public function getSchema(): TableObject
    {
        if (!$this->schema instanceof TableObject) {
            $this->schema = $this->getMetadata()->getTable($this->getTable());
        }
        return $this->schema;
    }

    /**
     * @param mixed $schema
     */
    public function setSchema(TableObject $schema)
    {
        $this->schema = $schema;
    }

    /**
     * @return MetadataInterface
     */
    public function getMetadata(): MetadataInterface
    {
        if (!$this->metadata instanceof MetadataInterface) {
            $this->metadata = Factory::createSourceFromAdapter($this->getAdapter());
        }
        return $this->metadata;
    }

    /**
     * @param MetadataInterface $metadata
     */
    public function setMetadata(MetadataInterface $metadata)
    {
        $this->metadata = $metadata;
    }

    public function aliasField(string $field): string
    {
        if (strpos($field, '.') !== false) {
            return $field;
        }

        return $this->getAlias() . '.' . $field;
    }

    /**
     * @param $field
     * @return bool
     */
    public function hasField(string $field): bool
    {
        return in_array($field, $this->getColumns());
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        if (!$this->columns) {
            $this->columns = [];
            /** @var ColumnObject $column */
            foreach ($this->getSchema()->getColumns() as $column) {
                $this->columns[] = $column->getName();
            }
        }

        return $this->columns;
    }

    /**
     * @return array
     */
    public function getPrimaryKey(): array
    {
        if (!$this->primaryKey) {
            $keys = [];
            /** @var ConstraintObject $constraint */
            foreach ($this->getSchema()->getConstraints() as $constraint) {
                if ($constraint->isPrimaryKey() && $constraint->hasColumns()) {
                    $keys = array_merge($keys, $constraint->getColumns());
                }
            }

            $this->primaryKey = $keys;
        }

        return $this->primaryKey;
    }

    /**
     * @param array $primaryKey
     */
    public function setPrimaryKey(array $primaryKey)
    {
        $this->primaryKey = $primaryKey;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        if (is_null($this->alias)) {
            $this->alias = Utility::camelCase($this->getTable());
        }
        return $this->alias;
    }

    /**
     * @param string $alias
     */
    public function setAlias(string $alias)
    {
        $this->alias = $alias;
    }

    /**
     * @return HydratorInterface
     */
    public function getHydrator(): HydratorInterface
    {
        if (!$this->hydrator) {
            if ($this->getHydratorPluginManager()->has($this->getPrototype()->hydrator())) {
                $this->hydrator = $this->getHydratorPluginManager()->get($this->getPrototype()->hydrator());
            } else {
                $this->hydrator = new ClassMethodsCamelCase();
            }
        }

        return $this->hydrator;
    }

    /**
     * @param HydratorInterface $hydrator
     */
    public function setHydrator(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;
    }

    /**
     * @return EntityInterface
     */
    public function getPrototype(): EntityInterface
    {
        return $this->prototype;
    }

    /**
     * @param EntityInterface $prototype
     */
    public function setPrototype(EntityInterface $prototype)
    {
        $this->prototype = $prototype;
    }

    /**
     * @return HydratorPluginManager
     */
    public function getHydratorPluginManager(): HydratorPluginManager
    {
        if (!$this->hydratorPluginManager instanceof HydratorPluginManager) {
            $this->hydratorPluginManager = new HydratorPluginManager(new ServiceManager());
        }

        return $this->hydratorPluginManager;
    }

    /**
     * @param HydratorPluginManager $hydratorPluginManager
     */
    public function setHydratorPluginManager(HydratorPluginManager $hydratorPluginManager)
    {
        $this->hydratorPluginManager = $hydratorPluginManager;
    }

    /**
     * @param Select $select
     * @param array $joins
     * @param string $parentAlias
     * @return Select
     */
    protected function applyJoins(Select $select, array $joins, string $parentAlias = ''): Select
    {
        foreach ($joins as $alias => $join) {
            if (is_array($join)) {
                if (is_numeric($alias)) {
                    $alias = '';
                }
                if (empty($alias) && !isset($join['table'])) {
                    throw new RuntimeException('Table join must be specified for non-aliased relations');
                }

                $table = $join['table'] ?? Utility::underscore($alias);
            } else {
                throw new InvalidArgumentException('Invalid joins specification');
            }

            $on = $join['on'] ?? '';
            if (empty($on)) {
                throw new InvalidArgumentException('ON clause must be specified in join');
            }

            // for a join, explicitly alias the columns, in order to be able to inflate results
            $columns = $join['fields'] ?? Select::SQL_STAR;
            if ($columns === Select::SQL_STAR) {
                $columns = $this->getMetadata()->getColumnNames($table);
            }

            $columnAlias = empty($parentAlias)
                ? $alias
                : (empty($alias)
                    ? $parentAlias
                    : $parentAlias . '.' . $alias
                );

            $aliasedColumns = [];
            foreach ($columns as $k => $column) {
                $key = empty($columnAlias) ? $column : $columnAlias . '.' . $column;
                $aliasedColumns[$key] = $column;
            }

            $type = $join['type'] ?? Select::JOIN_INNER;
            $table = empty($alias) ? $table : [$alias => $table];
            $select->join($table, $on, $aliasedColumns, $type);

            if (is_array($join) && isset($join['joins']) && is_array($join['joins'])) {
                $this->applyJoins($select, $join['joins'], $columnAlias);
            }
        }

        return $select;
    }

    /**
     * @return \Zend\Db\Adapter\Driver\ConnectionInterface
     */
    public function beginTransaction()
    {
        return $this->getConnection()->beginTransaction();
    }

    /**
     * @return bool
     */
    public function inTransaction(): bool
    {
        return $this->getConnection()->inTransaction();
    }

    /**
     * @return \Zend\Db\Adapter\Driver\ConnectionInterface
     */
    public function commit()
    {
        return $this->getConnection()->commit();
    }

    /**
     * @return \Zend\Db\Adapter\Driver\ConnectionInterface
     */
    public function rollback()
    {
        return $this->getConnection()->rollback();
    }

    /**
     * @param string|null $name
     * @return int
     */
    public function lastGeneratedValue(string $name = null)
    {
        return $this->getConnection()->getLastGeneratedValue($name);
    }

    /**
     * @return EntityInterface
     */
    public function newEntity(): EntityInterface
    {
        return clone $this->getPrototype();
    }

    /**
     * @param $identifier
     * @return string
     */
    public function quoteIdentifier($identifier): string
    {
        return $this->adapter->getPlatform()->quoteIdentifier($identifier);
    }

    /**
     * @param $value
     * @return string
     */
    public function quoteValue($value): string
    {
        return $this->adapter->getPlatform()->quoteValue($value);
    }
}

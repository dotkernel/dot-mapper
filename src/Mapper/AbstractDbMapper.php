<?php
/**
 * @copyright: DotKernel
 * @library: dot-ems
 * @author: n3vrax
 * Date: 2/8/2017
 * Time: 4:03 PM
 */

declare(strict_types = 1);

namespace Dot\Ems\Mapper;

use Dot\Ems\Entity\EntityInterface;
use Dot\Ems\Event\DispatchMapperEventsTrait;
use Dot\Ems\Event\MapperEvent;
use Dot\Ems\Event\MapperEventListenerInterface;
use Dot\Ems\Event\MapperEventListenerTrait;
use Dot\Ems\Exception\BadMethodCallException;
use Dot\Ems\Exception\InvalidArgumentException;
use Dot\Ems\Exception\RuntimeException;
use Dot\Hydrator\ClassMethodsCamelCase;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ConnectionInterface;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Metadata\MetadataInterface;
use Zend\Db\Metadata\Object\ColumnObject;
use Zend\Db\Metadata\Object\ConstraintObject;
use Zend\Db\Metadata\Object\TableObject;
use Zend\Db\Metadata\Source\Factory;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ResponseCollection;
use Zend\Hydrator\HydratorInterface;
use Zend\Hydrator\HydratorPluginManager;
use Zend\ServiceManager\ServiceManager;

/**
 * Class AbstractDbMapper
 * @package Test\Ems\Mapper
 */
abstract class AbstractDbMapper implements MapperInterface, MapperEventListenerInterface
{
    use DispatchMapperEventsTrait;
    use MapperEventListenerTrait;

    /** @var array */
    protected $identityMap = [];

    /** @var  HydratorPluginManager */
    protected $hydratorPluginManager;

    /** @var  MetadataInterface */
    protected $metadata;

    /** @var  Adapter */
    protected $adapter;

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
     * @param array $options
     */
    public function __construct(array $options = [])
    {
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

        if (isset($options['event_manager']) && $options['event_manager'] instanceof EventManagerInterface) {
            $this->setEventManager($options['event_manager']);
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

        $this->dispatchEvent(MapperEvent::EVENT_MAPPER_BEFORE_FIND, ['select' => $select, 'options' => $options]);

        $stmt = $this->getSlaveSql()->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet(ResultSet::TYPE_ARRAY);
            $resultSet->initialize($result);
            return $this->loadAll($resultSet);
        }

        return [];
    }

    /**
     * @param $primaryKey
     * @param array $options
     * @return EntityInterface|mixed|null|object
     */
    public function get($primaryKey, array $options = []): ?EntityInterface
    {
        $primaryKey = (array)$primaryKey;
        $mapKey = implode(',', $primaryKey);

        if (isset($this->identityMap[$mapKey])) {
            return $this->identityMap[$mapKey];
        }

        $keys = (array)$this->getPrimaryKey();
        foreach ($keys as $index => $keyname) {
            $keys[$index] = $this->aliasField($keyname);
        }

        if (count($primaryKey) !== count($keys)) {
            throw new InvalidArgumentException(
                'Invalid primary key given. Provide all primary keys that identifies the object'
            );
        }

        $options['conditions'] = array_combine($keys, $primaryKey);

        $finder = (string)($options['finder'] ?? 'all');
        $result = $this->find($finder, $options);
        if (!empty($result) && $result[0] instanceof EntityInterface) {
            return $result[0];
        }

        return null;
    }

    /**
     * @param EntityInterface $entity
     * @param array $options
     * @return bool|EntityInterface
     */
    public function save(EntityInterface $entity, array $options = [])
    {
        $atomic = (bool)($options['atomic'] ?? false);
        if ($atomic) {
            try {
                $this->getConnection()->beginTransaction();
                $success = $this->processSave($entity, $options);
                $this->getConnection()->commit();
            } catch (\Exception $e) {
                $success = false;
                $this->getConnection()->rollback();
            }
        } else {
            $success = $this->processSave($entity, $options);
        }

        if ($success) {
            if ($atomic) {
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
        $primaryColumns = (array)$this->getPrimaryKey();
        $tableColumns = $this->getColumns();

        $data = $this->getHydrator()->extract($entity);
        $data = array_intersect_key($data, array_flip($tableColumns));

        $primaryKey = array_intersect_key($data, array_flip($primaryColumns));
        $primaryKey = array_filter($primaryKey);
        $mapKey = implode(',', $primaryKey);

        /** @var ResponseCollection $event */
        $event = $this->dispatchEvent(
            MapperEvent::EVENT_MAPPER_BEFORE_SAVE,
            ['entity' => $entity, 'options' => $options]
        );

        if ($event->stopped()) {
            return $event->last();
        }

        $isNew = empty($primaryKey) && !isset($this->identityMap[$mapKey]);

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
            $this->identityMap[$mapKey] = $entity;
        }

        return $success ? $entity : false;
    }

    /**
     * @param EntityInterface $entity
     * @param bool $isNew
     * @param array $options
     * @return bool
     */
    protected function onSaveSuccess(EntityInterface $entity, bool $isNew, array $options)
    {
        //TODO: implement save associations in the future

        $this->dispatchEvent(
            MapperEvent::EVENT_MAPPER_AFTER_SAVE,
            ['entity' => $entity, 'options' => $options, 'isNew' => $isNew]
        );

        return true;
    }

    /**
     * @param EntityInterface $entity
     * @param array $data
     * @return bool|EntityInterface
     */
    protected function insert(EntityInterface $entity, array $data)
    {
        $primary = (array)$this->getPrimaryKey();
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
        $primaryColumns = (array)$this->getPrimaryKey();
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
        if ($result->getAffectedRows() !== 0) {
            $success = $entity;
        }

        return $success;
    }

    /**
     * @param EntityInterface $entity
     * @param array $options
     * @return bool
     */
    public function delete(EntityInterface $entity, array $options = [])
    {
        $atomic = (bool)($options['atomic'] ?? false);
        if ($atomic) {
            try {
                $this->getConnection()->beginTransaction();
                $success = $this->processDelete($entity, $options);
                $this->getConnection()->commit();
            } catch (\Exception $e) {
                $success = false;
                $this->getConnection()->rollback();
            }
        } else {
            $success = $this->processDelete($entity, $options);
        }

        if ($success) {
            if ($atomic) {
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
        $primaryColumns = (array)$this->getPrimaryKey();

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
     * @param ResultSet $resultSet
     * @return EntityInterface
     */
    public function load(ResultSet $resultSet): EntityInterface
    {
        $data = $resultSet->current();

        //extract primary keys from entity
        $primaryColumns = (array)$this->getPrimaryKey();
        $primaryKey = array_intersect_key($data, array_flip($primaryColumns));
        $primaryKey = array_filter($primaryKey);

        if (count($primaryKey) !== count($primaryColumns)) {
            throw new RuntimeException('Could not load entity due to primary key mismatch');
        }

        $mapKey = implode(',', $primaryKey);

        /** @var ResponseCollection $event */
        $event = $this->dispatchEvent(
            MapperEvent::EVENT_MAPPER_BEFORE_LOAD,
            ['resultSet' => $resultSet, 'data' => $data]
        );

        if ($event->stopped()) {
            return $event->last();
        }

        if (isset($this->identityMap[$mapKey])) {
            $entity = $this->identityMap[$mapKey];
        } else {
            $entity = $this->getHydrator()->hydrate($data, clone $this->getPrototype());
        }

        $this->dispatchEvent(
            MapperEvent::EVENT_MAPPER_AFTER_LOAD,
            ['entity' => $entity, 'data' => $data, 'resultSet' => $resultSet]
        );

        $this->identityMap[$mapKey] = $entity;
        return $entity;
    }

    /**
     * @param ResultSet $resultSet
     * @return array
     */
    protected function loadAll(ResultSet $resultSet): array
    {
        $entities = [];
        $resultSet->next();
        while ($resultSet->valid()) {
            $entities[] = $this->load($resultSet);
            $resultSet->next();
        }
        return $entities;
    }

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

        if (isset($options['join']) && is_array($options['join'])) {
            foreach ($options['join'] as $table => $join) {
                $alias = $join['alias'] ?? '';
                $on = $join['on'] ?? '';
                $columns = $join['fields'] ?? Select::SQL_STAR;
                $type = $join['type'] ?? Select::JOIN_INNER;

                $table = empty($alias) ? $table : [$alias => $table];

                $select->join($table, $on, $columns, $type);
            }
        }

        return $select;
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnection(): ConnectionInterface
    {
        return $this->getAdapter()->getDriver()->getConnection();
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
            $table = explode('\\', get_class($this));
            $table = substr(end($table), 0, -6);

            if (empty($table) || $table === 'Entity') {
                //try to get table name from entity name
                $entityClass = get_class($this->getPrototype());

                $table = explode('\\', get_class($entityClass));
                $table = substr(end($table), 0, -6);

                if (empty($table) || $table === 'Entity') {
                    throw new RuntimeException(
                        'Could not generate table name from class names. Try setting the table name explicitly'
                    );
                }

                if (!in_array($table, $this->getMetadata()->getTableNames())) {
                    throw new RuntimeException(sprintf('Table `%s` does not exist'));
                }
            }

            $this->table = $this->underscore($table);
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
            $this->alias = $this->getTable();
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
     * @param string $string
     * @return string
     */
    protected function underscore(string $string): string
    {
        return $this->delimit(str_replace('-', '_', $string), '_');
    }

    /**
     * @param string $string
     * @param string $delimiter
     * @return string
     */
    protected function delimit(string $string, string $delimiter = '_'): string
    {
        return mb_strtolower(preg_replace('/(?<=\\w)([A-Z])/', $delimiter . '\\1', $string));
    }
}

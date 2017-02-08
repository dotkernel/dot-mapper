<?php
/**
 * @copyright: DotKernel
 * @library: dot-ems
 * @author: n3vra
 * Date: 2/8/2017
 * Time: 4:03 PM
 */

declare(strict_types = 1);

namespace Dot\Ems\Mapper;

use Dot\Ems\Entity\EntityInterface;
use Dot\Ems\Exception\BadMethodCallException;
use Dot\Ems\Exception\RuntimeException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Hydrator\HydratorInterface;

/**
 * Class AbstractDbMapper
 * @package Dot\Ems\Mapper
 */
abstract class AbstractDbMapper implements MapperInterface
{
    /** @var array  */
    protected $identityMap = [];

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

        if (! $this->adapter instanceof Adapter) {
            throw new RuntimeException('Db adapter is required and was not set');
        }
    }

    /**
     * @param string $type
     * @param array $options
     * @return array|HydratingResultSet
     */
    public function find(string $type = 'all', array $options = [])
    {
        $select = $this->getSlaveSql()->select($this->getTable());
        $select = $this->callFinder($type, $select, $options);

        $stmt = $this->getSlaveSql()->prepareStatementForSqlObject($select);
        $result = $stmt->execute();
        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new HydratingResultSet($this->getHydrator(), $this->getPrototype());
            $resultSet->initialize($result);
            return $resultSet;
        }

        return [];
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
        $finder = 'find' . $type;
        if (method_exists($this, $finder)) {
            return $this->{$finder}($select, $options);
        }

        throw new BadMethodCallException(sprintf('Unknown finder method `%s`', $type));
    }

    protected function applyOptions(Select $query, array &$options): Select
    {

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
        if (! $this->sql instanceof Sql) {
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
        if (! $this->slaveSql instanceof Sql) {
            $this->sql = new Sql($this->getSlaveAdapter());
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
     * @return HydratorInterface
     */
    public function getHydrator(): HydratorInterface
    {
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
}

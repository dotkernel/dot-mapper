<?php
/**
 * @copyright: DotKernel
 * @library: dot-ems
 * @author: n3vrax
 * Date: 2/8/2017
 * Time: 4:02 PM
 */

declare(strict_types = 1);

namespace Dot\Mapper\Mapper;

use Dot\Mapper\Entity\EntityInterface;
use Zend\Hydrator\HydratorInterface;

/**
 * Interface MapperInterface
 * @package Dot\Mapper\Mapper
 */
interface MapperInterface
{
    /**
     * Begins a transaction if backend is accepting
     */
    public function beginTransaction();

    /**
     * Commits the opened transactions
     */
    public function commit();

    /**
     * Rollback the transaction
     */
    public function rollback();

    /**
     * @param string $name
     * @return mixed
     */
    public function lastGeneratedValue(string $name = null);

    /**
     * @param $identifier
     * @return string
     */
    public function quoteIdentifier($identifier): string;

    /**
     * @return mixed
     */
    public function getPrimaryKey(): array;

    /**
     * @return array
     */
    public function getColumns(): array;

    /**
     * @return EntityInterface
     */
    public function getPrototype(): EntityInterface;

    /**
     * @return HydratorInterface
     */
    public function getHydrator(): HydratorInterface;

    /**
     * @param $value
     * @return string
     */
    public function quoteValue($value): string;

    /**
     * Used to get lists of entities
     * @param string $type
     * @param array $options
     * @return mixed
     */
    public function find(string $type, array $options = []): array;

    /**
     * @param string $type
     * @param array $options
     * @return int
     */
    public function count(string $type, array $options = []): int;

    /**
     * Gets an entity by its ID
     *
     * @param $primaryKey
     * @param array $options
     * @return mixed
     */
    public function get($primaryKey, array $options = []);

    /**
     * @param EntityInterface $entity
     * @param array $options
     * @return mixed
     */
    public function save(EntityInterface $entity, array $options = []);

    /**
     * @param EntityInterface $entity
     * @param array $options
     * @return mixed
     */
    public function delete(EntityInterface $entity, array $options = []);

    /**
     * @param array $fields
     * @param array $conditions
     * @return mixed
     */
    public function updateAll(array $fields, array $conditions);

    /**
     * @param array $conditions
     * @return mixed
     */
    public function deleteAll(array $conditions);

    /**
     * @return EntityInterface
     */
    public function newEntity(): EntityInterface;
}

<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/15/2016
 * Time: 10:24 PM
 */

declare(strict_types=1);

namespace Dot\Ems\Mapper;

use Zend\Hydrator\HydratorInterface;

/**
 * Interface MapperInterface
 * @package Dot\Ems\Mapper
 */
interface MapperInterface
{
    /**
     * Gets the last inserted id value
     * @return string
     */
    public function lastInsertValue(): string;

    /**
     * Begins a backend transaction, may vary between backend types
     */
    public function beginTransaction();

    /**
     * Saves the operations from a transaction
     */
    public function commit();

    /**
     * In case of error, revert changes to backend
     */
    public function rollback();

    /**
     * @param array $options
     * @return object
     */
    public function find(array $options = []): ?object;

    /**
     * @param array $options
     * @return array
     */
    public function findAll(array $options = []): array;

    /**
     * @param object $entity
     * @param array $options
     * @return int
     */
    public function create(object $entity, array $options = []): int;

    /**
     * @param $entity
     * @param array $options
     * @return int
     */
    public function update(object $entity, array $options = []): int;

    /**
     * @param array $options
     * @return int
     */
    public function delete(array $options = []): int;

    /**
     * @return object
     */
    public function getPrototype(): object;

    /**
     * @return HydratorInterface
     */
    public function getHydrator(): HydratorInterface;

    /**
     * @return mixed
     */
    public function getIdentifierName(): string;

    /**
     * @param $name
     * @return mixed
     */
    public function setIdentifierName(string $name);

    /**
     * @param string $name
     * @param array $specs
     */
    public function addRelation(string $name, array $specs = []);

    /**
     * @param string $name
     * @param array $options
     */
    public function hasOne(string $name, array $options = []);

    /**
     * @param string $name
     * @param array $options
     */
    public function belongsTo(string $name, array $options = []);

    /**
     * @param string $name
     * @param array $options
     */
    public function hasMany(string $name, array $options = []);

    /**
     * @param string $name
     * @param array $options
     */
    public function hasManyBelongsToMany(string $name, array $options = []);
}

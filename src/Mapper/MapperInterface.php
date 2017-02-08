<?php
/**
 * @copyright: DotKernel
 * @library: dot-ems
 * @author: n3vra
 * Date: 2/8/2017
 * Time: 4:02 PM
 */

declare(strict_types = 1);

namespace Dot\Ems\Mapper;

use Dot\Ems\Entity\EntityInterface;

/**
 * Interface MapperInterface
 * @package Dot\Ems\Mapper
 */
interface MapperInterface
{
    /**
     * Used to get lists of entities
     * @param string $type
     * @param array $options
     * @return mixed
     */
    public function find(string $type, array $options = []);

    /**
     * Gets an entity by its ID
     *
     * @param $id
     * @param array $options
     * @return mixed
     */
    public function get($id, array $options = []);

    /**
     * @param EntityInterface $entity
     * @param array $options
     * @return mixed
     */
    public function insert(EntityInterface $entity, array $options = []);

    /**
     * @param EntityInterface $entity
     * @param array $options
     * @return mixed
     */
    public function update(EntityInterface $entity, array $options = []);

    /**
     * @param EntityInterface $entity
     * @param array $options
     * @return mixed
     */
    public function delete(EntityInterface $entity, array $options = []);

    /**
     * @param array $fields
     * @param array $options
     * @return mixed
     */
    public function updateAll(array $fields, array $options = []);

    /**
     * @param array $options
     * @return mixed
     */
    public function deleteAll(array $options = []);
}

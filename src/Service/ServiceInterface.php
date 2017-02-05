<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/15/2016
 * Time: 10:03 PM
 */

declare(strict_types=1);

namespace Dot\Ems\Service;

use Dot\Ems\Mapper\MapperInterface;
use Dot\Ems\Result\DeleteResult;
use Dot\Ems\Result\FindResult;
use Dot\Ems\Result\SaveResult;
use Zend\EventManager\EventManagerAwareInterface;

/**
 * Interface ServiceInterface
 * @package Dot\Ems\Service
 */
interface ServiceInterface extends EventManagerAwareInterface
{
    /**
     * @param array $options
     * @return FindResult
     */
    public function find(array $options = []): FindResult;

    /**
     * @param array $options
     * @return FindResult
     */
    public function findAll(array $options = []): FindResult;

    /**
     * @param object $entity
     * @param array $options
     * @return SaveResult
     */
    public function save(object $entity, array $options = []): SaveResult;

    /**
     * @param object $entity
     * @param array $options
     * @return DeleteResult
     */
    public function deleteEntity(object $entity, array $options = []): DeleteResult;

    /**
     * @param array $options
     * @return DeleteResult
     */
    public function delete(array $options = []): DeleteResult;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param $name
     */
    public function setName(string $name);

    /**
     * @param bool $value
     */
    public function setEnableEvents(bool $value);

    /**
     * @return bool
     */
    public function isEnableEvents(): bool;

    /**
     * @return MapperInterface
     */
    public function getMapper(): MapperInterface;

    /**
     * @param MapperInterface $mapper
     */
    public function setMapper(MapperInterface $mapper);
}

<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/15/2016
 * Time: 10:03 PM
 */

namespace Dot\Ems\Service;

/**
 * Interface ServiceInterface
 * @package Dot\Ems\Service
 */
interface ServiceInterface
{
    /**
     * @param $where
     * @return mixed
     */
    public function find($where);

    /**
     * @param array $where
     * @param array $filters
     * @param bool $paginated
     * @return mixed
     */
    public function findAll($where = [], $filters = [], $paginated = false);

    /**
     * @param $entity
     * @return mixed
     */
    public function save($entity);

    /**
     * @param $entity
     * @return mixed
     */
    public function delete($entity);
}
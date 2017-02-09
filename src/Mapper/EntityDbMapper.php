<?php
/**
 * @copyright: DotKernel
 * @library: dot-ems
 * @author: n3vrax
 * Date: 2/9/2017
 * Time: 5:50 PM
 */

declare(strict_types = 1);

namespace Dot\Ems\Mapper;

use Dot\Ems\Entity\EntityInterface;

/**
 * Class EntityDbMapper
 * @package Dot\Ems\Mapper
 */
class EntityDbMapper extends AbstractDbMapper
{
    /**
     * @param array $primaryKey
     * @param array $data
     * @return EntityInterface
     */
    public function loadEntity(array $primaryKey, array $data): EntityInterface
    {
        /** @var EntityInterface $entity */
        $entity = $this->getHydrator()->hydrate($data, clone $this->getPrototype());
        return $entity;
    }
}
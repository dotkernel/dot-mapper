<?php
/**
 * @copyright: DotKernel
 * @library: dot-ems
 * @author: n3vra
 * Date: 2/8/2017
 * Time: 4:48 PM
 */

declare(strict_types = 1);

namespace Dot\Ems\Entity;

use Dot\Hydrator\ClassMethodsCamelCase;

/**
 * Class Entity
 * @package Dot\Ems\Entity
 */
abstract class Entity implements EntityInterface
{
    /** @var string */
    protected $hydrator = ClassMethodsCamelCase::class;

    /**
     * @return string
     */
    public function hydrator(): string
    {
        return $this->hydrator;
    }
}

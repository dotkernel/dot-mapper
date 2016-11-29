<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/29/2016
 * Time: 8:15 PM
 */

namespace Dot\Ems\Options;


use Dot\Ems\Service\EntityService;
use Zend\Stdlib\AbstractOptions;

class ServiceOptions extends AbstractOptions
{
    /** @var bool  */
    protected $atomicOperations = true;

    /** @var  string */
    protected $type = EntityService::class;

    /** @var  array */
    protected $mapper;

    /**
     * @return boolean
     */
    public function isAtomicOperations()
    {
        return $this->atomicOperations;
    }

    /**
     * @param boolean $atomicOperations
     * @return ServiceOptions
     */
    public function setAtomicOperations($atomicOperations)
    {
        $this->atomicOperations = $atomicOperations;
        return $this;
    }

    /**
     * @return array
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * @param array $mapper
     * @return ServiceOptions
     */
    public function setMapper($mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return ServiceOptions
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

}
<?php
/**
 * @copyright: DotKernel
 * @library: dot-ems
 * @author: n3vra
 * Date: 2/5/2017
 * Time: 2:17 PM
 */

declare(strict_types = 1);

namespace Dot\Ems\Result;

use Dot\Ems\Service\ServiceInterface;

/**
 * Class AbstractResult
 * @package Dot\Ems\Result
 */
abstract class AbstractResult implements ResultInterface
{
    /** @var  ServiceInterface */
    protected $service;

    /** @var  mixed */
    protected $data;

    /** @var  mixed */
    protected $error;

    /**
     * AbstractResult constructor.
     * @param ServiceInterface $service
     * @param $data
     * @param mixed|null $error
     */
    public function __construct(
        ServiceInterface $service,
        $data = null,
        mixed $error = null
    ) {
        $this->service = $service;
        $this->data = $data;
        $this->error = $error;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return ! $this->hasError();
    }

    /**
     * @return ServiceInterface
     */
    public function getService(): ServiceInterface
    {
        return $this->service;
    }

    /**
     * @param ServiceInterface $service
     */
    public function setService(ServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getError(): ?mixed
    {
        return $this->error;
    }

    /**
     * @param mixed $error
     */
    public function setError(mixed $error)
    {
        $this->error = $error;
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return $this->error !== null;
    }

    /**
     * @return bool
     */
    public function hasException(): bool
    {
        return $this->error instanceof \Exception;
    }
}

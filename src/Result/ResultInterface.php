<?php
/**
 * @copyright: DotKernel
 * @library: dot-ems
 * @author: n3vra
 * Date: 2/5/2017
 * Time: 2:15 PM
 */

declare(strict_types = 1);

namespace Dot\Ems\Result;

use Dot\Ems\Service\ServiceInterface;

/**
 * Interface ResultInterface
 * @package Dot\Ems\Result
 */
interface ResultInterface
{
    /**
     * @return bool
     */
    public function isValid(): bool;

    /**
     * @return ServiceInterface
     */
    public function getService(): ServiceInterface;

    /**
     * @return mixed
     */
    public function getData();

    /**
     * @return bool
     */
    public function hasError(): bool;

    /**
     * @return bool
     */
    public function hasException(): bool;

    /**
     * @return mixed
     */
    public function getError(): mixed;
}

<?php
/**
 * @copyright: DotKernel
 * @library: dot-ems
 * @author: n3vra
 * Date: 2/5/2017
 * Time: 2:23 PM
 */

declare(strict_types = 1);

namespace Dot\Ems\Result;

/**
 * Class SaveResult
 * @package Dot\Ems\Result
 */
class SaveResult extends AbstractResult
{
    /** @var int  */
    protected $affectedRows = 0;

    /**
     * @return int
     */
    public function getAffectedRows(): int
    {
        return $this->affectedRows;
    }

    /**
     * @param int $affectedRows
     */
    public function setAffectedRows(int $affectedRows)
    {
        $this->affectedRows = $affectedRows;
    }
}

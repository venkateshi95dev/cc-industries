<?php
/**
 * @namespace   Crimson
 * @module      MachBase
 * @author      Matheus Gontijo
 * @email       mgontijo@crimsonagility.com
 * @date        12/20/2018 9:54 PM
 * @brief
 */

namespace Crimson\MachBase\Model;

use Crimson\MachBase\Model\Api\Logger;

/**
 * Class Log
 * @package Crimson\MachBase\Model
 */
class Log
{
    /** @var Logger|null */
    protected $debugLog = null;

    /** @var Logger|null */
    protected $infoLog = null;

    /** @var Logger|null */
    protected $critical = null;

    public function __construct(
        Logger $debugLog,
        Logger $infoLog,
        Logger $critical
    ) {
        $this->debugLog = $debugLog;
        $this->infoLog  = $infoLog;
        $this->critical = $critical;
    }

    /**
     * @return Logger|null
     */
    public function getDebugLog(): ?Logger
    {
        return $this->debugLog;
    }

    /**
     * @return Logger|null
     */
    public function getInfoLog(): ?Logger
    {
        return $this->infoLog;
    }

    /**
     * @return Logger|null
     */
    public function getCriticalLog(): ?Logger
    {
        return $this->critical;
    }
}

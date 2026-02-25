<?php
/**
 * @namespace   Crimson
 * @module      MachBase
 * @author      Matheus Gontijo
 * @email       mgontijo@crimsonagility.com
 * @date        12/20/2018 9:54 PM
 * @brief
 */

namespace Crimson\MachBase\Model\Logger\Handler;

/**
 * Class Critical
 * @package Crimson\MachBase\Model\Logger\Handler
 */
class Critical extends \Magento\Framework\Logger\Handler\Debug
{
    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/mach-critical.log';
}

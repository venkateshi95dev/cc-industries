<?php
/**
 * @namespace   Crimson
 * @module      ScheduledImport
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        6/24/2019 1:26 PM
 * @brief
 */

namespace Crimson\ScheduledImport\Model;

/**
 * Interface PreProcessPoolInterface
 * @package Crimson\ScheduledImport\Model
 */
interface PreProcessPoolInterface
{
    public function processByPath(string $absolutePath): void;
}

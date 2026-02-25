<?php
/**
 * @namespace   Crimson
 * @module      ScheduledImport
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        6/24/2019 12:58 PM
 * @brief       Remove BOM (byte-order-mark) from file to import if present.
 */

namespace Crimson\ScheduledImport\Model\Pool\Service;

use Crimson\ScheduledImport\Model\PreProcessPoolInterface;

/**
 * Class BOMDisposal
 * @package Crimson\ScheduledImport\Model\Pool\Service
 */
class BOMDisposal implements PreProcessPoolInterface
{
    /**
     * @param string $absoluteFilePath
     */
    public function processByPath(string $absoluteFilePath): void
    {
        if (!file_exists($absoluteFilePath)) {
            return;
        }

        $string = @file_get_contents($absoluteFilePath);
        if (!$string) {
            return;
        }

        if ($string !== false && substr($string, 0, 3) == pack("CCC", 0xef, 0xbb, 0xbf)) {
            $string = substr($string, 3);
            file_put_contents($absoluteFilePath, $string);
        }

        return;
    }
}

<?php
/**
 * @namespace   Crimson
 * @module      MachBase
 * @author      Matheus Gontijo
 * @email       mgontijo@crimsonagility.com
 * @date        12/20/2018 9:54 PM
 * @brief
 */

namespace Crimson\MachBase\Model\Logger;

/**
 * Class BuildExceptionMessage
 * @package Crimson\MachBase\Model\Logger
 */
class BuildExceptionMessage
{
    /**
     * @param \Exception $e
     * @return string
     */
    public function build(\Exception $e): string
    {
        $message  = sprintf('Message: %s <br />%s', $e->getMessage(), PHP_EOL);
        $message .= sprintf('File: %s <br />%s', $e->getFile(), PHP_EOL);
        $message .= sprintf('Line: %s <br />%s', $e->getLine(), PHP_EOL);
        $message .= sprintf('Line: %1$s <br />%2$s <br />%2$s', $e->getTraceAsString(), PHP_EOL);

        return $message;
    }
}

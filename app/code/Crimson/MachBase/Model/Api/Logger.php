<?php
/**
 * @namespace   Crimson
 * @module      MachBase
 * @author      Matheus Gontijo
 * @email       mgontijo@crimsonagility.com
 * @date        12/20/2018 9:54 PM
 * @brief
 */

namespace Crimson\MachBase\Model\Api;

use Crimson\MachBase\Model\Logger\BuildExceptionMessage;
use Crimson\MachBase\Model\MachConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;

/**
 * Class Logger
 * @package Crimson\MachBase\Model\Api
 */
class Logger extends \Monolog\Logger
{
    const DEBUG_KEYS_MASK = '****';

    /**
     * @var BuildExceptionMessage
     */
    protected $buildExceptionMessage;

    /**
     * @var array
     */
    protected $debugReplaceKeys;
    /**
     * @var MachConfig
     */
    protected $machConfig;

    /**
     * Logger constructor.
     *
     * @param string                                             $name
     * @param BuildExceptionMessage                              $buildExceptionMessage
     * @param MachConfig                                         $machConfig
     * @param array                                              $handlers
     * @param array                                              $processors
     * @param array                                              $debugReplaceKeys
     */
    public function __construct(
        $name,
        BuildExceptionMessage $buildExceptionMessage,
        MachConfig $machConfig,
        array $handlers = array(),
        array $processors = array(),
        array $debugReplaceKeys = array()
    ) {
        parent::__construct($name, $handlers, $processors);

        $this->buildExceptionMessage = $buildExceptionMessage;
        $this->debugReplaceKeys = array_keys($debugReplaceKeys);
        $this->machConfig = $machConfig;
    }

    /**
     * Adds a log record.
     *
     * @param integer                                         $level   The logging level
     * @param string|\Exception|DataObject $message The log message
     * @param array                                           $context The log context
     * @param bool                                             $force - will ignore log level check
     *
     * @return bool Whether the record has been processed
     */
    public function addRecord($level, $message, array $context = [], $force = false): bool
    {
        $availableLevels = $this->availableLevels();

        if (!$force && !in_array($level, $availableLevels)) {
            return true;
        }

        if ($message instanceof \Exception) {
            $context['is_exception'] = true;
            $message                 = $this->buildExceptionMessage->build($message);
        } elseif (is_object($message) && method_exists($message, 'debug')) {
            $message = $message->debug();
        }

        if (is_array($message)) {
            $message = $this->filterDebugData($message);

            //this attempts to go one level down to parse inputs properly.
            foreach ($message as $key => $value) {
                if ($value instanceof \Exception) {
                    $context['is_exception'] = true;
                    $message[$key]           = $this->buildExceptionMessage->build($value);
                } elseif (is_object($value) && method_exists($value, 'debug')) {
                    $message[$key] = $value->debug();
                }
            }

            $message = print_r($message, true);
        }

        $message = sprintf('PID: %s | %s', getmypid(), $message);

        return parent::addRecord($level, $message, $context);
    }

    /**
     * Recursive filter data by private conventions
     *
     * @param array $debugData
     *
     * @return array
     */
    protected function filterDebugData(array $debugData): array
    {
        $debugReplacePrivateDataKeys = array_map('strtolower', $this->debugReplaceKeys);

        foreach (array_keys($debugData) as $key) {
            if (in_array(strtolower($key), $debugReplacePrivateDataKeys)) {
                $debugData[$key] = self::DEBUG_KEYS_MASK;
            } elseif (is_array($debugData[$key])) {
                $debugData[$key] = $this->filterDebugData($debugData[$key]);
            }
        }

        return $debugData;
    }

    /**
     * @return array
     */
    protected function availableLevels(): array
    {
        $levelsString = $this->machConfig->getLogLevel();

        if (!$levelsString) {
            return [];
        }

        $levels = explode(',', $levelsString);

        if ($levels) {
            return $levels;
        }

        return [];
    }
}

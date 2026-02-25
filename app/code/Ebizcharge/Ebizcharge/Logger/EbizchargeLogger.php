<?php
/**
 * Century Business Solutions
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the centurybizsolutions.com license that is
 * available through the URL:  https://www.centurybizsolutions/License.txt
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file to upgrade this extension to newer
 * version in the future please contact to CENTURY BUSINESS SOLUTIONS.
 *
 * @category    Ebizcharge
 * @package     Ebizcharge_Ebizcharge
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Logger;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Monolog\DateTimeImmutable;
use Monolog\Logger;

/**
 * EbizCharge Logger class to log
 *
 * Class EbizchargeLogger
 */
class EbizchargeLogger extends Logger
{
    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $_scopeConfig;

    /**
     * @param string $name
     * @param ScopeConfigInterface $scopeConfig
     * @param array $handlers
     * @param array $processors
     */
    public function __construct(
        string $name,
        ScopeConfigInterface $scopeConfig,
        array $handlers = [],
        array $processors = []
    ) {
        /** @var _scopeConfig */
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($name, $handlers, $processors);
    }

    /**
     * Handling the Logger Records
     *
     * @param int $level
     * @param string $message
     * @param array $context
     * @param DateTimeImmutable|null $datetime
     * @return bool
     */
    public function addRecord(
        int               $level,
        string            $message,
        array             $context = [],
        DateTimeImmutable $datetime = null
    ): bool {
        $isLogsEnabled = $this->_isLogsEnabled();
        /** if $logEnabled add record to file */
        if ($isLogsEnabled) {
            return parent::addRecord($level, $message, $context);
        }
        return false;
    }

    /**
     * Check is Logs Enabled via System Configuration
     *
     * @return bool
     */
    protected function _isLogsEnabled(): bool
    {
        /** @var $isLogsEnabled */
        $isLogsEnabled = false;
        /**
         * Ebizcharge Gateway is Active
         */
        $ebizchargeEnabled = (int)$this->_scopeConfig->getValue(
            EbizchargeHandler::SYSTEM_CONFIG_EBIZCHARGE_ACTIVE,
            ScopeInterface::SCOPE_STORE
        );
        /**
         * Logs Enabled @ System Config
         */
        $logsEnabled = (int)$this->_scopeConfig->getValue(
            EbizchargeHandler::SYSTEM_CONFIG_ENABLE_LOGS,
            ScopeInterface::SCOPE_STORE
        );

        /** check if Gateway and logs are enabled */
        return $ebizchargeEnabled && $logsEnabled;
    }

    /**
     * Add Error message
     *
     * @param mixed $message
     * @param mixed $level
     * @param array $context
     * @return bool
     */
    public function addError($message, $level = 100, array $context = [])
    {
        $isLogsEnabled = $this->_isLogsEnabled();
        /** if $logEnabled add record to file */
        if ($isLogsEnabled) {
            return parent::addRecord((int)$level, (string)$message, $context);
        }
        return false;
    }

    /**
     * Add Info message
     *
     * @param mixed $message
     * @param int $level
     * @param array $context
     * @return bool
     */
    public function addInfo($message, $level = 100, array $context = [])
    {
        $isLogsEnabled = $this->_isLogsEnabled();
        /** if $logEnabled add record to file */
        if ($isLogsEnabled) {
            return parent::addRecord((int)$level, (string)$message, $context);
        }
        return false;
    }

    /**
     * Add Critical message
     *
     * @param mixed $message
     * @param int $level
     * @param array $context
     * @return bool
     */
    public function addCritical($message, $level = 100, array $context = [])
    {
        $isLogsEnabled = $this->_isLogsEnabled();
        /** if $logEnabled add record to file */
        if ($isLogsEnabled) {
            return parent::addRecord((int)$level, (string)$message, $context);
        }
        return false;
    }
    /**
     * Add Debug
     *
     * @param mixed $message
     * @param int $level
     * @param array $context
     * @return bool
     */
    public function addDebug($message, $level = 100, array $context = [])
    {
        $isLogsEnabled = $this->_isLogsEnabled();
        /** if $logEnabled add record to file */
        if ($isLogsEnabled) {
            return parent::addRecord((int)$level, (string)$message, $context);
        }
        return false;
    }
}

<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Model;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class for log management
 */
class Logger extends \I95DevConnect\MessageQueue\Model\Logger
{
    public const PUSHDATA = 'PushData';
    public const PUSHRESPONSE = 'PushResponse';
    public const PULLRESPONSE = 'PullResponse';
    public const PULLDATA = 'PullData';
    public const PULLRESPONSEACK = 'PullResponseAck';
    public const GENERIC = 'Generic';
    public const EXCEPTION = 'I95DevCloudException';
    public const CLEANDATA = 'I95DevCloudCleanData';
    public const CLEANDATAEXCEPTION = 'I95DevCloudCleanDataException';
    public const CRITICAL = 'critical';
    public const INFO = 'info';

    /**
     * @var string
     */
    public $path = "/var/log/i95dev/cloud/";

    /**
     *
     * @param File $ioOperations
     * @param DateTime $date
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     */
    public function __construct(
        File $ioOperations,
        DateTime $date,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem\Driver\File $fileDriver
    ) {
        $this->ioOperations = $ioOperations;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->fileDriver = $fileDriver;
        parent::__construct($ioOperations, $date, $scopeConfig, $storeManager, $fileDriver);
    }

    /**
     * Get log file type/name
     *
     * @param string $schedulerType
     * @param string $entity
     * @return string
     */
    public function getEntityLogType($schedulerType, $entity = null)
    {
        if ($schedulerType) {
            $schedulerType = strtoupper($schedulerType);
            $logType = constant("self::{$schedulerType}");
        } else {
            return $entity . Logger::GENERIC;
        }

        return $entity . $logType;
    }

    /**
     * Create cloud log folder
     *
     * @param type $date
     * @throws Exception
     */
    public function createLogFolder($date = null)
    {
        try {
            parent::createLogFolder($date);
            if ($date) {
                $this->ioOperations->checkAndCreateFolder(BP . "/var/log/i95dev/cloud/" . date('Y-m-d'), 0777);
            } else {
                $this->ioOperations->checkAndCreateFolder(BP . "/var/log/i95dev/cloud", 0777);
            }
        } catch (LocalizedException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Create log using MessageQueue
     *
     * @param string $logArea
     * @param string $message
     * @param string $logName
     * @param string $logType
     * @throws LocalizedException
     */
    public function createLog($logArea, $message, $logName, $logType)
    {
        if ($this->isLogsEnabled()) {
            parent::createLog($logArea, $message, $logName, $logType);
        }
    }

    /**
     * Get cloud connector status
     *
     * @return boolean
     */
    public function isLogsEnabled()
    {
        return $this->scopeConfig->getValue(
            'i95dev_adapter_configurations/enabled_disabled/logs_enabled',
            ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getDefaultStoreView()->getWebsiteId()
        );
    }
}

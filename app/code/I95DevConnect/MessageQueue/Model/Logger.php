<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model;

use Exception;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Log_Exception;
use Zend_Log_Writer_Stream;

/**
 * Class for log creation
 */
class Logger implements LoggerInterface
{
    public const MAX_LOG_SIZE = 5000;

    /**
     * @var object
     */
    public $logger;

    /**
     * @var object
     */
    public $zendLogger;

    /**
     * @var object
     */
    public $fileStream;

    /**
     * @var File
     */
    public $ioOperations;

    /**
     * @var string
     */
    public $path = "/var/log/i95dev/";

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $fileDriver;

    /**
     *
     * @var DateTime
     */
    public $date;
    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;
    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

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
        $this->date = $date;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->fileDriver = $fileDriver;
    }

    /**
     * Creates log folder
     *
     * @param boolean $date
     *
     * @throws Exception
     */
    public function createLogFolder($date = null)
    {
        if ($date) {
            // reverted code for log creation using magento framework
            $this->ioOperations->checkAndCreateFolder(BP . $this->path . date('Y-m-d'), 0777);
        } else {
            // reverted code for log creation using magento framework
            $this->ioOperations->checkAndCreateFolder(BP . $this->path, 0777);
        }
    }

    /**
     * Get Absolute path of the log file to be created.
     *
     * @param string $logName
     *
     * @return string $logPath
     * @throws LocalizedException
     * @throws Exception
     */
    public function getLogPath($logName)
    {
        try {
            if ($logName == 'general') {
                $this->createLogFolder(false);
                $logPath = BP . $this->path . $logName;
                return $this->recurciveFileCheck(1, $logPath);
            } else {
                $this->createLogFolder(true);
                $todayDate = $this->date->gmtDate();
                $dateObj = new \DateTime($todayDate);
                $logPath = BP . $this->path . $dateObj->format('Y-m-d') . '/' . $logName;
                return $this->recurciveFileCheck(1, $logPath);
            }
        } catch (LocalizedException $ex) {
            throw new LocalizedException(__($ex->getMessage()));
        }
    }

    /**
     * Create log
     *
     * @param string $logArea
     * @param string $message
     * @param string $logName
     * @param string $logType
     * @throws LocalizedException
     * @throws Zend_Log_Exception
     */
    public function createLog($logArea, $message, $logName, $logType)
    {
        try {
            //@author Divya Koona. Checking logs configuration enabled or not.
            $logsEnabled = $this->scopeConfig->getValue(
                'i95dev_messagequeue/I95DevConnect_logsettings/debug',
                ScopeInterface::SCOPE_WEBSITE,
                $this->storeManager->getStore()->getWebsiteId()
            );
            if (!$logsEnabled) {
                return;
            }

            $logsTypeEnabled = $this->scopeConfig->getValue(
                'i95dev_messagequeue/I95DevConnect_logsettings/logtype',
                ScopeInterface::SCOPE_WEBSITE,
                $this->storeManager->getStore()->getWebsiteId()
            );
            $logsTypeArray = explode(",", $logsTypeEnabled);

            // to prevent multiple logging
            $this->logger = new \Zend_Log();
            $logPath = $this->getLogPath($logName);

            $writer = new Zend_Log_Writer_Stream($logPath, 'a', null, 0777);
            $this->logger->addWriter($writer);

            if (is_array($message) || is_object($message)) {
                $message = json_encode($message, JSON_UNESCAPED_UNICODE);
            }

            switch ($logType) {
                case "info":
                    if (in_array("info", $logsTypeArray)) {
                        $this->logger->info($logArea);
                        $this->logger->info($message);
                    }
                    break;
                case "critical":
                    if (in_array("critical", $logsTypeArray)) {
                        $this->logger->crit($logArea);
                        $this->logger->crit($message);
                    }
                    break;
                case "error":
                    if (in_array("error", $logsTypeArray)) {
                        $this->logger->err($logArea);
                        $this->logger->err($message);
                    }
                    break;
                default:
                    if (in_array("debug", $logsTypeArray)) {
                        $this->logger->debug($logArea);
                        $this->logger->debug($message);
                    }
            }
        } catch (LocalizedException | Exception $ex) {
            throw new LocalizedException(__($ex->getMessage()));
        }
    }

    /**
     * Recursively check the file already exists or not.
     * If exist will check if it reached to maximum size.
     * If it has reached to max size it will create a new file with one increment count.
     *
     * @param int $count
     * @param string $logPath
     *
     * @throws LocalizedException
     * @return string $logPath
     */
    public function recurciveFileCheck($count, $logPath)
    {
        $checkPath = $logPath . '_' . $count . '.log';
        try {
            if ($this->fileDriver->isExists($checkPath)) {
                /* @updatedBy Ranjith Rasakatla, get file size change as earlier
                 * used file function popen() was taking more memory, processing
                 * and file was not closed after the functionality utilization */
                // phpcs:disable
                $fsize = filesize($checkPath);
                // phpcs:enable
                $maxConfigFileSize = $this->scopeConfig->getValue(
                    'i95dev_messagequeue/I95DevConnect_logsettings/max_log_size',
                    ScopeInterface::SCOPE_WEBSITE,
                    $this->storeManager->getStore()->getWebsiteId()
                );

                /* @updatedBy Ranjith Rasakatla, converting size KB to B for comparison */
                $maxFileSize = ((isset($maxConfigFileSize) && trim($maxConfigFileSize)) ?
                        $maxConfigFileSize : self::MAX_LOG_SIZE) * 1024;
                if ($fsize > $maxFileSize) {
                    $logPath = $this->recurciveFileCheck($count + 1, $logPath);
                    if ($logPath) {
                        return $logPath;
                    }
                } else {
                    return $checkPath;
                }
            } else {
                return $checkPath;
            }
        } catch (LocalizedException $ex) {
            throw new LocalizedException(__($ex->getMessage()));
        }
    }
}

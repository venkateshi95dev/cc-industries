<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model;

use Exception;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

/**
 * Class responsible for cleaning logs and message queue data
 */
class DataClean
{
    public const LOG_CLEAN_DAYS = 30;
    public const I95DEV = 'i95dev';
    public const CLOUD = 'cloud';
    public const EXCEP = 'i95devException';

    /**
     *
     * @var DirectoryList
     */
    public $directoryList;

    /**
     *
     * @var DateTime
     */
    public $date;

    /**
     *
     * @var Data
     */
    public $msgHelper;

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var File
     */
    protected $fileDriver;

    /**
     *
     * @param DirectoryList $directoryList
     * @param DateTime $date
     * @param Data $msgHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param File $fileDriver
     * @param LoggerInterface $logger
     */
    public function __construct(
        DirectoryList $directoryList,
        DateTime $date,
        Data $msgHelper,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        File $fileDriver,
        LoggerInterface $logger
    ) {
        $this->directoryList = $directoryList;
        $this->date = $date;
        $this->msgHelper = $msgHelper;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->fileDriver = $fileDriver;
    }

    /**
     * Cleans all old data, like old logs, old message queue data
     *
     * @return void
     * @throws Exception
     * @throws Exception
     */
    public function cleanData()
    {
        $this->logClean();
        $this->cleanMQData();
        $this->cleanMagentoMQData();
    }

    /**
     * Cleans old logs
     *
     * @return void
     * @throws LocalizedException
     * @throws Exception
     * @author Divya Koona. Updated code to clean logs before configured days
     * and zip last day log before configured days
     */
    public function logClean()
    {
        $storeCode = "corvette_central_store";
        $path = $this->directoryList->getPath('log');
        $days = $this->scopeConfig->getValue(
            'i95dev_messagequeue/I95DevConnect_logsettings/log_clean_days',
            ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getStore($storeCode)->getWebsiteId()
        );

        $zipDays = $this->scopeConfig->getValue(
            'i95dev_messagequeue/I95DevConnect_logsettings/log_zip_days',
            ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getStore($storeCode)->getWebsiteId()
        );

        if (!trim($days)) {
            $days = self::LOG_CLEAN_DAYS;
        }

        $toDeleteDate = $this->getLastDateFromDays($days);
        $toZipDate = $this->getLastDateFromDays($zipDays);

        $isCloudEnabled = $this->scopeConfig->getValue(
            'i95dev_adapter_configurations/enabled_disabled/enabled',
            ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getStore($storeCode)->getWebsiteId()
        );

        try {
            if ($isCloudEnabled) {
                $dir = $path . DS . self::I95DEV . DS . self::CLOUD;
            } else {
                $dir = $path . DS . self::I95DEV;
            }
            // phpcs:disable
            $cDir = scandir($dir);
            // phpcs:enable

            $this->archiveLogDirectory($cDir, $toZipDate, $isCloudEnabled, $path);

            // phpcs:disable
            $currentDir = scandir($dir);
            // phpcs:enable

            $this->deleteLogDirectory($currentDir, $toDeleteDate, $isCloudEnabled, $path);
        } catch (LocalizedException $ex) {
            $this->logger->createLog(__METHOD__, $ex->getMessage(), self::EXCEP, LoggerInterface::CRITICAL);
        }
    }

    /**
     * Delete log directory
     *
     * @param string $cdir
     * @param string $toDeleteDate
     * @param bool $isCloudEnabled
     * @param string $path
     */
    public function deleteLogDirectory($cdir, $toDeleteDate, $isCloudEnabled, $path)
    {
        if (!empty($cdir)) {
            foreach ($cdir as $value) {
                if (!in_array($value, [".", ".."]) && $value < $toDeleteDate) {
                    $this->deleteLogFolders($isCloudEnabled, $path, $value);
                }
            }
        }
    }

    /**
     * Delete log folders and files
     *
     * @param bool $isCloudEnabled
     * @param string $path
     * @param string $value
     * @return void
     */
    public function deleteLogFolders($isCloudEnabled, $path, $value)
    {
        if ($isCloudEnabled) {
            $logCleaner = $path . DS . self::I95DEV . DS . self::CLOUD . DS . $value;
        } else {
            $logCleaner = $path . DS . self::I95DEV . DS . $value;
        }
        if ($this->fileDriver->isDirectory($logCleaner)) {
            $this->deleteDirectory($logCleaner);
        }
        if ($this->fileDriver->isExists($logCleaner)) {
            $this->fileDriver->deleteFile($logCleaner);
        }
    }

    /**
     * Archive log directory
     *
     * @param string $cdir
     * @param string $toZipDate
     * @param bool $isCloudEnabled
     * @param string $path
     */
    public function archiveLogDirectory($cdir, $toZipDate, $isCloudEnabled, $path)
    {
        if (!empty($cdir)) {
            foreach ($cdir as $value) {
                if (!in_array($value, [".", ".."]) && $value < $toZipDate) {
                    if ($isCloudEnabled) {
                        $logArchive = $path . DS . self::I95DEV . DS . self::CLOUD . DS . $value;
                    } else {
                        $logArchive = $path . DS . self::I95DEV . DS . $value;
                    }
                    $this->compressLog($logArchive);
                }
            }
        }
    }

    /**
     * Compress previous day log file.
     *
     * @param string $filePath
     */
    public function compressLog($filePath)
    {
        try {
            if ($this->fileDriver->isDirectory($filePath)) {
                if (!class_exists('\ZipArchive')) {
                    $this->logger->createLog(
                        __METHOD__,
                        __('ZipArchive class not found'),
                        self::EXCEP,
                        LoggerInterface::CRITICAL
                    );
                }
                // phpcs:disable
                chdir($filePath);
                // phpcs:enable
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($filePath),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );
                $zip = new ZipArchive();
                $zip->open($filePath . '.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
                foreach ($files as $file) {
                    if (!$file->isDir()) {
                        $realPath = $file->getRealPath();
                        $relativePath = substr($realPath, strlen($filePath) + 1);
                        $zip->addFile($realPath, $relativePath);
                        $zip->setCompressionName($relativePath, ZipArchive::CM_DEFLATE, 9); // apply max compression
                    }
                }
                $zip->close();
                $this->deleteDirectory($filePath);
            }
        } catch (LocalizedException $ex) {
            $this->logger->createLog(__METHOD__, $ex->getMessage(), self::EXCEP, LoggerInterface::CRITICAL);
        }
    }

    /**
     * Delete directory
     *
     * @param string $dir
     * @return bool
     */
    public function deleteDirectory($dir)
    {
        if ($this->dirExists($dir) && $this->isDirectory($dir)) {
            // phpcs:disable
            foreach (scandir($dir) as $item) {
                // phpcs:enable
                if ($item == '.' || $item == '..') {
                    continue;
                }

                if (!$this->deleteDirectory($dir . DS . $item)) {
                    return false;
                }
            }

            return $this->fileDriver->deleteDirectory($dir);
        }
        return true;
    }

    /**
     * Checks if if given directory is exists or not
     *
     * @param string $dir
     * @return boolean
     * @throws FileSystemException
     */
    private function dirExists($dir)
    {
        if ($this->fileDriver->isExists($dir)) {
            return true;
        }

        return false;
    }

    /**
     * Checks if the given path is a directory or not
     *
     * @param string $dir
     * @return boolean
     */
    private function isDirectory($dir)
    {
        if (!$this->fileDriver->isDirectory($dir)) {
            $this->fileDriver->deleteFile($dir);
            return false;
        }

        return true;
    }

    /**
     * Clean messagequeue data with status complete and error with limit 5
     *
     * @return boolean
     */
    public function cleanMQData()
    {
        try {
            $syncEntities = $this->msgHelper->getEntityTypeList();

            if (!empty($syncEntities)) {
                //@author Divya Koona. Sending entity code instead of entity name to clean MQ data
                foreach ($syncEntities as $entityCode => $entityName) {
                    $status = $this->msgHelper->deleteMQData($entityCode);
                    if (!$status) {
                        $this->logger->createLog(
                            __METHOD__,
                            "An error occured while deleting records from MQ for entity " . $entityCode,
                            self::EXCEP,
                            LoggerInterface::CRITICAL
                        );
                    }
                }
            }
        } catch (LocalizedException|Exception $ex) {
            $this->logger->createLog(__METHOD__, $ex->getMessage(), self::EXCEP, LoggerInterface::CRITICAL);
        }
        return true;
    }

    /**
     * Clean messagequeue data with status complete and error with limit 5
     *
     * @return boolean
     */
    public function cleanMagentoMQData()
    {
        try {
            $syncEntities = $this->msgHelper->getEntityTypeList();
            if (!empty($syncEntities)) {
                //@author Divya Koona. Sending entity code instead of entity name to clean MQ data
                foreach ($syncEntities as $entityCode => $entityName) {
                    $status = $this->msgHelper->deleteMMQData($entityCode);
                    if (!$status) {
                        $this->logger->createLog(
                            __METHOD__,
                            "An error occured while deleting records from MQ for entity " . $entityCode,
                            self::EXCEP,
                            LoggerInterface::CRITICAL
                        );
                    }
                }
            }
        } catch (LocalizedException|Exception $ex) {
            $this->logger->createLog(__METHOD__, $ex->getMessage(), self::EXCEP, LoggerInterface::CRITICAL);
        }
        return true;
    }

    /**
     * Get last date by days provided from today
     *
     * @param int $days
     * @return string
     * @throws Exception
     */
    public function getLastDateFromDays($days)
    {
        $todayDate = $this->date->gmtDate();
        $dateObj = new \DateTime($todayDate);
        $dateObj->modify('-' . $days . ' day');
        return $dateObj->format('Y-m-d');
    }
}

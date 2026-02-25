<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Model;

use DateTime;
use Exception;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Helper\Data;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Model class for MQ Data and Logs Clean
 */
class DataClean
{
    public const MAX_DAYS = '7';

    /**
     *
     * @var DirectoryList
     */
    public $directoryList;

    /**
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
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
     * @var File
     */
    protected $fileDriver;

    /**
     * @var DriverInterface
     */
    public $driverSystem;

    /**
     * @param DirectoryList $directoryList
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param Data $msgHelper
     * @param DriverInterface $driverSystem
     * @param LoggerInterface $logger
     * @param File $fileDriver
     */
    public function __construct(
        DirectoryList $directoryList,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        Data $msgHelper,
        DriverInterface $driverSystem,
        LoggerInterface $logger,
        File $fileDriver
    ) {
        $this->directoryList = $directoryList;
        $this->date = $date;
        $this->msgHelper = $msgHelper;
        $this->logger = $logger;
        $this->driverSystem = $driverSystem;
        $this->fileDriver = $fileDriver;
    }

    /**
     * Method to get no. of days to clean
     *
     * @return int
     */
    public function getLogCleanDays()
    {
        $days = $this->msgHelper->scopeConfig->getValue(
            'i95dev_messagequeue/I95DevConnect_logsettings/log_clean_days',
            ScopeInterface::SCOPE_WEBSITE,
            $this->msgHelper->storeManager->getDefaultStoreView()->getWebsiteId()
        );

        return ($days ? $days : self::MAX_DAYS);
    }

    /**
     * Method to clean log files
     *
     * @throws LocalizedException
     * @throws Exception
     */
    public function logClean()
    {
        $path = $this->directoryList->getPath('log');
        $todayDate = $this->date->gmtDate();
        $days = $this->getLogCleanDays();
        $dateObj = new DateTime($todayDate);
        $dateObj->modify('-' . $days . ' day');
        $toDeleteDate = $dateObj->format('Y-m-d');

        try {
            for ($i = $days; $i > 0; $i--) {
                $dateObj->modify('-1 day');
                $toDeleteDate = $dateObj->format('Y-m-d');

                $logCleaner = $path . DS . 'i95dev' . DS . 'cloud' . DS . $toDeleteDate;
                if ($this->driverSystem->isDirectory($logCleaner)) {
                    $this->deleteDirectory($logCleaner);
                }
            }
        } catch (LocalizedException $ex) {
            throw new LocalizedException(__($ex->getMessage()));
        }
    }

    /**
     * Method to delete empty directory
     *
     * @param string $dir
     * @return boolean
     * @throws FileSystemException
     */
    public function deleteDirectory($dir)
    {
        if ($this->dirExists($dir) && $this->isDirectory($dir)) {
            // phpcs:disable
            foreach (scandir($dir) as $item) {
                //phpcs:enable
                if ($item == '.' || $item == '..') {
                    continue;
                }

                if (!$this->deleteDirectory($dir . DS . $item)) {
                    return false;
                }
            }

            return $this->driverSystem->deleteDirectory($dir);
        }
        return true;
    }

    /**
     * Method to check directory exist or not
     *
     * @param string $dir
     * @return boolean
     */
    public function dirExists($dir)
    {
        if ($this->fileDriver->isExists($dir)) {
            return true;
        }

        return false;
    }

    /**
     * Method to check directory and remove if it is not
     *
     * @param string $dir
     * @return boolean
     * @throws FileSystemException
     */
    public function isDirectory($dir)
    {
        if (!$this->driverSystem->isDirectory($dir)) {
            // phpcs:disable
            unlink($dir);
            // phpcs:enable
            return false;
        }
        return true;
    }
}

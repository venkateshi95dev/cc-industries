<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Test\Integration\DataClean;

/**
 * Test case for Data Clean
 */
class CancelDataTest extends \PHPUnit\Framework\TestCase
{
    public const SUCCESS = 1;
    public const ERROR = 0;
    public const DS = '/';
    public const DATE_FORMATE = "Y-m-d";
    public const PATH = 'i95dev/cloud';
    public const DATE = '2019-08-11 07:35:17';

    /**
     * @author Hrusikesh Manna
     */
    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->date = $objectManager->create(
            \Magento\Framework\Stdlib\DateTime\DateTime::class
        );
        $this->i95devServerRepo = $objectManager->create(
            \I95DevConnect\MessageQueue\Model\DataClean::class
        );
        $this->scopeConfig = $objectManager->create(
            \Magento\Framework\App\Config\ScopeConfigInterface::class
        );
        $this->erpMessageQueue = $objectManager->create(
            \I95DevConnect\MessageQueue\Model\I95DevErpMQRepositoryFactory::class
        );
        $this->magentoMessageQueue = $objectManager->create(
            \I95DevConnect\MessageQueue\Model\I95DevMagMQRepositoryFactory::class
        );
        $this->i95devServerRepoMq = $objectManager->create(
            \I95DevConnect\I95DevServer\Model\I95DevServerRepository::class
        );
        $this->directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
    }

    /**
     * Test case for clean data
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_logsettings/log_clean_days 90
     * @author               Hrusikesh Manna
     */
    public function testCleanData()
    {
        $this->createInboundMqData();
        $this->createInboundMqDataRecentLog();
        $this->createOutboundMqData();
        $mqId = $this->createRecentOutboundMqData();
        $this->createLog();
        $this->i95devServerRepo->cleanData();
        $isFolderExist = $this->checkLogDirectory();
        $this->assertEquals(self::SUCCESS, $isFolderExist, "Issue Came With Log Clean");
        $erpMqData = $this->checkERPMQdata();
        $this->assertEquals(self::SUCCESS, $erpMqData, "Issue Came With ERP MessageQue Data Clean");
        $erpMqRecentData = $this->checkERPMQrecentDatadata();
        $this->assertEquals(self::SUCCESS, $erpMqRecentData, "Deleted Recent ERP Logs");
        $mageMqData = $this->checkMMQdata();
        $this->assertEquals(self::SUCCESS, $mageMqData, "Issue Came With Magento MessageQue Data Clean");
        $mmqRecentlog = $this->checkMMQrecentdata($mqId);
        $this->assertEquals(self::SUCCESS, $mmqRecentlog, "Deleted Recent Magento Logs");
        $recentLog = $this->checkRecentLog();
        $this->assertEquals(self::SUCCESS, $recentLog, "Deleted Recent Logs");
    }

    /**
     * Check log exist or not after clean data
     *
     * @return boolean
     * @throws \Exception
     * @author Hrusieksh Manna
     */
    public function checkLogDirectory()
    {
        $path = $this->getLogPath();
        $todayDate = $this->date->gmtDate();
        $days = 90;
        $dateObj = new \DateTime($todayDate);
        $dateObj->modify('-' . $days . ' day');
        $dateObj->format(self::DATE_FORMATE);
        $isDir = [];
        for ($i = $days; $i > 0; $i--) {
            $dateObj->modify('-1 day');
            $toDeleteDate = $dateObj->format(self::DATE_FORMATE);
            $logCleaner = $path . self::DS . self::PATH . self::DS . $toDeleteDate;
            if (is_dir($logCleaner)) {
                $isDir[] = $i;
            }
        }
        if (!empty($isDir)) {
            $status = false;
        } else {
            $status = true;
        }
        return $status;
    }

    /**
     * Create log before data clean
     *
     * @author Hrusikesh Manna
     */
    public function createLog()
    {
        $path = $this->getLogPath();
        $todayDate = $this->date->gmtDate();
        $days = 90;
        $dateObj = new \DateTime($todayDate);
        $dateObj->modify('-' . $days . ' day');
        $toDeleteDate = $dateObj->format(self::DATE_FORMATE);
        $lastDayLog = $path . self::DS . self::PATH . self::DS . $toDeleteDate;
        if (!file_exists($lastDayLog) && !is_dir($lastDayLog)) {
            mkdir($lastDayLog, 0777, true);
        }
        for ($i = $days; $i > 0; $i--) {
            $dateObj->modify('-1 day');
            $toDeleteDate = $dateObj->format(self::DATE_FORMATE);
            $logDir = $path . self::DS . self::PATH . self::DS . $toDeleteDate;
            if (!file_exists($logDir) && !is_dir($logDir)) {
                mkdir($logDir, 0777, true);
            }
            file_put_contents($logDir . '/test.txt', 'Data Clean Testing');
        }
        file_put_contents($logDir . '.txt', 'Data Clean Testing For Non Directory');

        // Code For Zip
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($logDir),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        $zip = new \ZipArchive();
        $zip->open($logDir . '.zip', \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $realPath = $file->getRealPath();
                $relativePath = substr($realPath, strlen($realPath) + 1);
                $zip->addFile($realPath, $relativePath);
            }
        }

        $zip->close();
        // End
        $dateObj = new \DateTime($todayDate);
        $dateObj->modify('-' . 1 . ' day');
        $toDeleteDate1 = $dateObj->format(self::DATE_FORMATE);
        $logDir = $path . self::DS . self::PATH . self::DS . $toDeleteDate1;
        if (!file_exists($logDir) && !is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
    }

    /**
     * Get log file path during Execution
     *
     * @return string
     * @author Hrusikesh Manna
     */
    public function getLogPath()
    {
        return $this->directory->getPath("log");
    }

    /**
     * Check ERP message que date before data clean
     *
     * @return boolean
     * @author Hrusikesh Manna
     */
    public function checkERPMQdata()
    {
        $configWeek = 30;
        $oneWeekBefore = date('Y-m-d H:i:s', strtotime("-" . $configWeek . " day"));
        $successRecord = $this->erpMessageQueue->create()->getCollection()
            ->addFieldtoFilter('updated_dt', ['to' => $oneWeekBefore])
            ->addFieldtoFilter('status', ['IN' => 2, 3, 4, 5]);
        $data = $successRecord->getData();
        if (!empty($data)) {
            $status = false;
        } else {
            $status = true;
        }
        return $status;
    }

    /**
     * Check Magento message que data before data clean
     *
     * @return boolean
     * @author Hrusikesh Manna
     */
    public function checkMMQdata()
    {
        $configWeek = 30;
        $oneWeekBefore = date('Y-m-d H:i:s', strtotime("-" . $configWeek . " day"));
        $successRecord = $this->magentoMessageQueue->create()->getCollection()
            ->addFieldtoFilter('updated_dt', ['to' => $oneWeekBefore])
            ->addFieldtoFilter('status', ['IN' => 2, 3, 4, 5]);
        $data = $successRecord->getData();
        if (!empty($data)) {
            $status = false;
        } else {
            $status = true;
        }
        return $status;
    }

    /**
     * Check recent log file after data clean
     *
     * @return boolean
     * @throws \Exception
     * @author Hrusikesh Manna
     */
    public function checkRecentLog()
    {
        $path = $this->getLogPath();
        $todayDate = $this->date->gmtDate();
        $dateObj = new \DateTime($todayDate);
        $dateObj->modify('-' . 1 . ' day');
        $toDeleteDate1 = $dateObj->format(self::DATE_FORMATE);
        $logDir = $path . self::DS . self::PATH . self::DS . $toDeleteDate1;
        if (is_dir($logDir)) {
            rmdir($logDir);
            $status = true;
        } else {
            $status = false;
        }
        return $status;
    }

    /**
     * Create inbound message que data for testing
     *
     * @author Hrusikesh Manna
     */
    public function createInboundMqData()
    {
        $filePath = realpath(dirname(__FILE__)) . "/json/ShipmentPullData.json";
        $shipmentJsonData = file_get_contents($filePath);
        $this->i95devServerRepoMq->serviceMethod("createShipmentList", $shipmentJsonData);
        $record = $this->erpMessageQueue->create()->getCollection()
            ->addFieldToFilter('ref_name', 1027)
            ->getData();

        $dataRec = $this->erpMessageQueue->create()->get($record[0]['msg_id']);
        $dataRec->setStatus(5);
        $dataRec->setCreatedDt(self::DATE);
        $dataRec->setUpdatedDt(self::DATE);
        $dataRec->save();
    }
    /**
     * Check ERP message que recent data after data clean
     *
     * @return boolean
     * @author Hrusikesh Manna
     */

    public function checkERPMQrecentDatadata()
    {
        $data = $this->erpMessageQueue->create()->getCollection()
            ->addFieldToFilter('ref_name', 1028)
            ->getData();

        if (!empty($data)) {
            $status = true;
        } else {
            $status = false;
        }
        return $status;
    }

    /**
     * Create inbound message que recent data
     *
     * @author Hrusikesh Manna
     */
    public function createInboundMqDataRecentLog()
    {
        $filePath = realpath(dirname(__FILE__)) . "/json/ShipmentPullDataRecent.json";
        $shipmentJsonData = file_get_contents($filePath);
        $this->i95devServerRepoMq->serviceMethod("createShipmentList", $shipmentJsonData);
    }

    /**
     * Create out bound message que data
     *
     * @author Hrusikesh Manna
     */
    public function createOutboundMqData()
    {
        $obMq = $this->magentoMessageQueue->create();
        $obMq->setErpCode('NAV');
        $obMq->setEntityCode('product');
        $obMq->setCreatedDt(self::DATE);
        $obMq->setUpdatedDt(self::DATE);
        $obMq->setStatus(5);
        $obMq->setUpdatedBy('Magento');
        $obMq->save();
    }

    /**
     * Create out bound message que recent data
     *
     * @return int
     * @author Hrusikesh Manna
     */
    public function createRecentOutboundMqData()
    {
        $obMq = $this->magentoMessageQueue->create();
        $obMq->setErpCode('NAV');
        $obMq->setEntityCode('product');
        $obMq->setCreatedDt('2019-09-10 07:35:17');
        $obMq->setUpdatedDt('2019-09-10 07:35:17');
        $obMq->setStatus(1);
        $obMq->setUpdatedBy('Magento');
        $obMq->save();
        return $obMq->getId();
    }

    /**
     * Check Magento message que recent log after data clean
     *
     * @param  type $mqId
     * @return boolean
     * @author Hrusieksh Manna
     */
    public function checkMMQrecentdata($mqId)
    {
        $dataRec = $this->magentoMessageQueue->create()->get($mqId)->getData();
        if (!empty($dataRec)) {
            $status = true;
        } else {
            $status =  false;
        }
        return $status;
    }
}

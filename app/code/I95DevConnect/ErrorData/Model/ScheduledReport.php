<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_ErrorData
 */

namespace I95DevConnect\ErrorData\Model;

use I95DevConnect\ErrorData\Helper\Generic;
use I95DevConnect\MessageQueue\Helper\Data as MqDataHelper;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\Manager;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Filesystem\DriverInterface;

/*
 * Model Class for Sending the Error Report to the Customer
 * @author Ranjith R
*/

/**
 * Class ScheduledReport for sending scheduled report.
 */
class ScheduledReport
{

    public const MAGENTO = "magento";
    public const NOTIFICATIONSENT = "notification_sent";
    public const MSGID = "msg_id";
    public const TARGETID = "target_id";

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    public $directory_list;

    /**
     * @var ErrorDataFactory
     */
    public $errorData;

    /**
     * @var Email
     */
    public $email;

    /**
     * @var ErrorMessageDataFactory
     */
    public $errorMsgData;

    /**
     * @var Generic
     */
    public $genericHelper;

    /**
     * @var \I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory
     */
    public $logger;

    /**
     * @var string[]
     */
    public $header = [
        'Message Id',
        'Entity',
        'Magento Id',
        'ERP Id',
        'Reference',
        'Created Date',
        'Updated Date',
        'Error Message'
    ];

    /**
     * @var null
     */
    public $filePointer = null;

    /**
     * @var array
     */
    public $notificationList = [];

    /**
     * @var array
     */
    public $notificationMsgList = [];

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    public $driverInterface;

    /**
     * @var MqDataHelper
     */
    public $mqHelper;

    /**
     * @var Manager
     */
    public $moduleManager;

    /**
     *
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directory_list
     * @param \I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory $logger
     * @param \I95DevConnect\ErrorData\Model\ErrorDataFactory $errorData
     * @param \I95DevConnect\ErrorData\Model\ErrorMessageDataFactory $errorMsgData
     * @param Generic $genericHelper
     * @param \I95DevConnect\ErrorData\Model\Email $email
     * @param \Magento\Framework\Filesystem\Driver\File $driverInterface
     * @param MqDataHelper $mqHelper
     * @param Manager $moduleManager
     */
    public function __construct(//NOSONAR
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        \I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory $logger,
        \I95DevConnect\ErrorData\Model\ErrorDataFactory $errorData,
        \I95DevConnect\ErrorData\Model\ErrorMessageDataFactory $errorMsgData,
        Generic $genericHelper,
        \I95DevConnect\ErrorData\Model\Email $email,
        \Magento\Framework\Filesystem\Driver\File $driverInterface,
        MqDataHelper $mqHelper,
        Manager $moduleManager
    ) {
        $this->directory_list = $directory_list;
        $this->logger = $logger;
        $this->errorData = $errorData;
        $this->errorMsgData = $errorMsgData;
        $this->genericHelper = $genericHelper;
        $this->email = $email;
        $this->driverInterface = $driverInterface;
        $this->mqHelper = $mqHelper;
        $this->moduleManager = $moduleManager;
    }

    /**
     * Get the list of errors and Send report to the customer
     *
     * @author Ranjith R
     */
    public function sendReport()
    {
        try {
            if ($this->genericHelper->isEnabled()) {
                $isReportEnabled = $this->mqHelper->scopeConfig->getValue(
                    'i95devconnect_errors/reports_enabled_settings/report',
                    ScopeInterface::SCOPE_WEBSITE,
                    $this->mqHelper->storeManager->getDefaultStoreView()->getWebsiteId()
                );
                $isModEnable = $this->moduleManager->isEnabled('I95DevConnect_ErrorData');
                $reportType = $this->genericHelper->getReportType();
                if ($isReportEnabled && $isModEnable && $reportType == "Schedule") {
                    $this->sendErpToMagentoErrors();
                }
            }
        } catch (\Exception $ex) {
            $this->logger->create()->createLog(__METHOD__, $ex->getMessage(), Generic::I95REPORT, Generic::CRITICAL);
        }
    }

    /**
     * Send ERP to eCommerce syncing error report in mail at schedule time.
     *
     * @param string $flow
     */
    public function sendErpToMagentoErrors($flow)
    {
        $entities = $this->genericHelper->getEnabledEntities($flow);
        if (empty($entities)) {
            return;
        }

        $directoryName = "ErrorReport";
        foreach ($entities as $entityCode) {
            $mqErrorList = $this->getEntityWiseErrorRecords($flow, $entityCode);

            if (!empty($mqErrorList)) {
                $fileName = $flow . $entityCode .  date('Y-m-d') . '.csv';

                $msgList = $this->prepareMqErrListByType($mqErrorList);

                $mailDetails = $this->genericHelper->getContactDetails();
                if (isset($msgList["unknown"])) {
                    $mailDetails['reciever_email'] = $this->genericHelper->getI95DevSupportEmail();
                    $this->initiateEmailSending(
                        $msgList["unknown"],
                        $fileName,
                        $directoryName,
                        "unknown",
                        $entityCode,
                        $flow,
                        $mailDetails
                    );
                }
                if (isset($msgList["known"])) {
                    $this->initiateEmailSending(
                        $msgList["known"],
                        $fileName,
                        $directoryName,
                        "Known",
                        $entityCode,
                        $flow,
                        $mailDetails
                    );
                }
            }
        }
    }

    /**
     * Prepare mq error list by type
     *
     * @param array $mqErrorList
     * @return array
     */
    public function prepareMqErrListByType($mqErrorList)
    {
        $msgList = [];
        foreach ($mqErrorList as $errorList) {
            if ($errorList["code"] == 107) {
                $msgList["unknown"][] = $errorList;
            } else {
                $msgList["known"][] = $errorList;
            }
        }

        return $msgList;
    }

    /**
     * Initiate email sending
     *
     * @param array $msgList
     * @param string $fileName
     * @param string $directoryName
     * @param string $errType
     * @param string $entityCode
     * @param string $flow
     * @param string $mailDetails
     */
    public function initiateEmailSending(
        $msgList,
        $fileName,
        $directoryName,
        $errType,
        $entityCode,
        $flow,
        $mailDetails
    ) {
        $titleERP2M = ($flow == "reverse") ?
            "List of $entityCode records has failed to synchronize from ERP to eCommerce" :
            "List of $entityCode records has failed to synchronize from eCommerce to ERP";

        $this->writeFile(
            $this->header,
            $titleERP2M,
            $directoryName,
            $msgList,
            $errType . $fileName
        );

        $message = $this->getMessage($entityCode, $msgList, $flow);
        $this->sendEmail(
            $directoryName,
            $entityCode,
            $message,
            $flow,
            $mailDetails,
            $errType
        );

        $this->updateNotification($flow);
        $fileToDelete = $directoryName . DS . $errType . $fileName;
        $this->deleteReportFile($fileToDelete);
    }

    /**
     * Get Message Queue Records which has errors
     *
     * @param string $origin
     * @return array $mqList
     * @author Ranjith R
     */
    public function getMQErrorRecords($origin)
    {
        try {
            $this->notificationMsgList[$origin] = [];
            $errorLogRec = $this->errorData->create()->getCollection()
                ->addFieldToFilter(self::NOTIFICATIONSENT, [['neq' => 1], ['null' => true]])
                ->addFieldToFilter('origin', $origin)
                ->getData();
            if (empty($errorLogRec)) {
                return $this->notificationMsgList[$origin];
            }

            foreach ($errorLogRec as $errorLogRecData) {
                $errorMsgList = $this->errorMsgData->create()->getCollection()
                    ->addFieldToFilter("notification_id", $errorLogRecData['id'])
                    ->getData();
                if (empty($errorMsgList)) {
                    continue;
                }
                $this->notificationList[$origin][] = $errorLogRecData[self::MSGID];
                $this->getMessageList($errorLogRecData, $errorMsgList, $origin);
            }
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(__METHOD__, $ex->getMessage(), Generic::I95REPORT, Generic::CRITICAL);
        }

        return $this->notificationMsgList[$origin];
    }

    /**
     * Prepare entity wise error report.
     *
     * @param string $flow
     * @param string $entityCode
     * @return array
     */
    public function getEntityWiseErrorRecords($flow, $entityCode)
    {
        try {
            $origin = ($flow == "reverse") ? "erp" : "magento";
            $this->notificationMsgList[$origin] = [];
            $errorLogRec = $this->errorData->create()->getCollection()
                ->addFieldToFilter(self::NOTIFICATIONSENT, [['neq' => 1], ['null' => true]])
                ->addFieldToFilter('origin', $origin)
                ->addFieldToFilter('entity_code', $entityCode)
                ->getData();
            if (empty($errorLogRec)) {
                return $this->notificationMsgList[$origin];
            }

            foreach ($errorLogRec as $errorLogRecData) {
                $errorMsgList = $this->errorMsgData->create()->getCollection()
                    ->addFieldToFilter("notification_id", $errorLogRecData['id'])
                    ->getData();
                if (empty($errorMsgList)) {
                    continue;
                }
                $this->notificationList[$origin][] = $errorLogRecData[self::MSGID];
                $this->getMessageList($errorLogRecData, $errorMsgList, $origin);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $ex) {
            $this->logger->create()->createLog(__METHOD__, $ex->getMessage(), Generic::I95REPORT, Generic::CRITICAL);
        }

        return $this->notificationMsgList[$origin];
    }

    /**
     * Get the list of error messages
     *
     * @param array $errorLogRecData
     * @param array $errorMsgList
     * @param string $origin
     * @author Ranjith R
     */
    public function getMessageList($errorLogRecData, $errorMsgList, $origin)
    {
        try {
            if ($origin == "erp") {
                $mqData = $this->genericHelper->getMQData($errorLogRecData[self::MSGID], "reverse");
            } else {
                $mqData = $this->genericHelper->getMQData($errorLogRecData[self::MSGID], "forward");
            }

            $this->notificationList[$origin] = [];
            if ($mqData) {
                foreach ($errorMsgList as $errorMsgData) {
                    $this->notificationMsgList[$origin][] = [
                        self::MSGID => $mqData->getMsgId(),
                        "entity_code" => $mqData->getEntityCode(),
                        "magento_id" => $mqData->getMagentoId(),
                        self::TARGETID => $mqData->getTargetId(),
                        "reference" =>$mqData->getRefName(),
                        "created_at" => $errorLogRecData['created_at'],
                        "updated_at" => $errorLogRecData['updated_at'],
                        "message" => $errorMsgData['message'],
                        "code" => $errorMsgData['code']
                    ];
                    $this->notificationList[$origin][] = $mqData->getMsgId();
                }
            }
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(__METHOD__, $ex->getMessage(), Generic::I95REPORT, Generic::CRITICAL);
        }
    }

    /**
     * Write the error data to the file
     *
     * @param string $header
     * @param string $title
     * @param string $directoryName
     * @param array $mqErrorList
     * @param string $fileName
     *
     * @author Ranjith R
     */
    public function writeFile($header, $title, $directoryName, $mqErrorList, $fileName)
    {
        try {
            if (!$this->filePointer && !empty($mqErrorList)) {
                $this->filePointer = $this->getFile($directoryName, $fileName);
            }

            if ($this->filePointer && !empty($mqErrorList)) {
                DriverInterface::fputcsv($this->filePointer, [$title], ",");
                DriverInterface::fputcsv($this->filePointer, $header, ",");
                foreach ($mqErrorList as $data) {
                    unset($data["code"]);
                    DriverInterface::fputcsv($this->filePointer, $data, ",");
                }
                DriverInterface::fputcsv($this->filePointer, [""], ",");
            }
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(__METHOD__, $ex->getMessage(), Generic::I95REPORT, Generic::CRITICAL);
        }
    }

    /**
     * Create a file for writing the error data
     *
     * @param string $name
     * @param string $fileName
     *
     * @return resource|null
     * @author Ranjith R
     */
    public function getFile($name, $fileName)
    {
        try {
            $directory = $this->directory_list->getPath('var') . DS . "log" . DS . $name;
            if (!$this->driverInterface->isDirectory($directory)) {
                $this->driverInterface->createDirectory($directory, 0755);
            }
            return $this->driverInterface->fileOpen($directory . DS . $fileName, 'w+');
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(__METHOD__, $ex->getMessage(), Generic::I95REPORT, Generic::CRITICAL);
            return null;
        }
    }

    /**
     * Send error report as an email to customer
     *
     * @param string $directoryName
     * @param string $entityCode
     * @param array $mqErrorList
     * @param string $flow
     * @param string $mailDetails
     * @param string $type
     * @return bool
     */
    public function sendEmail($directoryName, $entityCode, $mqErrorList, $flow, $mailDetails, $type)
    {
        try {
            if (empty($this->notificationList)) {
                throw new \Magento\Framework\Exception\LocalizedException('notificationList empty');
            }

            if ($this->filePointer) {
                $directory = $this->directory_list->getPath('var') . DS . "log" . DS . $directoryName;
                $this->driverInterface->fileClose($this->filePointer);
                $file = $directory . DS . $type . $flow . $entityCode.  date('Y-m-d') . '.csv';
                $template = 'i95devconnect_error_report';
                $subject = "i95Dev Notification System | ";

                if ($flow == "reverse") {
                    $subject .= "Error in $entityCode Sync from ERP to eCommerce";
                } elseif ($flow == "forward") {
                    $subject .= "Error in $entityCode Sync from eCommerce to ERP";
                }

                $this->email->sendEmail($file, $mailDetails, $template, $subject, $mqErrorList);
                $this->filePointer = null;
                return true;
            }
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(__METHOD__, $ex->getMessage(), Generic::I95REPORT, Generic::CRITICAL);
            return false;
        }
        return false;
    }

    /**
     * Get the message body for the email
     *
     * @param string $entityCode
     * @param array $mqErrorList
     * @param string $flow
     *
     * @return string $message
     * @author Ranjith R
     */
    public function getMessage($entityCode, $mqErrorList, $flow)
    {
        if ($flow == "reverse") {
            $origin = "erp";
            $message = "<br/><p>There are errors in $entityCode Sync from ERP to eCommerce.";
        } elseif ($flow == "forward") {
            $origin = "magento";
            $message = "<br/><p>There are errors in $entityCode Sync from eCommerce to ERP.";
        }
        switch ($entityCode) {
            case 'address':
                $message .= "<br/>The following $entityCode ID's of respective Customer has error :</p>";
                break;
            case 'inventory':
                $message .= ($flow == "reverse") ?
                    "<br/>Inventory sync for the following SKU's has error :</p>" :
                    "<br/>Inventory sync for the following product magento id has error :</p>";
                break;
            case 'product':
                $message .= ($flow == "reverse") ?
                    "<br/>The following product SKU's has error :</p>" :
                    "<br/>The following product magento id has error :</p>";
                break;
            default:
                $message .= "<br/>The following $entityCode ID's has error :</p>";
        }

        $i = 1;
        $errorIds = [];
        $track_field_name = ($flow == "reverse") ? "target_id" : "magento_id";

        $this->notificationList = [];
        foreach ($mqErrorList as $error) {
            if (in_array($error[$track_field_name], $errorIds)) {
                continue;
            }
            array_push($errorIds, $error[$track_field_name]);
            if ($entityCode === 'address') {
                $message .= "<br>($i) " . "Address Id:: "
                    . $error[$track_field_name]. " Customer Id:: ". $error['reference'];
            } else {
                $message .= "<br>($i) " . $error[$track_field_name];
            }

            $this->notificationList[$origin][] = $error["msg_id"];
            $i++;
        }

        return $message .= "<br/><br/>The details of these Errors are attached.";
    }

    /**
     * Update the notification list as sen
     *
     * @param bool $flow
     * @return bool|null
     */
    public function updateNotification($flow)
    {
        try {
            $origin = ($flow == "reverse") ? "erp" : "magento";
            $notificationData = $this->notificationMsgList[$origin];
            $notificationData = array_chunk($notificationData, 10, true);

            foreach ($notificationData as $chunkNotificationData) {
                $msgIdList = [];
                foreach ($chunkNotificationData as $data) {
                    $msgIdList[] = $data["msg_id"];
                }

                if (isset($msgIdList) && !empty($msgIdList)) {
                    $condition = "`msg_id` IN ('".implode("','", $msgIdList)."') 
                    AND `origin` = '" . $origin . "'";
                    $this->errorData->create()->getCollection()
                        ->setTableRecords(
                            $condition,
                            [self::NOTIFICATIONSENT => 1]
                        );
                }
            }
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(__METHOD__, $ex->getMessage(), Generic::I95REPORT, Generic::CRITICAL);
        }
        return true;
    }

    /**
     * Delete report file after successful mail sent.
     *
     * @param string $filePath
     */
    public function deleteReportFile($filePath)
    {
        try {
            $path = $this->directory_list->getPath('var') . DS . "log" . DS . $filePath;
            $this->driverInterface->deleteFile($path);
        } catch (FileSystemException $ex) {
            $this->logger->create()->createLog(__METHOD__, $ex->getMessage(), Generic::I95REPORT, Generic::CRITICAL);
        }
    }
}

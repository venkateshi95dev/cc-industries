<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_ErrorData
 */

namespace I95DevConnect\ErrorData\Helper;

/*
 * Generic Helper Class for Sending the Error Report to the Customer
 * @author Ranjith R
*/

use Exception;
use I95DevConnect\ErrorData\Model\ErrorDataFactory;
use I95DevConnect\ErrorData\Model\ErrorMessageDataFactory;
use I95DevConnect\MessageQueue\Api\I95DevErpMQRepositoryInterface;
use I95DevConnect\MessageQueue\Api\I95DevErpMQRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Api\I95DevMagMQRepositoryInterface;
use I95DevConnect\MessageQueue\Api\I95DevMagMQRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Generic Helper for error reporting
 */
class Generic
{
    public const XML_PATH_ENABLED = 'i95devconnect_errors/reports_enabled_settings/report';
    public const I95REPORT = "i95devErrorReport";
    public const CRITICAL = "critical";
    public const REPORT_TYPE = 'i95devconnect_errors/reports_enabled_settings/report_type';
    public const REPORT_ENTITIES = 'i95devconnect_errors/reports_enabled_settings/report_entities';
    public const OUTBOUND_REPORT_ENTITIES = 'i95devconnect_errors/reports_enabled_settings/outbound_report_entities';

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var I95DevErpMQRepositoryInterface
     */
    public $i95DevErpMQRepository;

    /**
     * @var I95DevMagMQRepositoryInterface
     */
    public $I95DevMagMQ;

    /**
     * @var FileResolverInterface
     */
    public $fileResolver;

    /**
     * @var ErrorDataFactory
     */
    public $errorData;

    /**
     * @var ErrorMessageDataFactory
     */
    public $errorMsgData;

    /**
     * @var DateTime
     */
    public $date;

    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     *
     * @param LoggerInterfaceFactory $logger
     * @param I95DevErpMQRepositoryInterfaceFactory $i95DevErpMQRepository
     * @param I95DevMagMQRepositoryInterfaceFactory $I95DevMagMQ
     * @param ScopeConfigInterface $scopeConfig
     * @param FileResolverInterface $fileResolver
     * @param ErrorDataFactory $errorData
     * @param ErrorMessageDataFactory $errorMsgData
     * @param DateTime $date
     */
    public function __construct( // NOSONAR
        LoggerInterfaceFactory $logger,
        I95DevErpMQRepositoryInterfaceFactory $i95DevErpMQRepository,
        I95DevMagMQRepositoryInterfaceFactory $I95DevMagMQ,
        ScopeConfigInterface $scopeConfig,
        FileResolverInterface $fileResolver,
        ErrorDataFactory $errorData,
        ErrorMessageDataFactory $errorMsgData,
        DateTime $date
    ) {
        $this->logger = $logger;
        $this->i95DevErpMQRepository = $i95DevErpMQRepository;
        $this->I95DevMagMQ = $I95DevMagMQ;
        $this->scopeConfig = $scopeConfig;
        $this->fileResolver = $fileResolver;
        $this->errorData = $errorData;
        $this->errorMsgData = $errorMsgData;
        $this->date = $date;
    }

    /**
     * Check if module is enable
     *
     * @return string
     * @author Ranjith R
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if module is enable
     *
     * @return string
     * @author Ranjith R
     */
    public function getReportType()
    {
        return $this->scopeConfig->getValue(
            self::REPORT_TYPE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get the inbound message queue record
     *
     * @param int $msgId
     * @param string $flow
     * @return null|I95DevConnect\MessageQueue\Api\I95DevErpMQ
     * @author Ranjith R
     */
    public function getMQData($msgId, $flow)
    {
        try {
            if ($flow == "reverse") {
                return $this->i95DevErpMQRepository->create()->load($msgId);
            } elseif ($flow == "forward") {
                return $this->I95DevMagMQ->create()->load($msgId);
            }
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(__METHOD__, $ex->getMessage(), self::I95REPORT, self::CRITICAL);
            return null;
        }
    }

    /**
     * Get the contact details to whom the error notification has to be sent
     *
     * @return array
     * @author Ranjith R
     */
    public function getContactDetails()
    {
        try {
            $fromEmail = $this->scopeConfig->getValue(
                'trans_email/ident_general/email',
                ScopeInterface::SCOPE_STORE
            );
            $from = $this->scopeConfig->getValue(
                'trans_email/ident_general/name',
                ScopeInterface::SCOPE_STORE
            );
            $recieverEmail = $this->scopeConfig->getValue(
                'i95dev_messagequeue/I95DevConnect_generalcontact/email_sent'
            );
            $reciever = $this->scopeConfig->getValue('i95dev_messagequeue/I95DevConnect_generalcontact/username');

            $cc = $this->scopeConfig->getValue(
                'i95dev_messagequeue/I95DevConnect_generalcontact/email_cc',
                ScopeInterface::SCOPE_STORE
            );
            $ccList = [];
            if ($cc) {
                $ccList = explode(",", $cc);
            }

            return [
                'from_email' => $fromEmail,
                'from_name' => $from,
                'reciever_email' => $recieverEmail,
                'reciever_name' => $reciever,
                'cc' => $ccList
            ];
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(__METHOD__, $ex->getMessage(), self::I95REPORT, self::CRITICAL);
            return [];
        }
    }

    /**
     * Return all enabled entities for which error report will be sent.
     *
     * @param array $flow
     * @author Debashis S. Gopal
     */
    public function getEnabledEntities($flow)
    {
        if ($flow == "reverse") {
            $entities = $this->scopeConfig->getValue(
                self::REPORT_ENTITIES,
                ScopeInterface::SCOPE_STORE
            );
        } elseif ($flow == "forward") {
            $entities = $this->scopeConfig->getValue(
                self::OUTBOUND_REPORT_ENTITIES,
                ScopeInterface::SCOPE_STORE
            );
        }

        return explode(",", $entities);
    }

    /**
     * Generic function for getting supported product types
     *
     * @param $entity
     *
     * @return array
     * @createdBy Arushi Bansal
     */
    public function getI95DevSupportEmail()
    {
        try {
            $xmlData = $this->fileResolver->get("settings.xml", 'global');
            if (count($xmlData) > 0) {
                foreach ($xmlData as $content) {
                    $xml = simplexml_load_string($content);
                    $currentEntity = json_decode(json_encode((array)$xml), 1);
                    if (isset($currentEntity['email']["supportemail"])) {
                        return $currentEntity['email']["supportemail"];
                    }
                }
            }
        } catch (LocalizedException $ex) {
            $this->logger->createLog(
                __METHOD__,
                $ex->getMessage(),
                LoggerInterface::I95EXC,
                LoggerInterface::CRITICAL
            );
        }
        return null;
    }

    /**
     * Create the errors generated while validation under the error notification and message models
     *
     * @param int $msgId
     * @param string $message
     * @param string $entityCode
     * @param string $code
     * @param string $origin
     * @author Ranjith R
     */
    public function updateErrorData($msgId, $message, $entityCode, $code = 107, $origin = "erp")
    {
        try {
            if ($message) {
                $messageList = explode(";", $message);
                $errorDataModelCollection = $this->errorData->create()
                ->getCollection()
                ->addFieldToFilter("msg_id", $msgId);
                $errorDataSize = $errorDataModelCollection->getSize();
                $errorDataModel = $this->errorData->create();
                if ($errorDataSize) {
                    foreach ($errorDataModelCollection as $errorData) {
                        $errorDataModel->load($errorData->getId());
                    }
                }
                $errorDataModel->setMsgId($msgId);
                $errorDataModel->setOrigin($origin);
                $errorDataModel->setCreatedAt($this->date->gmtDate());
                $errorDataModel->setUpdatedAt($this->date->gmtDate());
                $errorDataModel->setEntityCode($entityCode);
                $errorDataModel->save();
                $errorId = $errorDataModel->getId();
                foreach ($messageList as $msg) {
                    $errorMsgDataModel = $this->errorMsgData->create();
                    $errorMsgDataModel->setNotificationId($errorId);
                    $errorMsgDataModel->setMessage($msg);
                    $code = ($code == 0) ? 107 : $code;
                    $errorMsgDataModel->setCode($code);
                    $errorMsgDataModel->save();
                }
            }
        } catch (Exception $ex) {
            $this->logger->create()->createLog(__METHOD__, $ex->getMessage(), self::I95REPORT, self::CRITICAL);
        }
    }

    /**
     * Delete the existing error message data of the respective message queue id
     *
     * @param array $errorLogRec
     * @param string $message
     * @param string $status
     *
     * @return bool
     * @author Ranjith R
     */
    public function deleteErrorMessageData($errorLogRec, $message, $status)
    {
        try {
            if (!$message && $status == Data::PROCESSING) {
                return false;
            }
            foreach ($errorLogRec as $errorLogRecData) {
                $errorMsgList = $this->errorMsgData->create()
                    ->getCollection()
                    ->addFieldToFilter("notification_id", $errorLogRecData['id']);
                $errorSize = $errorMsgList->getSize();
                if ($errorSize) {
                    foreach ($errorMsgList as $errorMsgData) {
                        $errorMsgLog = $this->errorMsgData->create();
                        $errorMsgLog->load($errorMsgData->getId());
                        $errorMsgLog->delete();
                    }
                }

                $errorLog = $this->errorData->create();
                $errorLog->load($errorLogRecData['id']);
                $errorLog->delete();
            }
        } catch (Exception $ex) {
            $this->logger->create()->createLog(__METHOD__, $ex->getMessage(), self::I95REPORT, self::CRITICAL);
        }
        return true;
    }
}

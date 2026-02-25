<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Model\ServiceMethod\ForwardSync\MQToErp;

use I95DevConnect\I95DevServer\Model\ServiceMethod\AbstractServiceMethod;
use I95DevConnect\MessageQueue\Api\Data\I95DevMagMQInterfaceFactory;
use I95DevConnect\MessageQueue\Api\DataPersistenceInterface;
use I95DevConnect\MessageQueue\Api\I95DevMagMQRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data as MessageQueueHelper;
use I95DevConnect\MessageQueue\Model\ErrorUpdateDataFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class to send entity info/data to ERP constructor
 */
class SendEntityData
{
    /**
     * @var DataPersistenceInterface
     */
    public $dataPersistence;
    /**
     * @var AbstractServiceMethod
     */
    public $abstractService;
    /**
     * @var I95DevMagMQInterfaceFactory
     */
    public $I95DevMagMQFactory;
    public const MESSAGEID = "messageId";
    public const REFERENCE = "reference";

    /**
     * @var object \I95DevConnect\MessageQueue\Model\ErrorUpdateDataFactory
     */
    public $messageErrorModel;

    /**
     * @var I95DevMagMQRepositoryInterfaceFactory
     */
    public $i95DevMagMQRepository;

    /**
     * @var SendIds
     */
    public $sendId;

    /**
     * @var LoggerInterfaceFactory
     */
    public $logger;

    /**
     * @param I95DevMagMQRepositoryInterfaceFactory $i95DevMagMQRepository
     * @param DataPersistenceInterface              $dataPersistence
     * @param SendIds                               $sendId
     * @param AbstractServiceMethod                 $abstractService
     * @param I95DevMagMQInterfaceFactory           $I95DevMagMQFactory
     * @param LoggerInterfaceFactory                $logger
     * @param ErrorUpdateDataFactory                $messageErrorModel
     */
    public function __construct(
        I95DevMagMQRepositoryInterfaceFactory $i95DevMagMQRepository,
        DataPersistenceInterface $dataPersistence,
        SendIds $sendId,
        AbstractServiceMethod $abstractService,
        I95DevMagMQInterfaceFactory $I95DevMagMQFactory,
        LoggerInterfaceFactory $logger,
        ErrorUpdateDataFactory $messageErrorModel
    ) {
        $this->i95DevMagMQRepository = $i95DevMagMQRepository;
        $this->dataPersistence = $dataPersistence;
        $this->sendId = $sendId;
        $this->abstractService = $abstractService;
        $this->I95DevMagMQFactory = $I95DevMagMQFactory;
        $this->logger = $logger;
        $this->messageErrorModel = $messageErrorModel;
    }

    /**
     * Method to update record status in outbound MQ
     *
     * @param  string $messageId
     * @param  string $status
     * @param  string $errorMessage
     * @param  string $errorCode
     * @return void
     */
    public function saveRecord($messageId, $status, $errorMessage = '', $errorCode = 107)
    {
        try {
            $I95DevMagMQ = $this->I95DevMagMQFactory->create();

            if ($errorMessage != '') {
                $message = is_array($errorMessage) ? implode(",", $errorMessage) : $errorMessage;
                $errorDataModel = $this->messageErrorModel->create();
                $errorDataModel->setMsg($message);
                $errorDataModel->setCode($errorCode);
                $errorDataModel->save();
                $errorId = $errorDataModel->getId();
                $I95DevMagMQ->setErrorId($errorId);
                $this->logger->create()->createLog(
                    __METHOD__,
                    $message,
                    LoggerInterface::I95EXC,
                    'error'
                );
            }

            $I95DevMagMQ->setMsgId($messageId);
            $I95DevMagMQ->setStatus($status);
            $this->i95DevMagMQRepository->create()->saveMQData($I95DevMagMQ);
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(
                __METHOD__,
                $ex->getMessage(),
                LoggerInterface::I95EXC,
                'error'
            );
        }
    }

    /**
     * Get the entity data
     *
     * @param  string $entityCode
     * @param  array  $dataString
     * @param  string $erpName
     * @return array
     * @throws LocalizedException
     */
    public function getEntityData($entityCode, $dataString, $erpName = null)
    {
        $responseData = [];
        foreach ($dataString as $recordRequest) {
            $magentoid = '';
            $messageId = '';
            $reference = null;
            $messageId = $this->setRecordStatusProcessing($recordRequest, $messageId);

            if (isset($recordRequest['magentoId']) && isset($messageId)) {
                $magentoid = $recordRequest['magentoId'];
                $recordData = null;

                $recordMessage = "";
                $processData = $this->processGetEntityInfo($entityCode, $magentoid, $erpName, $messageId);
                $recordData = $processData["recordData"];
                $recordstatus = $processData["recordstatus"];
                $recordMessage = $processData["recordMessage"];
                $errorCode = $processData["errorCode"];
                $reference = $processData[self::REFERENCE];
            } else {
                $recordstatus = false;
                $recordMessage = "Some issue occur. Please contact admin";
            }

            $record['result'] = $recordstatus;
            $record['message'] = $recordMessage;
            if ($messageId !== '') {
                $record[self::MESSAGEID] = $messageId;
                /**
 * @updatedBy Sravani Polu Code starts for updating Outbound MQ record status
**/
                $this->setRecordStatusErrorOrSuccess($messageId, $recordstatus, $recordMessage, $errorCode);
                /**
* Code Ends for updating Outbound MQ record status
**/
            }
            if ($magentoid !== '') {
                $record['sourceId'] = $magentoid;
                $record[self::REFERENCE] = $reference;
            }
            $record['InputData'] = $this->abstractService->encryptAES(json_encode($recordData, JSON_UNESCAPED_UNICODE));
            if ($record['result']) {
                $responseData[] = $record;
            }
        }
        return [
            "status" => true,
            "message" => "",
            "responseData" => $responseData
        ];
    }

    /**
     * Set record status processing
     *
     * @param  array  $recordRequest
     * @param  string $messageId
     * @return mixed|string
     */
    public function setRecordStatusProcessing($recordRequest, $messageId)
    {
        if (isset($recordRequest[self::MESSAGEID])) {
            /**
             * @updatedBy Sravani Polu Added processing status as method parameter
             **/
            $this->saveRecord($messageId, MessageQueueHelper::PROCESSING);
            return $messageId = $recordRequest[self::MESSAGEID];
        }
        return "";
    }

    /**
     * Set record status error or success
     *
     * @param  string $messageId
     * @param  string $recordstatus
     * @param  string $recordMessage
     * @param  string $errorCode
     * @return void
     */
    public function setRecordStatusErrorOrSuccess($messageId, $recordstatus, $recordMessage, $errorCode = 107)
    {
        /**
         * @updatedBy Sravani Polu Code starts for updating Outbound MQ record status
        **/
        if ($recordstatus) {
            $this->saveRecord($messageId, MessageQueueHelper::SUCCESS);
        } else {
            $this->saveRecord($messageId, MessageQueueHelper::ERROR, $recordMessage, $errorCode);
        }
        /**
        * Code Ends for updating Outbound MQ record status
        **/
    }

    /**
     * Process get entity info
     *
     * @param  string $entityCode
     * @param  int    $magentoid
     * @param  string $erpName
     * @param  string $messageId
     * @return array
     */
    public function processGetEntityInfo($entityCode, $magentoid, $erpName, $messageId)
    {
        $errorCode = 0;
        $recordstatus = true;
        $recordMessage = "";
        $reference = null;
        $recordData = [];
        try {
            $recordData = $this->dataPersistence->getEntityInfo(
                $entityCode,
                $magentoid,
                $erpName,
                $messageId
            );

            if (!is_array($recordData)) {
                $recordstatus = false;
                $recordMessage = $recordData;
                $errorCode = 107;
            } else {
                $reference = array_key_exists(self::REFERENCE, $recordData) ? $recordData[self::REFERENCE] : '';
            }
        } catch (LocalizedException $ex) {
            $recordstatus = false;
            $recordMessage = $ex->getMessage();
            $errorCode = $ex->getCode();
        }

        return [
            "recordData" => $recordData,
            "recordstatus" => $recordstatus,
            "recordMessage" => $recordMessage,
            "reference" => $reference,
            "errorCode" => $errorCode
        ];
    }
}

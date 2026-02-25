<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Model\ServiceMethod\ForwardSync\MQToErp;

use Exception;
use I95DevConnect\MessageQueue\Api\Data\I95DevMagMQInterfaceFactory;
use I95DevConnect\MessageQueue\Api\DataPersistenceInterface;
use I95DevConnect\MessageQueue\Api\I95DevMagMQRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\ErrorUpdateDataFactory;
use I95DevConnect\MessageQueue\Model\Logger;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class to Save Entity Response in Outbound MQ
 */
class SendEntityResponse
{
    public const MSG = "message";
    public const TARGETID = "targetId";
    public const SOURCEID = "sourceId";
    public const MSGID = "messageId";

    /**
     * @var I95DevMagMQRepositoryInterfaceFactory
     */
    public $i95DevMagMQRepository;
    /**
     * @var DataPersistenceInterface
     */
    public $dataPersistence;
    /**
     * @var string
     */
    public $statusCode = '5';
    /**
     * @var string
     */
    public $updatedBy = 'ERP';
    /**
     * @var I95DevMagMQInterfaceFactory
     */
    public $I95DevMagMQDataFactory;
    /**
     * @var ErrorUpdateDataFactory
     */
    public $messageErrorModel;
    /**
     * @var Logger
     */
    public $logger;
    /**
     * @var $messageId
     */
    public $messageId;
    /**
     * @var $targetId
     */
    public $targetId;

    /**
     * SendEntityResponse constructor.
     *
     * @param DataPersistenceInterface              $dataPersistence
     * @param I95DevMagMQRepositoryInterfaceFactory $i95DevMagMQRepository
     * @param ErrorUpdateDataFactory                $messageErrorModel
     * @param I95DevMagMQInterfaceFactory           $I95DevMagMQDataFactory
     * @param Logger                                $logger
     */
    public function __construct(
        DataPersistenceInterface $dataPersistence,
        I95DevMagMQRepositoryInterfaceFactory $i95DevMagMQRepository,
        ErrorUpdateDataFactory $messageErrorModel,
        I95DevMagMQInterfaceFactory $I95DevMagMQDataFactory,
        Logger $logger
    ) {
        $this->dataPersistence = $dataPersistence;
        $this->i95DevMagMQRepository = $i95DevMagMQRepository;
        $this->messageErrorModel = $messageErrorModel;
        $this->I95DevMagMQDataFactory = $I95DevMagMQDataFactory;
        $this->logger = $logger;
    }

    /**
     * Get the entity data
     *
     * @param string $entityCode
     * @param array  $dataString
     * @param string $erpName
     *
     * @return array
     * @throws Exception
     */
    public function getEntityResponse($entityCode, $dataString, $erpName = null) //NOSONAR
    {
        try {
            $responseData = [];
            $recordStatus = false;
            $recordMessage = "";

            foreach ($dataString as $recordRequest) {
                if (isset($recordRequest[self::MSGID])) {
                    $this->messageId = $recordRequest[self::MSGID];
                    $messageData = $this->i95DevMagMQRepository->create()->load($recordRequest[self::MSGID]);

                    $output = $this->prepareRecordStatusNMessage(
                        $messageData,
                        $entityCode,
                        $recordRequest,
                        $erpName
                    );
                    $recordStatus = $output['recordstatus'];
                    $recordMessage = $output['recordMessage'];
                    $errorCode = $output['errorCode'];
                } else {
                    $recordStatus = false;
                    $record[self::MSG] = "MessageId is mandatory";
                    $errorCode = 104;
                }

                /* updatedBy Ranjith; messageId, targetId, sourceId need to be
                set by default irrespective of record sync status */
                $record[self::MSGID] = isset($recordRequest[self::MSGID]) ? $recordRequest[self::MSGID] : null;
                $record[self::TARGETID] = isset($recordRequest[self::TARGETID]) ? $recordRequest[self::TARGETID] : null;
                $record[self::SOURCEID] = isset($recordRequest[self::SOURCEID]) ? $recordRequest[self::SOURCEID] : null;
                $record['result'] = $recordStatus;
                $record[self::MSG] = isset($recordMessage) ? $recordMessage : null;
                if (isset($recordRequest['resultData'])) {
                    $record['inputData'] = $recordRequest['resultData'];
                }

                if (!$recordStatus) {
                    $this->updateMessageQueue(
                        $messageData['msg_id'],
                        Data::ERROR,
                        $recordMessage,
                        isset($errorCode) ? $errorCode : 107
                    );
                }
                $responseData[] = $record;
            }
        } catch (LocalizedException $ex) {
            $this->logger->createLog(
                __METHOD__,
                $ex->getMessage(),
                LoggerInterface::I95EXC,
                'critical'
            );
        }

        return [
            "status" => true,
            self::MSG => "",
            "responseData" => $responseData,
        ];
    }

    /**
     * Prepare record status message
     *
     * @param  object $messageData
     * @param  string $entityCode
     * @param  array  $recordRequest
     * @param  string $erpName
     * @return array
     */
    public function prepareRecordStatusNMessage($messageData, $entityCode, $recordRequest, $erpName)
    {
        $errorCode = 0;
        if (empty($messageData->getData())) {
            $recordstatus = false;
            $recordMessage = "No such message id exist";
            $errorCode = 108;
        } else {
            if (isset($messageData['entity_code']) && $messageData['entity_code'] != $entityCode) {
                $recordstatus = false;
                $recordMessage = "Either Message id or entity code is wrong";
                $errorCode = 104;
            } else {
                if (!isset($recordRequest[self::TARGETID]) || $recordRequest[self::TARGETID] == "") {
                    $recordstatus = false;
                    $recordMessage = "No target Id exist because " . $recordRequest[self::MSG];
                    $errorCode = 108;
                } else {
                    $this->targetId = $recordRequest[self::TARGETID];
                    $recordstatus = true;
                    $recordData = $this->dataPersistence->getEntityResponse(
                        $entityCode,
                        json_encode($recordRequest),
                        $erpName
                    );
                    $data = $this->setRecordMsgnCode($recordData, $messageData);
                    $recordstatus = $data["recordstatus"];
                    $recordMessage = $data["recordMessage"];
                    $errorCode = $data["errorCode"];
                }
            }
        }
        return ["recordstatus" => $recordstatus, "recordMessage" => $recordMessage, "errorCode" => $errorCode];
    }

    /**
     * Set record msg code
     *
     * @param object $recordData
     * @param array $messageData
     * @return array
     */
    public function setRecordMsgnCode($recordData, $messageData)
    {
        $errorCode = 0;
        $recordstatus = true;
        $recordMessage = null;
        switch ($recordData->getStatus()) {
            case Data::SUCCESS:
                if ($messageData['destination_msg_id'] > 0) {
                    $this->statusCode = 6;
                }
                $this->saveDataInOutboundMQ();
                $recordMessage = "Erp response set successfully";
                break;
            case Data::ERROR:
                $recordstatus = false;
                $recordMessage = $recordData->getMessage();
                $errorCode = $recordData->getCode();
                break;
            default:
                $recordstatus = false;
                $recordMessage = "Something went wrong. Please contact I95Dev team.";
                $errorCode = 107;
        }

        return [
            "recordstatus" => $recordstatus,
            "recordMessage" => $recordMessage,
            "errorCode" => $errorCode
        ];
    }

    /**
     * Update message queue
     *
     * @param  string $msgId
     * @param  string $status
     * @param  string $message
     * @param  string $code
     * @return void
     * @throws Exception
     */
    public function updateMessageQueue($msgId, $status, $message = null, $code = 107)
    {
        $messageData = $this->I95DevMagMQDataFactory->create();
        $messageData->setMsgId($msgId);
        if ($status !== null) {
            $messageData->setStatus($status);
        }
        if ($message !== null) {
            $errMsgId = $this->updateErrorData($message, $code);
            $messageData->setErrorId($errMsgId);
        }

        $this->i95DevMagMQRepository->create()->saveMQData($messageData);
    }

    /**
     * Update error data
     *
     * @param  string $message
     * @param  string $errorCode
     * @return mixed
     */
    public function updateErrorData($message, $errorCode)
    {
        return $this->dataPersistence->updateErrorData($message, $errorCode);
    }

    /**
     * Save data in outbound MQ
     *
     * @return void
     */
    public function saveDataInOutboundMQ()
    {
        $I95DevMagMQData = $this->I95DevMagMQDataFactory->create();
        $I95DevMagMQData->setMsgId($this->messageId);
        $I95DevMagMQData->setStatus($this->statusCode);
        $I95DevMagMQData->setUpdatedby(__($this->updatedBy));
        $I95DevMagMQData->setTargetId($this->targetId);
        $this->i95DevMagMQRepository->create()->saveMQData($I95DevMagMQData);
    }
}

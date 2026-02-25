<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Model\ServiceMethod\ReverseSync\ErpToMQ;

use I95DevConnect\I95DevServer\Model\ServiceMethod\ReverseSync;
use I95DevConnect\MessageQueue\Api\Data\I95DevErpMQInterfaceFactory;
use I95DevConnect\MessageQueue\Api\DataPersistenceInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\I95DevErpMQRepositoryFactory;
use I95DevConnect\MessageQueue\Model\Logger;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class to get reverse entity from ERP and save in to Inbound MQ
 */
class Generic
{
    public const DESTINATIONID = "DestinationId";
    /**
     * @var Logger
     */
    public $logger;
    /**
     * @var I95DevErpMQInterfaceFactory
     */
    public $i95DevErpMQFactory;
    /**
     * @var DateTime
     */
    public $date;
    /**
     * @var I95DevErpMQRepositoryFactory
     */
    public $i95DevErpMQRepository;
    /**
     * @var DataPersistenceInterfaceFactory
     */
    public $dataPersistence;
    /**
     * @var string
     */
    public $erpName;

    /**
     * Constructor for DI
     *
     * @param I95DevErpMQRepositoryFactory    $i95DevErpMQRepository
     * @param I95DevErpMQInterfaceFactory     $i95DevErpMQFactory
     * @param Logger                          $logger
     * @param DataPersistenceInterfaceFactory $dataPersistence
     * @param DateTime                        $date
     */
    public function __construct(
        I95DevErpMQRepositoryFactory $i95DevErpMQRepository,
        I95DevErpMQInterfaceFactory $i95DevErpMQFactory,
        Logger $logger,
        DataPersistenceInterfaceFactory $dataPersistence,
        DateTime $date
    ) {
        $this->i95DevErpMQFactory = $i95DevErpMQFactory;
        $this->i95DevErpMQRepository = $i95DevErpMQRepository;
        $this->logger = $logger;
        $this->date = $date;
        $this->dataPersistence = $dataPersistence;
    }

    /**
     * Insert data in to Inbound message queue
     *
     * @param array       $recordData
     * @param ReverseSync $reverseSync
     *
     * @return string|array
     * @throws LocalizedException
     */
    public function defaultMessageQueueInsert($recordData, $reverseSync)
    {
        try {
            $targetKey = Data::TARGET_KEY;
            $referenceKey = Data::REF_KEY;
            $targetId = $this->checkTargetId($recordData, $targetKey);
            $recordData[$referenceKey] = $referKey = $this->checkReferenceKey($recordData, $referenceKey);
            $messageId = null;

            $i95DevErpMQ = $this->i95DevErpMQFactory->create();
            $i95DevErpMQ->setErpCode($reverseSync->erpName);
            $i95DevErpMQ->setEntityCode($reverseSync->currententitiyCode);
            $i95DevErpMQ->setTargetId($targetId);
            $i95DevErpMQ->setCreatedDt($this->date->gmtDate());
            $i95DevErpMQ->setStatus(Data::PENDING);
            $i95DevErpMQ->setRefName($referKey);
            $i95DevErpMQ->setUpdatedDt($this->date->gmtDate());
            $this->setI95DevErpMQValue($recordData, $i95DevErpMQ, $targetId);

            $i95DevErpMQ->setDataString(json_encode($recordData));
            /* Updated by Ranjith Rasakatla, Null value check for parent data */
            if (isset($reverseSync->currentMethodProperties['isChild'])
                && $reverseSync->parentData !== null
            ) {
                $i95DevErpMQ->setParentMsgId($reverseSync->parentData->getMessageId());
            }

            $mqData = $this->i95DevErpMQRepository->create()->saveMQData($i95DevErpMQ);
            $messageId = $mqData->getMsgId();
            //@ Hrusikesh added extra parameter $messageId
            if (isset($reverseSync->currentMethodProperties['saveWithSync'])
                && $messageId && json_encode($recordData)
            ) {
                $response = $this->dataPersistence->create()->createEntity(
                    $reverseSync->currententitiyCode,
                    json_encode($recordData),
                    $messageId
                );

                if (isset($response)) {
                    $this->dataPersistence->create()->updateErpMQStatus(
                        $response->getStatus(),
                        $response->getResultdata(),
                        $response->getMessage(),
                        $messageId,
                        $response->getCode()
                    );
                    if ($response->status == Data::ERROR) {
                        return ['messageId' => $messageId, 'message' => $response->getMessage()];
                    }
                } else {
                    $this->dataPersistence->create()->updateErpMQStatus(
                        Data::ERROR,
                        null,
                        'Some issue occur while saving data. Please contact admin.',
                        $messageId,
                        105
                    );
                }
            }
            return $messageId;
        } catch (LocalizedException $ex) {
            $this->logger->createLog(
                __METHOD__,
                $ex->getMessage(),
                Data::I95EXC,
                'critical'
            );

            return null;
        }
    }

    /**
     * Check target id
     *
     * @param  array  $recordData
     * @param  string $targetKey
     * @return mixed
     */
    public function checkTargetId($recordData, $targetKey)
    {
        if (isset($recordData[$targetKey])) {
            return $recordData[Data::TARGET_KEY];
        } else {
            throw new LocalizedException(__("target_id_required"));
        }
    }

    /**
     * Check reference key
     *
     * @param  array  $recordData
     * @param  string $referenceKey
     * @return mixed|null
     */
    public function checkReferenceKey($recordData, $referenceKey)
    {
        if (isset($recordData[$referenceKey])) {
            return $recordData[Data::REF_KEY];
        } else {
            return null;
        }
    }

    /**
     * Set i95dev erp mq value
     *
     * @param        array  $recordData
     * @param        object $i95DevErpMQ
     * @param        string $targetId
     * @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection
     */
    public function setI95DevErpMQValue($recordData, &$i95DevErpMQ, $targetId)
    {
        if (isset($recordData[self::DESTINATIONID]) && $recordData[self::DESTINATIONID] > 0) {
            $i95DevErpMQ->setDestinationMsgId($recordData[self::DESTINATIONID]);
            $record_exists = $this->i95DevErpMQRepository->create()->getCollection()
                ->addFieldToSelect(['msg_id','status'])
                ->addFieldToFilter("destination_msg_id", $recordData[self::DESTINATIONID])
                ->addFieldToFilter("target_id", $targetId)
                ->getFirstItem();
            if (!empty($record_exists->getData())
                && $record_exists->getStatus() == Data::ERROR
            ) {
                $i95DevErpMQ->setStatus(Data::ERROR);
                $i95DevErpMQ->setMsgId($record_exists->getMsgId());
                $i95DevErpMQ->setCounter(0);
                $i95DevErpMQ->setResponseCounter(0);
            } elseif (!empty($record_exists->getData())) {
                $i95DevErpMQ->setMsgId($record_exists->getMsgId());
                $i95DevErpMQ->setCounter(0);
            }
        }
    }
}

<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model;

use Exception;
use I95DevConnect\MessageQueue\Api\Data\I95DevErpMQInterfaceFactory;
use I95DevConnect\MessageQueue\Api\I95DevErpDataRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Api\I95DevErpMQRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Api\I95DevResponseInterfaceFactory;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\DataPersistence\Validate;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Decoder;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Generic class for data persistance.
 */
class AbstractDataPersistence
{
    /**
     * @var string
     */
    public $entityCode;

    /**
     * @var Decoder
     */
    public $jsonDecoder;

    /**
     * @var string
     */
    public $stringData;

    /**
     * @var I95DevResponseInterfaceFactory
     */
    public $i95DevResponse;

    /**
     * @var ErrorUpdateDataFactory
     */
    public $messageErrorModel;

    /**
     * @var I95DevErpMQInterfaceFactory
     */
    public $i95DevErpMQFactory;

    /**
     * @var LoggerInterfaceFactory
     */
    public $logger;

    /**
     * @var I95DevErpMQRepositoryInterfaceFactory
     */
    public $i95DevErpMQRepository;

    /**
     * @var DateTime
     */
    public $date;

    /**
     * @var Manager
     */
    public $eventManager;

    /**
     * @var DataPersistence\Validate
     */
    public $validate;

    /**
     * @var I95DevErpDataRepositoryInterfaceFactory
     */
    public $i95DevERPDataRepository;

    /**
     * @var string
     */
    public $erpCode;

    /**
     * @var string
     */
    public $statusCode;

    /**
     * @var string
     */
    public $updatedBy;

    /**
     *
     * @param Decoder $jsonDecoder
     * @param I95DevResponseInterfaceFactory $i95DevResponse
     * @param ErrorUpdateDataFactory $messageErrorModel
     * @param I95DevErpMQInterfaceFactory $i95DevErpMQFactory
     * @param LoggerInterfaceFactory $logger
     * @param I95DevErpMQRepositoryInterfaceFactory $i95DevErpMQRepository
     * @param DateTime $date
     * @param Manager $eventManager
     * @param Validate $validate
     * @param I95DevErpDataRepositoryInterfaceFactory $i95DevERPDataRepository
     */
    public function __construct( // NOSONAR
        Decoder $jsonDecoder,
        I95DevResponseInterfaceFactory $i95DevResponse,
        ErrorUpdateDataFactory $messageErrorModel,
        I95DevErpMQInterfaceFactory $i95DevErpMQFactory,
        LoggerInterfaceFactory $logger,
        I95DevErpMQRepositoryInterfaceFactory $i95DevErpMQRepository,
        DateTime $date,
        Manager $eventManager,
        Validate $validate,
        I95DevErpDataRepositoryInterfaceFactory $i95DevERPDataRepository
    ) {
        $this->jsonDecoder = $jsonDecoder;
        $this->i95DevResponse = $i95DevResponse;
        $this->messageErrorModel = $messageErrorModel;
        $this->i95DevErpMQFactory = $i95DevErpMQFactory;
        $this->logger = $logger;
        $this->i95DevErpMQRepository = $i95DevErpMQRepository;
        $this->date = $date;
        $this->eventManager = $eventManager;
        $this->validate = $validate;
        $this->i95DevERPDataRepository = $i95DevERPDataRepository;
    }

    /**
     * Get entity code
     *
     * @return string
     */
    public function getEntityCode()
    {
        return $this->entityCode;
    }

    /**
     * Set entity code
     *
     * @param string $entityCode
     */
    public function setEntityCode($entityCode)
    {
        $this->entityCode = $entityCode;
    }

    /**
     * Get formatted string
     *
     * @param string $dataString
     * @return array
     * @throws LocalizedException
     */
    public function getFormattedString($dataString)
    {
        //Hrusikesh Added Try Catch Block
        try {
            if (!$dataString) {
                throw new LocalizedException(__("Data string required is empty"));
            }

            return $this->jsonDecoder->decode($dataString);
        } catch (LocalizedException $ex) {
            $this->logger->create()
                ->createLog(
                    __METHOD__,
                    $ex->getMessage(),
                    LoggerInterface::I95EXC,
                    'critical'
                );
            throw new LocalizedException(__($ex->getMessage()));
        }
    }

    /**
     * Set stringData
     *
     * @param string $stringData
     */
    public function setStringData($stringData)
    {
        $this->stringData = $stringData;
    }

    /**
     * Set response in responseInterface
     *
     * @param bool $status
     * @param string $message
     * @param string $resultData
     * @param int $code
     * @return mixed
     */
    public function setResponse($status, $message = null, $resultData = null, $code = 107)
    {
        $i95DevResponse = $this->i95DevResponse->create();
        $i95DevResponse->setStatus($status);
        $i95DevResponse->setMessage($message);
        $i95DevResponse->setResultdata($resultData);
        $i95DevResponse->setCode($code);

        return $i95DevResponse;
    }

    /**
     * Update Message queue table status
     *
     * @param string $status
     * @param string $data
     * @param string $message
     * @param int $msgId
     * @param int $code
     * @throws Exception
     */
    public function updateErpMQStatus($status, $data, $message, $msgId, $code = 107)
    {
        $messageQueue = $this->i95DevErpMQRepository->create()->get($msgId);
        if ($messageQueue->getMsgId()) {
            $counter = $messageQueue->getCounter();
            $i95DevErpMQ = $this->i95DevErpMQFactory->create();
            $i95DevErpMQ->setMsgId($messageQueue->getMsgId());
            $i95DevErpMQ->setUpdatedDt($this->date->gmtDate());

            if (($status == Data::SUCCESS) || ($status == Data::COMPLETE)) {
                $errorLog = $this->messageErrorModel->create();
                $errorLog->load($messageQueue->getErrorId());
                $errorLog->delete();
                $i95DevErpMQ->setStatus($status);
                $i95DevErpMQ->setMagentoId($data);
                $i95DevErpMQ->setErrorId(0);
                $i95DevErpMQ->setCounter($counter + 1);
                $i95DevErpMQ->setResponseCounter(0);
                $this->i95DevErpMQRepository->create()->saveMQData($i95DevErpMQ);    
                $this->i95DevERPDataRepository->create()->deleteMQData($messageQueue->getDataId());
                $this->logger->create()
                    ->createLog(
                        __METHOD__,
                        "updated message queue with msg id - $msgId status as : SUCCESS",
                        LoggerInterface::MSGLOGNAME,
                        'success'
                    );
            } else {
                $errorLog = $this->messageErrorModel->create();
                $errorLog->load($messageQueue->getErrorId());
                $errorId = $this->updateErrorData($message, $code);
                if ($errorLog->getMsg() != $message) {
                    $i95DevErpMQ->setResponseCounter(0);
                }
                $i95DevErpMQ->setStatus(Data::ERROR);
                $this->logger->create()
                    ->createLog(
                        __METHOD__,
                        $message,
                        LoggerInterface::MSGLOGNAME,
                        'error'
                    );
                $i95DevErpMQ->setErrorId($errorId);

            $i95DevErpMQ->setCounter($counter + 1);
            $this->i95DevErpMQRepository->create()->saveMQData($i95DevErpMQ);
            }
        }
    }

    /**
     * Updates error data in error report table
     *
     * @param string $message
     * @param int $code
     * @return int
     */
    public function updateErrorData($message, $code = 107)
    {
        $errorId = 0;
        if ($message) {
            $message = is_array($message) ? implode(",", $message) : $message;
            try {
                $errorDataModel = $this->messageErrorModel->create();
                $errorDataModel->setMsg($message);
                $errorDataModel->setCode($code);
                $errorDataModel->save();
                $errorId = $errorDataModel->getId();
            } catch (LocalizedException $ex) {
                $this->logger->create()
                    ->createLog(
                        __METHOD__,
                        $ex->getMessage(),
                        LoggerInterface::I95EXC,
                        'critical'
                    );
            }
        }
        return $errorId;
    }

    /**
     * Returns ERP code
     *
     * @return string
     */
    public function getErpCode()
    {
        return $this->erpCode;
    }

    /**
     * Sets erp code
     *
     * @param string $erpCode
     * @return $this
     */
    public function setErpCode($erpCode)
    {
        $this->erpCode = $erpCode;
        return $this;
    }

    /**
     * Return status code
     *
     * @return string
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Sets status code
     *
     * @param string $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Returns updated by
     *
     * @return string
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Set updated by
     *
     * @param string $updatedBy
     * @return $this
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;
        return $this;
    }
}

<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model;

use I95DevConnect\MessageQueue\Api\Data\I95DevErpMQInterfaceFactory;
use I95DevConnect\MessageQueue\Api\DataPersistenceInterface;
use I95DevConnect\MessageQueue\Api\I95DevErpDataRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Api\I95DevErpMQRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Api\I95DevResponseInterface;
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
 * Data Persistence model
 */
class DataPersistence extends AbstractDataPersistence implements DataPersistenceInterface
{
    /**
     * @var string
     */
    public $syncMethodNotExists = "Sync Method Not Exists";

    /**
     * @var \I95DevConnect\MessageQueue\Helper\DataPersistence
     */
    public $dataPersistenceHelper;

    /**
     * @var I95DevErpMQInterfaceFactory
     */
    public $MQInterface;

    /**
     * @var LoggerInterfaceFactory
     */
    public $logger;

    /**
     * @var object
     */
    public $syncModel;

    /**
     *
     * @param \I95DevConnect\MessageQueue\Helper\DataPersistence $dataPersistenceHelper
     * @param I95DevErpMQInterfaceFactory $MQInterface
     * @param LoggerInterfaceFactory $logger
     * @param Decoder $jsonDecoder
     * @param I95DevResponseInterfaceFactory $i95DevResponse
     * @param ErrorUpdateDataFactory $messageErrorModel
     * @param I95DevErpMQInterfaceFactory $i95DevErpMQ
     * @param I95DevErpMQRepositoryInterfaceFactory $i95DevErpMQRepository
     * @param DateTime $date
     * @param Manager $eventManager
     * @param Validate $validate
     * @param I95DevErpDataRepositoryInterfaceFactory $i95DevERPDataRepository
     */
    public function __construct( // NOSONAR
        \I95DevConnect\MessageQueue\Helper\DataPersistence $dataPersistenceHelper,
        I95DevErpMQInterfaceFactory $MQInterface,
        LoggerInterfaceFactory $logger,
        Decoder $jsonDecoder,
        I95DevResponseInterfaceFactory $i95DevResponse,
        ErrorUpdateDataFactory $messageErrorModel,
        I95DevErpMQInterfaceFactory $i95DevErpMQ,
        I95DevErpMQRepositoryInterfaceFactory $i95DevErpMQRepository,
        DateTime $date,
        Manager $eventManager,
        Validate $validate,
        I95DevErpDataRepositoryInterfaceFactory $i95DevERPDataRepository
    ) {
        $this->dataPersistenceHelper = $dataPersistenceHelper;
        $this->MQInterface = $MQInterface;
        $this->logger = $logger;
        parent::__construct(
            $jsonDecoder,
            $i95DevResponse,
            $messageErrorModel,
            $i95DevErpMQ,
            $logger,
            $i95DevErpMQRepository,
            $date,
            $eventManager,
            $validate,
            $i95DevERPDataRepository
        );
    }

    /**
     * Create Entity
     *
     * @param string $entityCode
     * @param array $dataString
     * @param string $messageId
     * @param string $erpCode
     * @return I95DevResponseInterface|void
     */
    public function createEntity($entityCode, $dataString, $messageId, $erpCode = null)
    {
        try {
            if ($this->checkEntityExists($entityCode)) {
                $dataString = $this->getFormattedString($dataString);
                //@Hrusikesh Updates MQ status after decode json string
                $this->saveRecord($messageId);
                return $this->syncModel->create($dataString, $entityCode, $erpCode);
            }
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(
                __METHOD__,
                $ex->getMessage(),
                LoggerInterface::I95EXC,
                LoggerInterface::CRITICAL
            );

            return $this->setResponse(
                Data::ERROR,
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
    }

    /**
     * Checks if the given entity code is exists or not
     *
     * @param string $entityCode
     * @return boolean
     * @throws LocalizedException
     */
    private function checkEntityExists($entityCode)
    {
        if (isset($this->dataPersistenceHelper->entityList[$entityCode])) {
            $this->setEntityCode($entityCode);
            $entityProperties = $this->dataPersistenceHelper->entityList[$entityCode];
            if (isset($entityProperties['syncdetails'])) {
                $syncdetails = $entityProperties['syncdetails'];
                $this->syncModel = $syncdetails['classObject'];

                return true;
            } else {
                $this->logger->create()->createLog(
                    __METHOD__,
                    __($this->syncMethodNotExists),
                    LoggerInterface::I95EXC,
                    LoggerInterface::CRITICAL
                );

                throw new LocalizedException(__($this->syncMethodNotExists));
            }
        }
        return false;
    }

    /**
     * Method to update status of Inbound MQ record
     *
     * @param type $messageId
     * @throws LocalizedException
     */
    public function saveRecord($messageId)
    {
        try {
            $i95DevErpMQ = $this->MQInterface->create();
            $i95DevErpMQ->setMsgId($messageId);
            $i95DevErpMQ->setStatus(Data::PROCESSING);
            $this->i95DevErpMQRepository->create()->saveMQData($i95DevErpMQ);
        } catch (LocalizedException $ex) {
            $message = $ex->getMessage();
            throw new LocalizedException(__($message));
        }
    }

    /**
     * Get Entity Info
     *
     * @param string $entityCode
     * @param array $dataString
     * @param string $erpCode
     * @param int $messageId
     * @return I95DevResponseInterface
     * @throws LocalizedException
     */
    public function getEntityInfo($entityCode, $dataString, $erpCode = null, $messageId = null)
    {
        try {
            if ($this->checkEntityExists($entityCode)) {
                return $this->syncModel->getInfo($dataString, $entityCode, $erpCode, $messageId);
            } else {
                $this->logger->create()->createLog(
                    __METHOD__,
                    __($this->syncMethodNotExists),
                    LoggerInterface::I95EXC,
                    LoggerInterface::CRITICAL
                );

                throw new LocalizedException(
                    __($this->syncMethodNotExists)
                );
            }
        } catch (LocalizedException $ex) {
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
    }

    /**
     * Get Entity Response
     *
     * @param string $entityCode
     * @param array $dataString
     * @param string $erpCode
     * @return I95DevResponseInterface|void
     */
    public function getEntityResponse($entityCode, $dataString, $erpCode = null)
    {
        try {
            if ($this->checkEntityExists($entityCode)) {
                $dataString = $this->getFormattedString($dataString);
                return $this->syncModel->getResponse($dataString, $entityCode, $erpCode);
            }
        } catch (LocalizedException $ex) {
            return $this->setResponse(
                Data::ERROR,
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
    }
}

<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Model;

use I95DevConnect\CloudConnect\Api\Data\RequestInterfaceFactory;
use I95DevConnect\CloudConnect\Api\PullResponseInterface;
use I95DevConnect\CloudConnect\Api\PushDataInterface;
use I95DevConnect\CloudConnect\Helper\ConfigHelper;
use I95DevConnect\CloudConnect\Model\ServiceMethod\ServiceMethodFactory;
use I95DevConnect\MessageQueue\Api\I95DevMagMQRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\ReadCustomXml;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;

/**
 * class to push the data from magento to cloud
 */
class PushData extends AbstractAgentCron implements PushDataInterface
{
    public const  SCHEDULER_TYPE = 'PushData';
    /**
     * @var ServiceMethod\ServiceMethodFactory
     */
    public $serviceMethod;
    /**
     * @var int
     */
    public $sendIds;
    /**
     * @var RequestInterfaceFactory
     */
    public $requestInterface;
    /**
     * @var PullResponseInterface
     */
    public $pullResponse;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $jsonHelper;
    /**
     * @var string
     */
    public $logFilename = self::SCHEDULER_TYPE;
    /**
     * @var $pushRecords
     */
    public $pushRecords;
    /**
     * @var I95DevMagMQRepositoryInterfaceFactory
     */
    public $I95DevMagMQRepository;
    /**
     * @var string
     */
    protected $schedulerType = self::SCHEDULER_TYPE;

    /**
     * @var Request
     */
    public $request;

    /**
     * @var ReadCustomXml
     */
    public $readCustomXml;

    /**
     * @var ConfigHelper
     */
    public $configHelper;

    /**
     * @var Data
     */
    public $mqHelper;

    /**
     * @var Manager
     */
    public $eventManager;

    /**
     * @var object
     */
    public $schedulerData;

    /**
     * PushData constructor.
     * @param LoggerFactory $logger
     * @param Request $request
     * @param Service $service
     * @param ServiceMethod\ServiceMethodFactory $serviceMethod
     * @param \I95DevConnect\CloudConnect\Helper\Data $cloudHelper
     * @param ReadCustomXml $readCustomXml
     * @param ConfigHelper $configHelper
     * @param RequestInterfaceFactory $requestInterface
     * @param PullResponseInterface $pullResponse
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param Data $mqHelper
     * @param I95DevMagMQRepositoryInterfaceFactory $I95DevMagMQRepository
     * @param Manager $eventManager
     */
    public function __construct( // NOSONAR
        LoggerFactory $logger,
        Request $request,
        Service $service,
        ServiceMethodFactory $serviceMethod,
        \I95DevConnect\CloudConnect\Helper\Data $cloudHelper,
        ReadCustomXml $readCustomXml,
        ConfigHelper $configHelper,
        RequestInterfaceFactory $requestInterface,
        PullResponseInterface $pullResponse,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        Data $mqHelper,
        I95DevMagMQRepositoryInterfaceFactory $I95DevMagMQRepository,
        Manager $eventManager
    ) {
        $this->request = $request;
        $this->readCustomXml = $readCustomXml;
        $this->serviceMethod = $serviceMethod;
        $this->configHelper = $configHelper;
        $this->requestInterface = $requestInterface;
        $this->pullResponse = $pullResponse;
        $this->jsonHelper = $jsonHelper;
        $this->mqHelper = $mqHelper;
        $this->I95DevMagMQRepository = $I95DevMagMQRepository;
        $this->eventManager = $eventManager;
        parent::__construct($cloudHelper, $service, $logger);
    }

    /**
     * @inheritDoc
     */
    public function syncData()
    {
        return $this->startCronProcess();
    }

    /**
     * Initiate push data job for forward sync
     *
     * @param string $schedulerId
     * @param array $schedulerData
     * @throws LocalizedException
     */
    protected function initiateJob($schedulerId, $schedulerData)
    {
        try {
            $this->processActiveEntity($schedulerData, $schedulerId);
        } catch (LocalizedException $ex) {
            throw new LocalizedException(__($ex->getMessage()));
        }
    }

    /**
     * Process active entity
     *
     * @param array $schedulerData
     * @param string $schedulerId
     * @throws LocalizedException
     */
    public function processActiveEntity($schedulerData, $schedulerId)
    {
        $this->entityManagementSync($schedulerData, $schedulerId);

        $this->schedulerData = $schedulerData;

        // @author arushi.bansal dispachted event to support mapping functionality in more modular way
        $this->eventManager->dispatch(
            "push_data_after_subscription_success",
            [
                'currentObject' => $this,
                'schedulerId' => $schedulerId
            ]
        );

        $entities = $this->readCustomXml->getXmlDataOrderBySyncOrder();
        $forwardSkipEntities = $this->configHelper->getForwardSkipEntities();

        foreach ($entities as $entity_code => $entity_value) {
            if (!in_array($entity_code, $forwardSkipEntities)) {
                $this->manipulateEntity($entity_code, $schedulerId);
            }
        }
    }

    /**
     * Entity management sync
     *
     * @param array $schedulerData
     * @param string $schedulerId
     * @throws LocalizedException
     */
    public function entityManagementSync($schedulerData, $schedulerId)
    {
        if ($schedulerData->IsConfigurationUpdated) {
            //@author Janani Allam service call to get entity status
            $schedulerEntityData = $this->service->makeServiceCall(
                self::SCHEDULER_TYPE,
                null,
                null,
                $schedulerId,
                'Entities'
            );
            if ($schedulerEntityData != '') {
                foreach ($schedulerEntityData->ResultData as $entityData) {
                    $this->cloudHelper->updateEntity(
                        $entityData->entityName,
                        $entityData->isOutboundActive,
                        $entityData->isInboundActive,
                        $this->logFilename
                    );
                }
                //@author Janani Allam send ACK for entity Management API
                $this->sendEntityACK(self::SCHEDULER_TYPE, $schedulerId);
            }
        }
    }

    /**
     * Method to send ACK for Entity status update ACK
     *
     * @param string $schedulerType
     * @param string $schedulerId
     * @return void
     * @throws LocalizedException
     * @author Janani Allam
     */
    public function sendEntityAck($schedulerType, $schedulerId)
    {
        $devReq = $this->requestInterface->create();
        $devReq->setContext(
            $this->request->prepareContextObject('pushData', $schedulerId)
        );
        $devReq->setType('entityUpdate');
        //sending entityAck to cloud
        $result = $this->service
            ->makeServiceCall($schedulerType, null, $devReq, $schedulerId, 'Ack');
        if (!$result->IsConfigurationUpdated) {
            $this->logger->create()->createLog(
                "PushData entity ACK",
                "Entity Management updated in cloud",
                $this->logFilename,
                Logger::INFO
            );
        }
    }

    /**
     * Method to push data to cloud
     *
     * @param string $entity
     * @param string $schedulerId
     * @throws LocalizedException
     */
    public function manipulateEntity($entity, $schedulerId)
    {
        try {
            $this->pushRecords = [];
            $packetSize = $this->cloudHelper->getPacketSize();
            $requestArray = [
                "requestData" => [],
                "packetSize" => $packetSize,
                "erp_name" => 'ERP'
            ];

            $collection = $this->I95DevMagMQRepository->create()->getCollection();
            $collection->addFieldToSelect('msg_id');
            $collection->addFieldToFilter("entity_code", $entity)
                ->addFieldToFilter("status", [Data::PENDING, Data::ERROR])
                ->addFieldToFilter('counter', ['lt' => Data::RETRY_LIMIT]);
            $collection->getSelect()->order('msg_id', 'ASC');
            $count = (int)($collection->getSize() / $packetSize);
            $loopCount = empty($collection->getSize() % $packetSize) ? $count : ($count + 1);

            while ($loopCount > 0) {
                //fetching entity from magento
                $result = $this->serviceMethod->create()->cloudConnect(
                    json_encode($requestArray),
                    $entity,
                    self::SCHEDULER_TYPE,
                    'getEntityInfo',
                    'sendInfo'
                );

                if (!empty($result) && !empty($result->resultData)) {
                    $this->pushRecords = $result->resultData;
                    $devResponse = $this->requestInterface->create();
                    $devResponse->setContext(
                        $this->request->prepareContextObject(self::SCHEDULER_TYPE, $schedulerId)
                    );
                    $devResponse->setPacketSize($loopCount);
                    $recordData = [];
                    foreach ($result->resultData as $record) {
                        $data = $this->cloudHelper->prepareDataObject();
                        $data->setSourceId($record['sourceId']);
                        $data->setInputData($record['InputData']);
                        $data->setReference($record['reference']);
                        $recordData[] = $data;
                    }
                    if (!empty($recordData)) {
                        $devResponse->setRequestData($recordData);
                        //sending entity to cloud
                        $this->resetRecordsToPending($entity);
                        $pushResponse = $this->service
                            ->makeServiceCall(self::SCHEDULER_TYPE, $entity, $devResponse, $schedulerId);
                        // save response  cloud_msg_id to destination_msg_id
                        $resultData = json_decode($this->jsonHelper->jsonEncode($pushResponse->ResultData), 1);
                        $this->serviceMethod->create()->cloudConnect(
                            $resultData,
                            $entity,
                            self::SCHEDULER_TYPE,
                            'getInfoResponse',
                            'setResponse'
                        );
                    }
                }
                $loopCount--;
            }
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(
                'PushDataCron',
                $ex->getMessage(),
                Logger::EXCEPTION,
                'critical'
            );
            $this->resetRecordsToPending($entity);
        }
    }

    /**
     * Method to reset the record status from Request Transferred to Pending on exception
     *
     * @param string $entity
     */
    public function resetRecordsToPending($entity)
    {
        try {
            if (!empty($this->pushRecords)) {
                foreach ($this->pushRecords as $record) {
                    $mqRecordCollection = $this->I95DevMagMQRepository->create()->getCollection();
                    $mqRecordCollection->addFieldToFilter("msg_id", $record['messageId'])
                        ->addFieldToFilter("entity_code", $entity)
                        ->addFieldToFilter("erp_code", $this->cloudHelper->getErpComponent());
                    $status = $mqRecordCollection->getFirstItem()->getStatus();
                    $destinationMessageId = $mqRecordCollection->getFirstItem()->getDestinationMsgId();
                    if ($destinationMessageId == 0 && $status == Data::SUCCESS) {
                        $this->cloudHelper->updateMagentoMQStatus(
                            Data::PENDING,
                            $mqRecordCollection->getFirstItem()->getMsgId()
                        );
                    }
                }
            }
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(
                'PushDataCron',
                $ex->getMessage(),
                Logger::EXCEPTION,
                'critical'
            );
        }
    }
}

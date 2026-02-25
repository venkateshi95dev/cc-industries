<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Model;

use I95DevConnect\CloudConnect\Api\Data\RequestInterfaceFactory;
use I95DevConnect\CloudConnect\Api\PullDataInterface;
use I95DevConnect\CloudConnect\Api\PushResponseInterface;
use I95DevConnect\CloudConnect\Helper\ConfigHelper;
use I95DevConnect\CloudConnect\Helper\Data;
use I95DevConnect\CloudConnect\Model\ServiceMethod\ServiceMethodFactory;
use I95DevConnect\MessageQueue\Model\ReadCustomXml;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;

/**
 * class to pull the data from cloud to magento
 */
class PullData extends AbstractAgentCron implements PullDataInterface
{
    /**
     * @var string
     */
    public $erpName = "ERP";
    /**
     * @var ServiceMethodFactory
     */
    public $serviceMethod;
    /**
     * @var ConfigHelper
     */
    public $configHelper;
    /**
     * @var ReadCustomXml
     */
    public $readCustomXml;
    /**
     * @var RequestInterfaceFactory
     */
    public $requestInterface;
    /**
     * @var RequestFactory
     */
    public $request;
    /**
     * @var PushResponseInterface
     */
    public $pushResponse;
    /**
     * @var Manager
     */
    public $eventManager;
    /**
     * @var string
     */
    protected $schedulerType = 'PullData';
    /**
     * @var string
     */
    protected $logFilename = 'PullData';

    /**
     * @var object
     */
    public $schedulerData;

    /**
     * PullData constructor.
     * @param LoggerFactory $logger
     * @param Service $service
     * @param ServiceMethodFactory $serviceMethod
     * @param ConfigHelper $configHelper
     * @param Data $cloudHelper
     * @param ReadCustomXml $readCustomXml
     * @param RequestInterfaceFactory $requestInterface
     * @param RequestFactory $request
     * @param PushResponseInterface $pushResponse
     * @param Manager $eventManager
     */
    public function __construct( // NOSONAR
        LoggerFactory $logger,
        Service $service,
        ServiceMethodFactory $serviceMethod,
        ConfigHelper $configHelper,
        Data $cloudHelper,
        ReadCustomXml $readCustomXml,
        RequestInterfaceFactory $requestInterface,
        RequestFactory $request,
        PushResponseInterface $pushResponse,
        Manager $eventManager
    ) {
        $this->readCustomXml = $readCustomXml;
        $this->serviceMethod = $serviceMethod;
        $this->configHelper = $configHelper;
        $this->requestInterface = $requestInterface;
        $this->request = $request;
        $this->pushResponse = $pushResponse;
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
     * Initiate pull data job for reverse sync
     *
     * @param string $schedulerId
     * @param array $schedulerData
     */
    protected function initiateJob($schedulerId, $schedulerData)
    {
        try {
            // Get the Scheduler Id - that will send with every request response cycle with cloud
            if ($schedulerData->IsConfigurationUpdated) {
                //@author Janani Allam service call for getting entity data info
                $schedulerEntityData = $this->service->makeServiceCall(
                    $this->schedulerType,
                    null,
                    null,
                    $schedulerId,
                    'Entities'
                );

                $this->updateEntity($schedulerEntityData, $schedulerId);
            }

            $this->schedulerData = $schedulerData;
            // @author arushi.bansal dispachted event to support mapping functionality in more modular way
            $this->eventManager->dispatch(
                "pull_data_after_subscription_success",
                [
                    'currentObject' => $this,
                    'schedulerId' => $schedulerId
                ]
            );

            $entities = $this->getEntityBySyncOrder();

            $this->processPullDataFunctionality($entities, $schedulerId);
        } catch (LocalizedException $ex) {
            throw new LocalizedException(__($ex->getMessage()));
        }
    }

    /**
     * Update entity
     *
     * @param array $schedulerEntityData
     * @param string $schedulerId
     */
    public function updateEntity($schedulerEntityData, $schedulerId)
    {
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
            $this->sendEntityACK($this->schedulerType, $schedulerId);
        }
    }

    /**
     * Method to send ACK for Entity status update
     *
     * @param string $schedulerType
     * @param string $schedulerId
     * @return boolean
     */
    public function sendEntityAck($schedulerType, $schedulerId)
    {
        $devReq = $this->requestInterface->create();
        $devReq->setContext(
            $this->request->create()->prepareContextObject('pullData', $schedulerId)
        );
        $devReq->setType('entityUpdate');
        //sending entityAck to cloud
        $result = $this->service
            ->makeServiceCall($schedulerType, null, $devReq, $schedulerId, 'Ack');
        if (!$result->IsConfigurationUpdated) {
            $this->logger->create()->createLog(
                "PullData entity ACK",
                "Entity Management updated in cloud",
                $this->logFilename,
                Logger::INFO
            );
        }
        return true;
    }

    /**
     * Method to get sync order for all entities
     *
     * @return array
     * @throws LocalizedException
     */
    public function getEntityBySyncOrder()
    {
        return $this->readCustomXml->getXmlDataOrderBySyncOrder();
    }

    /**
     * Fetch the data from cloud
     *
     * @param array $entities
     * @param string $schedulerId
     */
    public function processPullDataFunctionality($entities, $schedulerId)
    {
        $reverseSkipEntities = $this->configHelper->getReverseSkipEntities();

        foreach ($entities as $entity_code => $entity_value) {
            // Loop all the entities for pulling the data
            if (!in_array($entity_code, $reverseSkipEntities)) {
                do {
                    $requestObj = $this->requestInterface->create();
                    $packetSize = $this->cloudHelper->getPacketSize();
                    $requestObj->setContext(
                        $this->request->create()->prepareContextObject(
                            $this->schedulerType,
                            $schedulerId
                        )
                    );
                    $requestObj->setPacketSize($packetSize);
                    $requestObj->setRequestData([]);
                    // fetch the data from cloud
                    $data = $this->service->makeServiceCall(
                        $this->schedulerType,
                        $entity_code,
                        $requestObj,
                        $schedulerId
                    );

                    if (!empty($data->ResultData) && is_array($data->ResultData)) {
                        // process the fetched data and move to cloud
                        $this->serviceMethod->create()->cloudConnect(
                            $data,
                            $entity_code,
                            $this->schedulerType,
                            'reverse'
                        );
                    }
                } while (!empty($data->ResultData));
            }
        }
    }
}

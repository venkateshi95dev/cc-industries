<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Model\ServiceMethod\Forward;

use I95DevConnect\CloudConnect\Api\Data\RequestInterfaceFactory;
use I95DevConnect\CloudConnect\Model\Logger;
use I95DevConnect\CloudConnect\Model\LoggerFactory;
use I95DevConnect\CloudConnect\Model\RequestFactory;
use I95DevConnect\CloudConnect\Model\Service;
use I95DevConnect\MessageQueue\Api\I95DevMagMQRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\CloudConnect\Helper\Data as CloudHelper;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class to send Acknowledgement for ERP Response
 */
class ResponseAcknowledgement
{
    /**
     * @var LoggerFactory
     */
    public $logger;
    /**
     * @var string
     */
    public $schedulerType = 'PullResponseAck';
    /**
     * @var Service
     */
    public $service;
    /**
     * @var CloudHelper
     */
    public $cloudHelper;
    /**
     * @var RequestInterfaceFactory
     */
    public $requestInterface;
    /**
     * @var RequestFactory
     */
    public $request;
    /**
     * @var I95DevMagMQRepositoryInterfaceFactory
     */
    public $I95DevMagMQ;

    /**
     * @param LoggerFactory $logger
     * @param Service $service
     * @param CloudHelper $cloudHelper
     * @param RequestInterfaceFactory $requestInterface
     * @param RequestFactory $request
     * @param I95DevMagMQRepositoryInterfaceFactory $I95DevMagMQ
     */
    public function __construct(
        LoggerFactory $logger,
        Service $service,
        CloudHelper $cloudHelper,
        RequestInterfaceFactory $requestInterface,
        RequestFactory $request,
        I95DevMagMQRepositoryInterfaceFactory $I95DevMagMQ
    ) {
        $this->logger = $logger;
        $this->service = $service;
        $this->cloudHelper = $cloudHelper;
        $this->requestInterface = $requestInterface;
        $this->request = $request;
        $this->I95DevMagMQ = $I95DevMagMQ;
    }

    /**
     * Method to send Acknowledgement to ERP
     *
     * @param string $entity
     * @param int $destinationId
     * @param int $schedulerId
     * @return void
     */
    public function syncResponseAck($entity, $destinationId, $schedulerId)
    {
        try {
            $destination_msg_id = 'destination_msg_id';
            $collection = $this->I95DevMagMQ->create()->getCollection();
            $collection->addFieldToSelect(['msg_id','magento_id', $destination_msg_id]);
            $collection->addFieldToFilter("entity_code", $entity);
            $collection->addFieldToFilter("erp_code", $this->cloudHelper->getErpComponent());
            $collection->addFieldToFilter("status", ["in" => [CloudHelper::SUCCESS_C]]);
            $collection->addFieldToFilter($destination_msg_id, ["in" => $destinationId]);
            $collection->getSelect()->order('msg_id', 'ASC');

            if (!empty($collection) && $collection->getSize() > 0) {
                $packetSize = $this->cloudHelper->getPacketSize();
                $devResponse = $this->requestInterface->create();
                $devResponse->setContext(
                    $this->request->create()->prepareContextObject($this->schedulerType, $schedulerId)
                );
                $packets = array_chunk($collection->getData(), $packetSize);

                $this->sendEntityToCloud($packets, $entity, $devResponse, $schedulerId);
            }
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(
                'PullResponseAckCron',
                $ex->getMessage(),
                Logger::EXCEPTION,
                'critical'
            );
        }
    }

    /**
     * Send entity to cloud
     *
     * @param array $packets
     * @param string $entity
     * @param object $devResponse
     * @param string $schedulerId
     */
    public function sendEntityToCloud($packets, $entity, $devResponse, $schedulerId)
    {
        try {
            $ackResponse = null;
            foreach ($packets as $packet) {
                $recordData = [];
                foreach ($packet as $record) {
                    $data = $this->cloudHelper->prepareDataObject();
                    $data->setSourceId($record['magento_id']);
                    $data->setMessageId((int)$record['destination_msg_id']);
                    $recordData[] = $data;
                }

                if (!empty($recordData)) {
                    $devResponse->setRequestData($recordData);
                    $devResponse->setPacketSize(count($recordData));
                    //sending entity to cloud
                    $ackResponse = $this->service
                        ->makeServiceCall($this->schedulerType, $entity, $devResponse, $schedulerId);
                }

                if (is_object($ackResponse) && $ackResponse->Result) {
                    foreach ($packet as $record) {
                        $this->logger->create()->createLog(
                            'PullResponseAckCron',
                            $record["msg_id"],
                            "responselog",
                            'critical'
                        );
                        $this->cloudHelper->
                        updateMagentoMQStatus(Data::COMPLETE, $record["msg_id"]);
                    }
                }
            }
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(
                'PullResponseAckCron',
                $ex->getMessage(),
                Logger::EXCEPTION,
                'critical'
            );
            throw new LocalizedException(__($ex->getMessage()));
        }
    }
}

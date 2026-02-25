<?php

/**
 * @author Arushi Bansal
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_ShippingMapping
 */

namespace I95DevConnect\CloudConnect\Observer;

use I95DevConnect\CloudConnect\Model\Logger;
use I95DevConnect\CloudConnect\Model\RequestFactory;
use I95DevConnect\ShippingMapping\Api\ShippingMappingManagementInterfaceFactory;
use I95DevConnect\ShippingMapping\Helper\Data;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Observer to get the shipping mapping when cron run
 */
class ShippingMappingList implements ObserverInterface
{
    public const SCHEDULER_TYPE = "pushData";

    /**
     * @var ShippingMappingManagementInterfaceFactory
     */
    public $shippingMappingMgmt;

    /**
     * @var Data
     */
    public $helper;

    /**
     * @var RequestFactory
     */
    public $request;

    /**
     * @var Logger
     */
    public $logger;

    /**
     * ShippingMappingList constructor.
     * @param ShippingMappingManagementInterfaceFactory $shippingMappingMgmt
     * @param Data $helper
     * @param RequestFactory $request
     * @param Logger $logger
     */
    public function __construct(
        ShippingMappingManagementInterfaceFactory $shippingMappingMgmt,
        Data $helper,
        RequestFactory $request,
        Logger $logger
    ) {
        $this->shippingMappingMgmt = $shippingMappingMgmt;
        $this->helper = $helper;
        $this->request = $request;
        $this->logger = $logger;
    }

    /**
     * Execute method
     *
     * @param Observer $observer
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {

        if ($this->helper->isEnabled()) {
            $currentObj = $observer->getEvent()->getData("currentObject");
            $schedulerId = $observer->getEvent()->getData("schedulerId");

            if ($currentObj->schedulerData->IsShippingMappingUpdated) {
                $shippingMappingData = $currentObj->service->makeServiceCall(
                    self::SCHEDULER_TYPE,
                    null,
                    null,
                    $schedulerId,
                    'ShippingList'
                );

                if ($shippingMappingData != '' && isset($shippingMappingData->ResultData)) {
                    $this->shippingMappingMgmt->create()->processMappingData($shippingMappingData->ResultData);

                    $this->sendAck(
                        self::SCHEDULER_TYPE,
                        $schedulerId,
                        $currentObj
                    );
                }
            }
        }
    }

    /**
     * Method to send ACK for Entity status update
     *
     * @param string $schedulerType
     * @param string $schedulerId
     * @param object $currentObj
     * @return boolean
     * @throws LocalizedException
     */
    private function sendAck($schedulerType, $schedulerId, $currentObj)
    {

        $devReq = $currentObj->requestInterface->create();

        $devReq->setContext(
            $this->request->create()->prepareContextObject('pullData', $schedulerId)
        );

        $devReq->setType('shippingMappingUpdate');

        //sending entityAck to cloud
        $result = $currentObj->service
            ->makeServiceCall($schedulerType, null, $devReq, $schedulerId, 'Ack');

        if (!$result->IsShippingMappingUpdated) {
            $this->logger->createLog(
                "PullData entity ACK",
                "Shipping Mapping updated in cloud",
                "shipping_mapping",
                Logger::INFO
            );
        }
        return true;
    }
}

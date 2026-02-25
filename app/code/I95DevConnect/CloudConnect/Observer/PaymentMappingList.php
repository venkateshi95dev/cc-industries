<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Observer;

use I95DevConnect\CloudConnect\Model\Logger;
use I95DevConnect\PaymentMapping\Api\PaymentMappingManagementInterfaceFactory;
use I95DevConnect\PaymentMapping\Helper\Data;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class for saving Payment Mapping List
 */
class PaymentMappingList implements ObserverInterface
{
    public const SCHEDULER_TYPE = "pushData";
    /**
     * @var PaymentMappingManagementInterfaceFactory
     */
    public $paymentMappingMgmt;
    /**
     * @var Data
     */
    public $helper;

    /**
     * @param PaymentMappingManagementInterfaceFactory $paymentMappingMgmt
     * @param Data $helper
     */
    public function __construct(
        PaymentMappingManagementInterfaceFactory $paymentMappingMgmt,
        Data $helper
    ) {
        $this->paymentMappingMgmt = $paymentMappingMgmt;
        $this->helper = $helper;
    }

    /**
     * Execute method
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->helper->isEnabled() && $this->helper->isCloudEnabled()) {
            $currentObj = $observer->getEvent()->getData("currentObject");
            $schedulerId = $observer->getEvent()->getSchedulerId();

            if ($currentObj->schedulerData->IsPaymentMappingUpdated) {
                $paymentMappingData = $currentObj->service->makeServiceCall(
                    self::SCHEDULER_TYPE,
                    null,
                    null,
                    $schedulerId,
                    'PaymentList'
                );
                if ($paymentMappingData != '' && isset($paymentMappingData->ResultData)) {
                    $this->paymentMappingMgmt->create()->processMappingData($paymentMappingData->ResultData);
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
     */
    private function sendAck($schedulerType, $schedulerId, $currentObj)
    {
        $devReq = $currentObj->requestInterface->create();
        $devReq->setContext(
            $currentObj->request->create()->prepareContextObject('pullData', $schedulerId)
        );
        $devReq->setType('paymentMappingUpdate');
        //sending entityAck to cloud
        $result = $currentObj->service
            ->makeServiceCall($schedulerType, null, $devReq, $schedulerId, 'Ack');

        if (!$result->IsPaymentMappingUpdated) {
            $currentObj->logger->create()->createLog(
                "PullData entity ACK",
                "Payment Mapping updated in cloud",
                "payment_mapping",
                Logger::INFO
            );
        }
        return true;
    }
}

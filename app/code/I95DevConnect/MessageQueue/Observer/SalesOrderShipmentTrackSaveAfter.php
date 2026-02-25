<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Observer;

use Exception;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\SalesShipment;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\Order\Shipment;

/**
 * Observer class for sales order shipment track after save
 */
class SalesOrderShipmentTrackSaveAfter implements ObserverInterface
{
    public const MAGLOGNAME = 'MagentoToERP';
    public const ERPLOGNAME = 'ERPToMagento';
    public const I95EXC = 'i95devApiException';

    /**
     * @var Data
     */
    public $data;

    /**
     * @var customSalesOrder
     */
    public $customSalesShipment;

    /**
     *
     * @var date
     */
    public $date;

    /**
     * @var salesShipment
     */
    public $salesShipment;

    /**
     * @var Http
     */
    public $request;

    /**
     *
     * @param Data $data
     * @param SalesShipment $customSalesShipment
     * @param DateTime $date
     * @param Shipment $salesShipment
     * @param Http $request
     */
    public function __construct(
        Data $data,
        SalesShipment $customSalesShipment,
        DateTime $date,
        Shipment $salesShipment,
        Http $request
    ) {
        $this->data = $data;
        $this->customSalesShipment = $customSalesShipment;
        $this->date = $date;
        $this->salesShipment = $salesShipment;
        $this->request = $request;
    }

    /**
     * Save i95Dev Custom attributes.
     *
     * @param Observer $observer
     *
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        $is_enabled = $this->data->isEnabled();
        if (!$is_enabled) {
            return;
        }
        if ($this->data->getGlobalValue('i95_observer_skip') || $this->request->getParam('isI95DevRestReq') == 'true') {
            return;
        }

        try {
            $shipment = $observer->getEvent()->getDataObject();
            $shipmentId = $shipment->getParentId();
            $sales_shipment = $this->salesShipment->load($shipmentId);
            $incrementId = $sales_shipment->getIncrementId();
            $shipmentModel = $this->customSalesShipment;
            $customShipmentData = $shipmentModel->getCollection()
                    ->addFieldToSelect('id')
                    ->addFieldToFilter('source_shipment_id', $incrementId);
            $customShipmentData->getSelect()->limit(1);
            $customShipmentData = $customShipmentData->getData();
            $customShipmentId = (isset($customShipmentData[0]['id']) ? $customShipmentData[0]['id'] : '');
            if ($customShipmentId !== null) {
                $customShipment = $shipmentModel->load($customShipmentId);
                $customShipment->setUpdatedDt($this->date->gmtDate());
                $customShipment->setUpdateBy('Magento');
                $customShipment->save();
            }
        } catch (LocalizedException $ex) {
            $this->data->createLog(__METHOD__, $ex->getMessage(), self::I95EXC, 'critical');
        }
    }
}

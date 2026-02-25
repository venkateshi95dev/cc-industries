<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 * @updatedBy Divya Koona. Removed getCustomOrder() function as it is not used.
 */

namespace I95DevConnect\MessageQueue\Observer;

use Exception;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\SalesOrder;
use I95DevConnect\MessageQueue\Model\SalesShipment;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection;

/**
 * Observer class for sales order shipment after save
 */
class SalesOrderShipmentSaveAfterObserver implements ObserverInterface
{
    public const MAGLOGNAME = 'MagentoToERP';
    public const ERPLOGNAME = 'ERPToMagento';
    public const I95EXC = 'i95devApiException';
    public const CRITICAL = 'critical';

    /**
     * @var Data
     */
    public $data;

    /**
     * @var shipmentTrackColelction
     */
    public $shipmentTrackColelction;

    /**
     * @var Order
     */
    public $salesOrderModel;

    /**
     * @var customSalesOrder
     */
    public $customSalesOrder;

    /**
     * @var customSalesOrder
     */
    public $customSalesShipment;

    /**
     * @var Http
     */
    public $request;

    /**
     *
     * @param Data $data
     * @param Collection $shipmentTrackColelction
     * @param Order $salesOrderModel
     * @param SalesOrder $customSalesOrder
     * @param SalesShipment $customSalesShipment
     * @param Http $request
     */
    public function __construct(
        Data $data,
        Collection $shipmentTrackColelction,
        Order $salesOrderModel,
        SalesOrder $customSalesOrder,
        SalesShipment $customSalesShipment,
        Http $request
    ) {
        $this->data = $data;
        $this->shipmentTrackColelction = $shipmentTrackColelction;
        $this->salesOrderModel = $salesOrderModel;
        $this->customSalesOrder = $customSalesOrder;
        $this->customSalesShipment = $customSalesShipment;
        $this->request = $request;
    }

    /**
     * Save i95Dev Custom attributes.
     *
     * @param Observer $observer
     *
     * @throws Exception
     * @updatedBy Divya Koona. Removed gp_orderprocess_flag related code.
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
            $shipment = $observer->getEvent()->getData('data_object');
            $orderId = $shipment->getData('order_id');
            //to check duplicate tracking numbers
            $shipmentId = $shipment->getData('entity_id');
            $this->trackingData($shipmentId);
            $isTotalQtyShipped = $this->isTotalOrderQtyShipped($orderId);
            //insert data into custom table i95dev_sales_flat_shipment if total qty of order shipment
            if ($isTotalQtyShipped) {
                $shipmentModel = $this->customSalesShipment;
                $shipmentModel->setSourceShipmentId($shipment->getIncrementId());
                $shipmentModel->setCreatedDt($shipment->getcreatedAt());
                $shipmentModel->setUpdatedDt($shipment->getupdatedAt());
                $shipmentModel->setUpdateBy('Magento');
                $shipmentModel->save();
            }
        } catch (LocalizedException $ex) {
            $this->data->createLog(__METHOD__, $ex->getMessage(), self::I95EXC, self::CRITICAL);
        }
    }

    /**
     * Check Duplicate Tracking Numbers
     *
     * @param string $shipmentId
     */
    private function trackingData($shipmentId)
    {

        try {
            $trackings = $this->shipmentTrackColelction->addAttributeToSelect('*')
                    ->addAttributeToFilter('parent_id', $shipmentId);
            $allTrackingIds = count($trackings->getAllIds());
            $trackingNumber = [];
            foreach ($trackings->getData() as $tracking) {
                $trackingNumber[] = $tracking['track_number'];
            }
            $uniqueData = count(array_unique($trackingNumber));
            if ($allTrackingIds != $uniqueData) {
                $this->data->criticalLog("Can't add same tracking numbers.");
                throw new LocalizedException(
                    __("Can't add same tracking numbers.")
                );
            }
        } catch (LocalizedException $ex) {
            $this->data->createLog(__METHOD__, $ex->getMessage(), self::I95EXC, self::CRITICAL);
        }
    }

    /**
     * Check all items qty shipped or not for order.
     *
     * @param  int $orderId
     * @return boolean
     */
    private function isTotalOrderQtyShipped($orderId)
    {

        $order = $this->salesOrderModel->load($orderId);
        $orderedQty = $order->getData('total_qty_ordered');
        $totalQty = 0;
        foreach ($order->getShipmentsCollection() as $shipment) {
            $qtyShipped = $shipment->getData('total_qty');
            $totalQty = $totalQty + $qtyShipped;
        }
        if ($orderedQty == $totalQty) {
            return true;
        }
        return false;
    }
}

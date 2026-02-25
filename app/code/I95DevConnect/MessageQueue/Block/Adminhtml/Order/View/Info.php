<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 * @codingStandardsIgnoreFile
 */

namespace I95DevConnect\MessageQueue\Block\Adminhtml\Order\View;

use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Helper\Generic;
use I95DevConnect\MessageQueue\Model\ChequeNumber;
use I95DevConnect\MessageQueue\Model\SalesOrder;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Escaper;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Block for displaying target information in order view page
 * @api
 */
class Info extends Template
{
    public const PAYMENTMETHOD = 'checkmo';
    public const TARGET_ORDER_ID = 'target_order_id';

    /**
     * @var string
     */
    protected $_template = 'I95DevConnect_MessageQueue::order/view/i95dev_custom_info.phtml';// phpcs:disable

    /**
     * @var string
     */
    public $customSalesOrder;

    /**
     * @var Registry
     */
    public $coreRegistry;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var Data
     */
    public $basedata;

    /**
     * @var Order
     */
    public $salesOrderModel;

    /**
     * @var generic
     */
    public $generic;

    /**
     * @var I95DevConnect\MessageQueue\Model\ChequeNumber
     */
    public $chequeNumberModel;

    /**
     * @var Escaper
     */
    public $escaper;

    /**
     * Info constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param SalesOrder $customSalesOrder
     * @param Data $helper
     * @param Order $salesOrderModel
     * @param Generic $generic
     * @param ChequeNumber $chequeNumber
     * @param StoreManagerInterface $storeManager
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct( // NOSONAR
        Context $context,
        Registry $registry,
        SalesOrder $customSalesOrder,
        Data $helper,
        Order $salesOrderModel,
        Generic $generic,
        ChequeNumber $chequeNumber,
        StoreManagerInterface $storeManager,
        Escaper $escaper,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->customSalesOrder = $customSalesOrder;
        $this->basedata = $helper;
        $this->salesOrderModel = $salesOrderModel;
        $this->generic = $generic;
        $this->storeManager = $storeManager;
        $this->chequeNumberModel = $chequeNumber;
        $this->escaper = $escaper;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve Order id
     *
     * @return string|null
     */
    public function getOrderId()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * Get target order id
     *
     * @return string
     */
    public function getTargetOrderId()
    {
        $order = $this->getOrderId();
        $sourceOrderId = $order->getIncrementId();
        $customCollection = $this->customSalesOrder
            ->getCollection();
        $customCollection->addFieldToSelect([self::TARGET_ORDER_ID, 'target_order_status'])
            ->addFieldToFilter('source_order_id', $sourceOrderId);

        $customCollection->getSelect()->limit(1);

        return $customCollection->getData();
    }

    /**
     * Retrieves target invoice id
     *
     * @return array
     */
    public function getTargetInvoiceId()
    {
        try {
            $targetInvoiceId = [];
            $order = $this->getOrderId();
            $invoices = $order->getInvoiceCollection()->getData();
            foreach ($invoices as $invoice) :
                $sourceInvoiceId = $invoice['increment_id'];
                $customInvoice = $this->generic->getCustomInvoiceById($sourceInvoiceId);
                $targetInvoiceId[] = $customInvoice->gettargetInvoiceId(); //changed
            endforeach;
        } catch (LocalizedException $ex) {
            $this->basedata->createLog(__METHOD__, $ex->getMessage(), "i95devException", 'critical');
        }
        return $targetInvoiceId;
    }

    /**
     * Retrieves target invoice id
     *
     * @return array
     */
    public function getTargetShipmentId()
    {
        try {
            $targetShipmentId = [];

            $order = $this->getOrderId();
            $shipments = $order->getShipmentsCollection()->getData();
            foreach ($shipments as $shipment) :
                $sourceShipmentId = $shipment['increment_id'];
                $cutomshipment = $this->generic->getCustomShipmentById($sourceShipmentId);
                $targetShipmentId[] = $cutomshipment->gettargetShipmentId(); //changed
            endforeach;
        } catch (LocalizedException $ex) {
            $this->basedata->criticalLog(__METHOD__, $ex->getMessage(), "i95devException");
        }
        return $targetShipmentId;
    }

    /**
     * To get Component
     *
     * @return string
     */
    public function getComponent()
    {
        $componentPath = 'i95dev_messagequeue/I95DevConnect_settings/component';
        return $this->basedata->getscopeConfig(
            $componentPath,
            ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getDefaultStoreView()->getWebsiteId()
        );
    }

    /**
     * To get custom attribute
     *
     * @return string
     */
    public function getCustomAttribute()
    {
        $targetOrder = $this->getTargetOrderId();
        $msg = 'Order Sync Is Pending';
        return isset($targetOrder[0][self::TARGET_ORDER_ID]) ? $targetOrder[0][self::TARGET_ORDER_ID] : $msg;
    }

    /**
     * Checks custom attribute
     *
     * @return boolean
     */
    public function checkCustomAttribute()
    {
        $targetOrder = $this->getTargetOrderId();
        $targetOrderId = isset($targetOrder[0][self::TARGET_ORDER_ID]) ? $targetOrder[0][self::TARGET_ORDER_ID] : '';
        $origin = isset($targetOrder[0]['origin']) ? $targetOrder[0]['origin'] : '';
        if ($targetOrderId == "" && $origin === null) {
            return false;
        }
        return true;
    }

    /**
     * Check if module is enabled
     *
     * @return boolean
     */
    public function isI95DevConnectEnabled()
    {
        return $this->basedata->isEnabledInAnyWebsite();
    }

    // Get enabled websites
    public function getEnabledWebsiteIds()
    {
       return $this->basedata->getEnabledWebsiteIds();
    }

    /**
     * Prepare the Custom Information
     *
     * @return array
     */
    public function getCustomOrderData()
    {
        $orderData = [];
        $component = $this->getComponent();
        $invoiceData = $this->getTargetInvoiceData();
        $shipmentData = $this->getTargetShipmentData();
        if ($invoiceData['value'] !== '' && strlen($invoiceData['value']) > 1) {
            $orderData[$invoiceData['sortOrder']] = [
                'label' => $invoiceData['label'],
                'value' => $this->escaper->escapeHtml($invoiceData['value'], ['br']),
            ];
        }

        if ($shipmentData['value'] !== '' && strlen($shipmentData['value']) > 1 && $component != 'GP') {
            $orderData[$shipmentData['sortOrder']] = [
                'label' => $shipmentData['label'],
                'value' => $this->escapeHtml($shipmentData['value'], ['br']),
            ];
        }

        ksort($orderData, SORT_NUMERIC);
        return $orderData;
    }

    /**
     * Get Target Invoice data
     *
     * @return array
     */
    public function getTargetInvoiceData()
    {
        $targetInvoiceIds = $this->getTargetInvoiceId();
        $maxIdsToShow = 3;
        $counter = 0;
        $targetInvoiceId = '';
        if (count($targetInvoiceIds) > 1) {
            foreach ($targetInvoiceIds as $invoiceId) {
                $counter++;
                if ($invoiceId !== null) {
                    $targetInvoiceId .= $invoiceId . ",";
                }
                if ($counter == $maxIdsToShow) {
                    $targetInvoiceId .= "<br>";
                    $counter = 0;
                }
            }
            $targetInvoiceId = trim($targetInvoiceId, ",");
        } else {
            $targetInvoiceId = isset($targetInvoiceIds[0]) ? $targetInvoiceIds[0] : "";
        }

        return [
            'sortOrder' => 0,
            'label' => __('Target Invoice ID'),
            'value' => $targetInvoiceId
        ];
    }

    /**
     * Get Target Shipment Data
     *
     * @return array
     */
    public function getTargetShipmentData()
    {
        $targetShipmentIds = $this->getTargetShipmentId();
        $_maxIdsToShow = 3;
        $counter = 0;
        $targetShipmentId = '';
        if (count($targetShipmentIds) > 1) {
            foreach ($targetShipmentIds as $shipmentId) {
                $counter++;
                $targetShipmentId .= $shipmentId . ",";
                if ($counter == $_maxIdsToShow) {
                    $targetShipmentId .= "<br>";
                    $counter = 0;
                }
            }
            $targetShipmentId = trim($targetShipmentId, ",");
        } else {
            $targetShipmentId = isset($targetShipmentIds[0]) ? $targetShipmentIds[0] : "";
        }

        return [
            'sortOrder' => 1,
            'label' => __('Target Shipment ID'),
            'value' => $targetShipmentId
        ];
    }
}

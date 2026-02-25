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
use I95DevConnect\MessageQueue\Model\SalesInvoiceFactory;
use I95DevConnect\MessageQueue\Model\SalesOrderFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Observer class for sales order invoice after save
 */
class SalesOrderInvoiceSaveAfterObserver implements ObserverInterface
{
    public const MAGLOGNAME = 'MagentoToERP';
    public const ERPLOGNAME = 'ERPToMagento';
    public const I95EXC = 'i95devApiException';
    public const PAYMENTMETHOD = 'checkmo';

    /**
     * @var Data
     */
    public $data;

    /**
     * @var Order
     */
    public $salesOrderModel;

    /**
     * @var customSalesOrder
     */
    public $customSalesInvoice;

    /**
     * @var customSalesOrder
     */
    public $customSalesOrder;

    /**
     * @var Http
     */
    public $request;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var object
     */
    public $logger;

    /**
     * SalesOrderInvoiceSaveAfterObserver constructor.
     *
     * @param Data $data
     * @param Order $salesOrderModel
     * @param SalesInvoiceFactory $customInvoiceOrder
     * @param SalesOrderFactory $customSalesOrder
     * @param Http $request
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Data $data,
        Order $salesOrderModel,
        SalesInvoiceFactory $customInvoiceOrder,
        SalesOrderFactory $customSalesOrder,
        Http $request,
        StoreManagerInterface $storeManager
    ) {

        $this->data = $data;
        $this->salesOrderModel = $salesOrderModel;
        $this->customSalesInvoice = $customInvoiceOrder;
        $this->customSalesOrder = $customSalesOrder;
        $this->request = $request;
        $this->storeManager = $storeManager;
    }

    /**
     * Save custom invoice
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
            $invoice = $observer->getEvent()->getInvoice();
            $orderId = $invoice->getOrderId();
            $isTotalQtyInvoiced = $this->isTotalOrderQtyInvoiced($orderId);
            if ($isTotalQtyInvoiced) {
                $this->createCustomSalesInvoice($invoice);
            }
        } catch (LocalizedException $ex) {
            $this->logger->createLog(__METHOD__, $ex->getMessage(), self::I95EXC, 'critical');
        }
    }

    /**
     * Create invoice entry in custom tables of i95dev
     *
     * @param Object $invoice
     * @param int|null $qty
     *
     * @author Arushi Bansal
     */
    protected function createCustomSalesInvoice($invoice, int $qty = null)
    {
        $invoiceModel = $this->customSalesInvoice->create();
        $loadCustomInvoice = $this->customSalesInvoice->create()
                ->load($invoice->getIncrementId(), 'source_invoice_id');
        if ($loadCustomInvoice->getId()) {
            $invoiceModel->setId($loadCustomInvoice->getId());
        }
        if ($qty !== 0) {
            $invoiceModel->setTargetInvoicedQty($qty);
        }
        $invoiceModel->setSourceInvoiceId($invoice->getIncrementId());
        $invoiceModel->setCreatedDt($invoice->getCreatedAt());
        $invoiceModel->setUpdatedDt($invoice->getUpdatedAt());
        $invoiceModel->setUpdateBy('Magento');
        $invoiceModel->save();
    }
    /**
     * Check all items qty invoiced or not for order
     *
     * @param  int $orderId
     * @return boolean
     */
    private function isTotalOrderQtyInvoiced($orderId)
    {
        $is_enabled = $this->data->isEnabled();
        if (!$is_enabled) {
            return false;
        }
        $order = $this->salesOrderModel->load($orderId);
        $orderedQty = $order->getData('total_qty_ordered');
        $totalQty = 0;
        foreach ($order->getInvoiceCollection() as $invoice) {
            $qtyInvoiced = $invoice->getData('total_qty');
            $totalQty = $totalQty + $qtyInvoiced;
        }
        $nonInvoiceTypes = ['configurable'];
        $nonInvoicedQtyArray = [];

        /* get parent orderQty */
        foreach ($order->getAllItems() as $orderedItem) {
            if (in_array($orderedItem->getProductType(), $nonInvoiceTypes)) {
                $nonInvoicedQtyArray[] = $orderedItem->getQtyOrdered();
            }
        }
        $nonInvoicedQty = array_sum($nonInvoicedQtyArray);
        $totalQty = $totalQty - $nonInvoicedQty;
        $orderedQty = (int) $orderedQty;
        if ($orderedQty == $totalQty) {
            return true;
        }
        return false;
    }
}

<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Helper;

use Exception;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;
use I95DevConnect\MessageQueue\Model\SalesInvoiceFactory;
use I95DevConnect\MessageQueue\Model\SalesOrderFactory;
use I95DevConnect\MessageQueue\Model\SalesShipmentFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Framework\Api\FilterFactory;
use Magento\Framework\Api\Search\FilterGroupFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\InvoiceSearchResultInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory;
use Magento\Sales\Api\InvoiceRepositoryInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterfaceFactory;
use Magento\Sales\Model\Order\ItemFactory;

/**
 * Class to get ERP order status
 */
class ErpOrderStatus extends AbstractHelper
{
    public const PARENTITEM = "parent_item";
    public const INTIAL = 'New';
    public const COMPLETE = 'Complete';
    public const INVOICED = 'Invoiced';
    public const SHIPPED = 'Shipped';
    public const PARTIALINVOICE = 'Partially Invoiced';
    public const PARTIALSHIP = 'Partially Shipped';
    public const CANCEL = 'Canceled';

    /**
     * @var Data
     */
    public $baseHelperData;

    /**
     * @var LoggerInterfaceFactory
     */
    public $logger;

    /**
     *
     * @var SalesInvoiceFactory
     */
    public $customInvoiceFactory;

    /**
     *
     * @var SalesShipmentFactory
     */
    public $customShipment;

    /**
     *
     * @var ItemFactory
     */
    public $orderItem;

    /**
     *
     * @var Data
     */
    public $data;

    /**
     * @var TransactionSearchResultInterfaceFactory
     */
    public $transactionsFactory;

    /**
     * @var OrderRepositoryInterfaceFactory
     */
    public $orderRepository;

    /**
     * @var SearchCriteriaInterface
     */
    public $searchCriteria;

    /**
     * @var FilterGroupFactory
     */
    public $filterGroup;

    /**
     * @var FilterFactory
     */
    public $filter;

    /**
     * @var InvoiceRepositoryInterfaceFactory
     */
    public $invoiceRepository;

    /**
     * @var ProductRepositoryInterfaceFactory
     */
    public $productRepository;

    /**
     * @var SalesOrderFactory
     */
    public $customSalesOrder;

    /**
     * ErpOrderStatus constructor.
     *
     * @param SalesInvoiceFactory $customInvoiceFactory
     * @param SalesShipmentFactory $customShipment
     * @param ItemFactory $orderItem
     * @param TransactionSearchResultInterfaceFactory $transactionsFactory
     * @param OrderRepositoryInterfaceFactory $orderRepository
     * @param SearchCriteriaInterface $searchCriteria
     * @param FilterGroupFactory $filterGroup
     * @param FilterFactory $filter
     * @param InvoiceRepositoryInterfaceFactory $invoiceRepository
     * @param ProductRepositoryInterfaceFactory $productRepository
     * @param SalesOrderFactory $customSalesOrder
     * @param Data $baseHelperData
     * @param LoggerInterfaceFactory $logger
     * @param Context $context
     */
    public function __construct(//NOSONAR
        SalesInvoiceFactory $customInvoiceFactory,
        SalesShipmentFactory $customShipment,
        ItemFactory $orderItem,
        TransactionSearchResultInterfaceFactory $transactionsFactory,
        OrderRepositoryInterfaceFactory $orderRepository,
        SearchCriteriaInterface $searchCriteria,
        FilterGroupFactory $filterGroup,
        FilterFactory $filter,
        InvoiceRepositoryInterfaceFactory $invoiceRepository,
        ProductRepositoryInterfaceFactory $productRepository,
        SalesOrderFactory $customSalesOrder,
        Data $baseHelperData,
        LoggerInterfaceFactory $logger,
        Context $context
    ) {
        $this->logger = $logger;
        $this->customInvoiceFactory = $customInvoiceFactory;
        $this->customShipment = $customShipment;
        $this->orderItem = $orderItem;
        $this->transactionsFactory = $transactionsFactory;
        $this->orderRepository = $orderRepository;
        $this->searchCriteria = $searchCriteria;
        $this->filterGroup = $filterGroup;
        $this->filter = $filter;
        $this->invoiceRepository = $invoiceRepository;
        $this->productRepository = $productRepository;
        $this->customSalesOrder = $customSalesOrder;
        $this->baseHelperData = $baseHelperData;
        parent::__construct($context);
    }

    /**
     * Get parent of simple products
     *
     * @param obj $order
     * @return array
     */
    public function getParentSimpleItems($order)
    {
        $parentSimpleItems = [];
        foreach ($order['items'] as $item) {
            if (isset($item[self::PARENTITEM]) && $item[self::PARENTITEM]['product_type'] !== 'bundle') {
                $parentSimpleItems[] = $item[self::PARENTITEM]['item_id'];
            }
        }
        return $parentSimpleItems;
    }

    /**
     * Get ERP order status
     *
     * @param string $orderId
     * @return string
     */
    public function getERPOrderStatus($orderId)
    {
        $status = [];
        $shippedOrderedQty = 0;
        $invoiceOrderedQty = 0;
        $cancelOrderedQty = 0;
        $shippedQty = 0;
        $order = $this->getOrder($orderId);
        $parentSimpleItems = $this->getParentSimpleItems($order);
        $invoices = $this->getInvoices($order->getEntityId());
        $targetInvoicedQty = $this->getTargetInvoicedQty($invoices);
        foreach ($order->getItems() as $item) {
            $shippingQty = $this->getShippedQty($item, $parentSimpleItems, $shippedOrderedQty, $shippedQty);
            $shippedOrderedQty = $shippingQty['shippedOrderedQty'];
            $shippedQty = $shippingQty['shippedQty'];
            $invoiceOrderedQty = $this->getInvoicedQty($item, $parentSimpleItems, $invoiceOrderedQty);
            $cancelOrderedQty = $this->getCanceledQty($item, $parentSimpleItems, $cancelOrderedQty);
        }
        $invoicedQty = (int)$targetInvoicedQty;
        $status = $this->generateStatus(
            $shippedOrderedQty,
            $shippedQty,
            $invoiceOrderedQty,
            $invoicedQty,
            $cancelOrderedQty
        );

        return implode(',', $status);
    }

    /**
     * Get shipped quantity
     *
     * @param object $item
     * @param object $parentSimpleItems
     * @param int $shippedOrderedQty
     * @param int $shippedQty
     * @return array
     */
    public function getShippedQty($item, $parentSimpleItems, $shippedOrderedQty, $shippedQty)
    {
        $nonShippedTypes = ['virtual', 'downloadable'];
        if (!in_array($item->getProductType(), $nonShippedTypes) &&
            !in_array($item->getItemId(), $parentSimpleItems)
        ) {
            $shippedOrderedQty = $this->getShippedOrderedQty($item, $shippedOrderedQty);
            $shippedQty = $this->getItemsShippedQty($item, $shippedQty);
        }
        if (in_array($item->getProductType(), $nonShippedTypes)) {
            $shippedOrderedQty += (int) $item->getQtyOrdered();
            $shippedQty += (int) $item->getQtyInvoiced();
        }
        return ['shippedOrderedQty' => $shippedOrderedQty, 'shippedQty' => $shippedQty];
    }

    /**
     * Get shipped ordered quantity
     *
     * @param object $item
     * @param int $shippedOrderedQty
     * @return int
     */
    public function getShippedOrderedQty($item, $shippedOrderedQty)
    {
        if ($item->getParentItem() && $item->getParentItem()->getProductType() === 'bundle') {
            $options = $item->getParentItem()->getProductOptions();
            $shipmentType = $options['shipment_type'] ?? 0;
            if ($shipmentType == \Magento\Catalog\Model\Product\Type\AbstractType::SHIPMENT_TOGETHER) {
                $shippedOrderedQty += 0;
            } else {
                $shippedOrderedQty += (int)$item->getQtyOrdered();
            }
        } elseif ($item->getProductType() === 'bundle') {
            $options = $item->getProductOptions();
            $shipmentType = $options['shipment_type'] ?? 0;
            if ($shipmentType == \Magento\Catalog\Model\Product\Type\AbstractType::SHIPMENT_TOGETHER) {
                $shippedOrderedQty += (int)$item->getQtyOrdered();
            } else {
                $shippedOrderedQty += 0;
            }
        } else {
            $shippedOrderedQty += (int)$item->getQtyOrdered();
        }
        return $shippedOrderedQty;
    }

    /**
     * Get shipped items quantity
     *
     * @param object $item
     * @param int $shippedQty
     * @return int
     */
    public function getItemsShippedQty($item, $shippedQty)
    {
        if ($item->getParentItem() && $item->getParentItem()->getProductType() !== 'bundle') {
            $shippedQty += (int)$item[self::PARENTITEM]['qty_shipped'];
        } elseif ($item->getParentItem() && $item->getParentItem()->getProductType() === 'bundle') {
            $options = $item->getParentItem()->getProductOptions();
            $shipmentType = $options['shipment_type'] ?? 0;
            if ($shipmentType == \Magento\Catalog\Model\Product\Type\AbstractType::SHIPMENT_TOGETHER) {
                $shippedQty += 0;
            } else {
                $shippedQty += (int)$item->getQtyShipped();
            }
        } elseif ($item->getProductType() === 'bundle') {
            $options = $item->getProductOptions();
            $shipmentType = $options['shipment_type'] ?? 0;
            if ($shipmentType == \Magento\Catalog\Model\Product\Type\AbstractType::SHIPMENT_TOGETHER) {
                $shippedQty += (int)$item->getQtyShipped();
            } else {
                $shippedQty += 0;
            }
        } else {
            $shippedQty += (int)$item->getQtyShipped();
        }
        return $shippedQty;
    }

    /**
     * Get invoice qty
     *
     * @param object $item
     * @param object $parentSimpleItems
     * @param int $invoiceOrderedQty
     * @return int
     */
    public function getInvoicedQty($item, $parentSimpleItems, $invoiceOrderedQty)
    {
        if (!in_array($item->getItemId(), $parentSimpleItems)) {
            if ($item->getParentItem() && $item->getParentItem()->getProductType() === 'bundle') {
                $invoiceOrderedQty += 0;
            } else {
                $invoiceOrderedQty += (int)$item->getQtyOrdered();
            }
        }
        return $invoiceOrderedQty;
    }

    /**
     * Get cancel qty
     *
     * @param object $item
     * @param array $parentSimpleItems
     * @param int $cancelOrderedQty
     * @return int
     */
    public function getCanceledQty($item, $parentSimpleItems, $cancelOrderedQty)
    {
        if (!in_array($item->getItemId(), $parentSimpleItems)) {
            if ($item->getParentItem() && $item->getParentItem()->getProductType() !== 'bundle') {
                $cancelOrderedQty += (int)$item->getParentItem()->getQtyCanceled();
            } elseif ($item->getParentItem() && $item->getParentItem()->getProductType() === 'bundle') {
                $options = $item->getParentItem()->getProductOptions();
                $shipmentType = $options['shipment_type'] ?? 0;
                $cancelOrderedQty = $this->getBundleChildCancelledQty($shipmentType, $item, $cancelOrderedQty);
            } elseif ($item->getProductType() === 'bundle') {
                $options = $item->getProductOptions();
                $shipmentType = $options['shipment_type'] ?? 0;
                $cancelOrderedQty = $this->getBundleCancelledQty($shipmentType, $item, $cancelOrderedQty);
            } else {
                $cancelOrderedQty += (int)$item->getQtyCanceled();
            }
        }
        return $cancelOrderedQty;
    }

    /**
     * Get bundle child cancelled qty
     *
     * @param int $shipmentType
     * @param mixed $item
     * @param int $cancelOrderedQty
     * @return int $cancelOrderedQty
     */
    public function getBundleChildCancelledQty($shipmentType, $item, $cancelOrderedQty)
    {
        if ($shipmentType == \Magento\Catalog\Model\Product\Type\AbstractType::SHIPMENT_TOGETHER) {
            $cancelOrderedQty += 0;
        } else {
            $cancelOrderedQty += (int) $item->getQtyCanceled();
        }
        return $cancelOrderedQty;
    }

    /**
     * Get bundle cancelled qty
     *
     * @param int $shipmentType
     * @param mixed $item
     * @param int $cancelOrderedQty
     * @return int $cancelOrderedQty
     */
    public function getBundleCancelledQty($shipmentType, $item, $cancelOrderedQty)
    {
        if ($shipmentType == \Magento\Catalog\Model\Product\Type\AbstractType::SHIPMENT_TOGETHER) {
            $cancelOrderedQty += (int)$item->getQtyCanceled();
        } else {
            $cancelOrderedQty += 0;
        }
        return $cancelOrderedQty;
    }

    /**
     * Generate status
     *
     * @param int $shippedOrderedQty
     * @param int $shippedQty
     * @param int $invoiceOrderedQty
     * @param int $invoicedQty
     * @param int $cancelOrderedQty
     * @return string
     */
    public function generateStatus($shippedOrderedQty, $shippedQty, $invoiceOrderedQty, $invoicedQty, $cancelOrderedQty)
    {
        $status = $this->setFullOrderStatus($shippedOrderedQty, $shippedQty, $invoiceOrderedQty, $invoicedQty);
        if ($cancelOrderedQty !== 0) {
            $status = $this->setCancelOrderStatus(
                $shippedOrderedQty,
                $shippedQty,
                $invoiceOrderedQty,
                $invoicedQty,
                $cancelOrderedQty
            );
        }
        if ($shippedOrderedQty != $shippedQty && $invoiceOrderedQty == $invoicedQty) {
            $status[] = self::INVOICED;
            if ($shippedQty !== 0) {
                $status[] = self::PARTIALSHIP;
            }
        }
        return $this->setPartialOrderStatus(
            $status,
            $shippedOrderedQty,
            $shippedQty,
            $invoiceOrderedQty,
            $invoicedQty,
            $cancelOrderedQty
        );
    }

    /**
     * Set partial status to order
     *
     * @param bool $status
     * @param int $shippedOrderedQty
     * @param int $shippedQty
     * @param int $invoiceOrderedQty
     * @param int $invoicedQty
     * @param int $cancelOrderedQty
     * @return mixed
     */
    public function setPartialOrderStatus(
        $status,
        $shippedOrderedQty,
        $shippedQty,
        $invoiceOrderedQty,
        $invoicedQty,
        $cancelOrderedQty
    ) {
        if ($shippedOrderedQty != $shippedQty && $invoiceOrderedQty != $invoicedQty) {
            if ($shippedQty !== 0 && $cancelOrderedQty === 0) {
                $status[] = self::PARTIALSHIP;
            }
            if ($invoicedQty !== 0 && $cancelOrderedQty === 0) {
                $status[] = self::PARTIALINVOICE;
            }
            if ($shippedQty === 0 && $invoicedQty === 0 && $cancelOrderedQty === 0) {
                $status[] = self::INTIAL;
            }
        }
        return $status;
    }

    /**
     * Set full order status
     *
     * @param int $shippedOrderedQty
     * @param int $shippedQty
     * @param int $invoiceOrderedQty
     * @param int $invoicedQty
     * @return array
     */
    public function setFullOrderStatus($shippedOrderedQty, $shippedQty, $invoiceOrderedQty, $invoicedQty)
    {
        $status = [];
        if ($shippedOrderedQty == $shippedQty && $invoiceOrderedQty == $invoicedQty) {
            $status[] = self::COMPLETE;
        }

        if ($shippedOrderedQty == $shippedQty && $invoiceOrderedQty != $invoicedQty) {
            $status[] = self::SHIPPED;
            if ($invoicedQty !== 0) {
                $status[] = self::PARTIALINVOICE;
            }
        }

        return $status;
    }

    /**
     * Set cancel order status
     *
     * @param int $shippedOrderedQty
     * @param int $shippedQty
     * @param int $invoiceOrderedQty
     * @param int $invoicedQty
     * @param int $cancelOrderedQty
     * @return array
     */
    public function setCancelOrderStatus(
        $shippedOrderedQty,
        $shippedQty,
        $invoiceOrderedQty,
        $invoicedQty,
        $cancelOrderedQty
    ) {
        $status = [];
        if ($invoiceOrderedQty == $cancelOrderedQty) {
            $status[] = self::CANCEL;
        }

        if ($shippedQty != 0 && $invoicedQty != 0 &&
            $shippedOrderedQty == $shippedQty + $cancelOrderedQty &&
            $invoiceOrderedQty == $invoicedQty + $cancelOrderedQty
        ) {
            $status[] = self::COMPLETE;
        }

        return $status;
    }

    /**
     * Get target invoices
     *
     * @param obj $invoices
     * @return int
     */
    public function getTargetInvoicedQty($invoices)
    {
        $targetInvoicedQty = 0;
        if (!empty($invoices)) {
            foreach ($invoices as $inv) {
                $sourceInvoiceId = $inv->getIncrementId();
                $customInvoice = $this->customInvoiceFactory->create()->getCollection()
                    ->addFieldToSelect('target_invoiced_qty')
                    ->addFieldToFilter('source_invoice_id', $sourceInvoiceId);
                $customInvoice->getSelect()->limit(1);

                $customInvoice = $customInvoice->getFirstItem();
                $targetInvoicedQty += $customInvoice->getTargetInvoicedQty();
            }
        }

        return $targetInvoicedQty;
    }

    /**
     * Get invoices
     *
     * @param string $orderId
     *
     * @return InvoiceSearchResultInterface|null
     */
    public function getInvoices($orderId)
    {
        $filter[0] = $this->filter->create()->setField('order_id')->setValue($orderId)->setConditionType('eq');
        $filterGroup[0] = $this->filterGroup->create()->setFilters($filter);
        $this->searchCriteria->setFilterGroups($filterGroup);
        $invoices = $this->invoiceRepository->create()->getList($this->searchCriteria);

        if ($invoices->getSize() > 0) {
            return $invoices;
        } else {
            return null;
        }
    }

    /**
     * Get Order data
     *
     * @param string $orderId
     * @return OrderInterface|null
     */
    public function getOrder($orderId)
    {
        $result = $this->orderRepository->create()->get($orderId);

        if ($result instanceof OrderInterface) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     * Fetch product by product sku
     *
     * @param string $sku
     * @return ProductInterface|null
     */
    public function getProduct($sku)
    {
        $filter[0] = $this->filter->create()->setField('sku')->setValue($sku)->setConditionType('eq');
        $filterGroup[0] = $this->filterGroup->create()->setFilters($filter);
        $this->searchCriteria->setFilterGroups($filterGroup);
        $products = $this->productRepository->create()->getList($this->searchCriteria);

        if ($products->getTotalCount() > 0) {
            foreach ($products->getItems() as $product) {
                return $product;
            }
        }
        return null;
    }

    /**
     * Get order data by order id
     *
     * @param string $orderId
     * @return array
     */
    public function getOrderByIncrementId($orderId)
    {
        $filter[0] = $this->filter->create()->setField('increment_id')->setValue($orderId)->setConditionType('eq');
        $filterGroup[0] = $this->filterGroup->create()->setFilters($filter);
        $this->searchCriteria->setFilterGroups($filterGroup);
        $orders = $this->orderRepository->create()->getList($this->searchCriteria);

        if ($orders->getSize() > 0) {
            foreach ($orders as $order) {
                return $order;
            }
        }
        return null;
    }

    /**
     * Checking order is able to sync or not
     *
     * @param string $orderId
     * @return string
     * @author Sravani Polu
     * Removed API call and used existing interface call to get order info.
     */
    public function isOrderSyncable($orderId)
    {
        $order = $this->getOrderByIncrementId($orderId);
        $message = '';
        if ($order !== null) {
            if ($order->getStatus() == "canceled") {
                $message = "Order with Cancel status are not transferred";
            }

            if ($order->getStatus() == "fraud") {
                $message = "Order with Fraud status are not transferred";
            }

            if ($order->getPayment()->getMethod() == "authorizenet_directpost") {
                $transactions = $this->transactionsFactory->create()->addOrderIdFilter(
                    $order->getEntityId()
                )->getItems();
                if (empty($transactions)) {
                    $message = "There was some issue with authorize.net (No transaction id exists)";
                }
            }
        }
        if ($message !== '') {
            return $message;
        }
        return null;
    }

    /**
     * Update i95dev order status during invoice sync
     *
     * @param int $customSalesOrderId
     * @param int $entityId
     * @throws Exception
     * @createdBy Arushi Bansal
     */
    public function updateCustomOrderStatus($customSalesOrderId, $entityId)
    {
        try {
            // Update i95dev order status during invoice sync
            $customOrderModel = $this->customSalesOrder->create()->load(
                $customSalesOrderId,
                "source_order_id"
            );
            $customOrderModel->setUpdatedDt($this->baseHelperData->date->gmtDate());
            $targetOrderStatus = $this->getERPOrderStatus(
                $entityId
            );
            $customOrderModel->setTargetOrderStatus($targetOrderStatus);
            $customOrderModel->save();
        } catch (LocalizedException $e) {
            $this->logger->create()->createLog(
                __METHOD__,
                $e->getMessage(),
                LoggerInterface::INFO,
                'info'
            );
        }
    }
}

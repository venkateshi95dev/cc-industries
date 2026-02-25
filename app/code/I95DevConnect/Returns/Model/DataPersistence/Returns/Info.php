<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_Returns
 */

namespace I95DevConnect\Returns\Model\DataPersistence\Returns;

use Exception;
use I95DevConnect\MessageQueue\Helper\Generic;
use I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Forward\BillingAddress;
use I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Forward\Customer;
use I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Forward\ShippingAddress;
use I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Forward\Payment\PaymentInfo;
use I95DevConnect\MessageQueue\Model\SalesOrderFactory;
use I95DevConnect\Returns\Helper\Data;
use I95DevConnect\Returns\Model\ReturnsItemsEntityFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Manager;
use Magento\Rma\Model\ResourceModel\Item\CollectionFactory;
use Magento\Rma\Model\RmaRepository;
use Magento\Sales\Api\OrderRepositoryInterfaceFactory;
use Magento\Sales\Model\OrderRepository;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class for preparing Returns result data to be sent to ERP
 */
class Info
{
    /**
     * @var \I95DevConnect\MessageQueue\Helper\Data
     */
    public $dataHelper;
    /**
     * @var Manager
     */
    public $eventManager;
    /**
     * @var Customer
     */
    public $customerEntity;
    /**
     * @var BillingAddress
     */
    public $billingAddressEntity;
    /**
     * @var ShippingAddress
     */
    public $shippingAddressEntity;
    /**
     * @var PaymentInfo
     */
    public $orderPaymentEntity;
    /**
     * @var OrderRepository
     */
    public $orderRepo;
    /**
     * @var int
     */
    public $orderId;
    /**
     * @var object
     */
    public $orderDetails;
    /**
     * @var object
     */
    public $ordItems;
    /**
     * @var object
     */
    public $orderInfom;
    /**
     * @var RmaRepository
     */
    public $returnrepo;
    /**
     * @var SearchCriteriaBuilder
     */
    public $searchCriteriaBuilder;
    /**
     * @var Generic
     */
    public $genericHelper;
    /**
     * @var OrderRepositoryInterfaceFactory
     */
    public $orderRepoIntf;
    /**
     * @var CollectionFactory
     */
    // @codingStandardsIgnoreLine
    public $_itemsFactory;
    /**
     * @var Data
     */
    public $returnHelper;
    /**
     * @var \I95DevConnect\MessageQueue\Model\I95DevInvoiceHistoryFactory
     */
    // @codingStandardsIgnoreLine
    public $_salesInvHistory;

    /**
     * @var ReturnsItemsEntityFactory $returnsItemEntity
     */
    protected $returnsItemEntity;
    /**
     * @var SalesOrderFactory
     */
    protected $salesOrderCollection;

    /**
     * @var \Magento\Rma\Helper\Eav
     */
    // @codingStandardsIgnoreLine
    protected $_rmaEav;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var int
     */
    protected $returnId;

    /**
     * @var array
     */
    protected $infoData;

    /**
     * @var float
     */
    protected $refundAmount = 0;

    /**
     * Info constructor.
     * @param Manager $eventManager
     * @param \I95DevConnect\MessageQueue\Helper\Data $dataHelper
     * @param RmaRepository $returnrepo
     * @param Collection $salesOrderCollection
     * @param Generic $genericHelper
     * @param OrderRepository $orderRepo
     * @param Customer $customerEntity
     * @param BillingAddress $billingAddressEntity
     * @param ShippingAddress $shippingAddressEntity
     * @param PaymentInfo $orderPaymentEntity
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterfaceFactory $orderRepoIntf
     * @param CollectionFactory $itemsFactory
     * @param Data $returnHelper
     * @param \I95DevConnect\MessageQueue\Model\I95DevInvoiceHistoryFactory $salesInvHistory
     * @param \Magento\Rma\Helper\Eav $rmaEav
     */
    public function __construct(
        Manager $eventManager,
        \I95DevConnect\MessageQueue\Helper\Data $dataHelper,
        RmaRepository $returnrepo,
        SalesOrderFactory $salesOrderCollection,
        Generic $genericHelper,
        OrderRepository $orderRepo,
        Customer $customerEntity,
        BillingAddress $billingAddressEntity,
        ShippingAddress $shippingAddressEntity,
        PaymentInfo $orderPaymentEntity,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterfaceFactory $orderRepoIntf,
        CollectionFactory $itemsFactory,
        Data $returnHelper,
        \I95DevConnect\MessageQueue\Model\I95DevInvoiceHistoryFactory $salesInvHistory,
        \Magento\Rma\Helper\Eav $rmaEav
    ) {
        $this->dataHelper = $dataHelper;
        $this->returnrepo = $returnrepo;
        $this->eventManager = $eventManager;
        $this->genericHelper = $genericHelper;
        $this->orderRepo = $orderRepo;
        $this->customerEntity = $customerEntity;
        $this->billingAddressEntity = $billingAddressEntity;
        $this->shippingAddressEntity = $shippingAddressEntity;
        $this->orderPaymentEntity = $orderPaymentEntity;
        $this->salesOrderCollection = $salesOrderCollection;
        $this->productRepository = $productRepository;
        $this->orderRepoIntf = $orderRepoIntf;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_itemsFactory = $itemsFactory;
        $this->returnHelper = $returnHelper;
        $this->_salesInvHistory = $salesInvHistory;
        $this->_rmaEav = $rmaEav;
    }

    /**
     * Get information
     *
     * @param int $returnId
     * @param string $entityCode
     * @param string $erpCode
     * @return array
     */
    public function getInfo($returnId, $entityCode, $erpCode = null) // NOSONAR
    {
        $this->returnId = $returnId;
        $this->infoData = $this->getReturnInfo($returnId);
        $returnInfoEvent = "erpconnect_forward_returninfo";
        $this->eventManager->dispatch($returnInfoEvent, ['currentObject' => $this]);
        return $this->infoData;
    }

    /**
     * Get return information
     *
     * @param int $returnId
     * @return array
     */
    public function getReturnInfo($returnId)
    {
        $returnEntity = [];
        $customerInfo = [];
        $returnEntityData = $this->returnrepo->get($returnId);
        $customerId = $returnEntityData->getCustomerId();
        $returnEntity['targetCustomerId'] = $this->getTargetCustomerId($customerId);
        $customerInfo['name'] = $returnEntityData->getData('customer_name');
        $customerInfo['email'] = $returnEntityData->getData('customer_email');
        $customerInfo['sourceId'] = $returnEntityData->getData('customer_id');
        $this->orderDetails = $this->orderRepo->get($returnEntityData->getOrderId());

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $returnEntityData->getOrderId(), 'eq')
            ->create();
        $orderCollection = $this->orderRepoIntf->create()->getList($searchCriteria);
        $this->orderInfom = $orderCollection->getFirstItem();

        $returnEntity['returnItems'] = $this->getReturnItems($returnEntityData);
        $returnEntity['refundAmount'] = $this->refundAmount;
        $returnEntity['sourceId'] = $returnId;

        $customer = $this->customerEntity->getCustomerEntity($this->orderDetails);
        $ordereCollection = $this->salesOrderCollection->create()->getCollection()
            ->addFieldToFilter('source_order_id', $this->orderDetails->getIncrementId());
        $ordereCollection->getSelect()->limit(1);
        $returnEntity['targetOrderId'] = $ordereCollection->getFirstItem()->getTargetOrderId() ?? null;
        $sInvHisty = $this->_salesInvHistory->create()->getCollection()
            ->addFieldToFilter('target_order_id', $returnEntity['targetOrderId'])->getFirstItem();
        $returnEntity['targetInvoiceId'] = $sInvHisty->getTargetInvoiceId() ?? '';
        $returnEntity['status'] = $returnEntityData->getStatus();

        $billingAddress = $this->billingAddressEntity->getBillingAddress($this->orderDetails);
        $shippingAddress = $this->shippingAddressEntity->getShippingAddress($this->orderDetails);
        $returnEntity['shippingMethod'] = $shippingAddress['shippingMethod'];
        unset($shippingAddress['shippingMethod']);
        $orderPayment = $this->orderPaymentEntity->getOrderPayment($this->orderDetails);
        if (!empty($billingAddress)) {
            $billingAddress['targetCustomerId'] = $customer['targetCustomerId'];
            $returnEntity['targetBillingAddressId'] = $billingAddress['targetId'];
        }
        if (!empty($shippingAddress)) {
            $returnEntity['targetShippingAddressId'] = $shippingAddress['targetId'];
        }

        $returnEntity['customer'] = $customerInfo;
        $returnEntity['billingAddress'] = $billingAddress;
        $returnEntity['shippingAddress'] = $shippingAddress;
        $returnEntity['payment'] = $orderPayment;
        $returnEntity['reference'] = $customerInfo['email'];
        $returnEntity['targetReturnId'] = '';
        $returnEntity['origin'] = 'website';
        return $returnEntity;
    }

    /**
     * Get target customer Id
     *
     * @param int $customerId
     * @return mixed|string
     */
    public function getTargetCustomerId($customerId)
    {
        $customerData = $customerId ? $this->genericHelper->getCustomerById($customerId) : [];
        $targetCustomerId = "";
        if (isset($customerData['custom_attributes'])) {
            foreach ($customerData['custom_attributes'] as $customAttribute) {
                if ($customAttribute['attribute_code'] == "target_customer_id") {
                    $targetCustomerId = $customAttribute['value'];
                }
            }
        }
        return $targetCustomerId;
    }

    /**
     * Get return Items
     *
     * @param object $returnEntityData
     * @return array[]
     */
    public function getReturnItems($returnEntityData)
    {
        $this->refundAmount = 0;
        $returnId = $returnEntityData->getEntityId();
        $returnDetails = $this->_itemsFactory->create();
        $returnDetails->addFieldToFilter('rma_entity_id', $returnId);

        $returnedItems = [];
        $allReturns = [];

        $orderedItems = $this->orderDetails->getAllItems();
        foreach ($orderedItems as $ordItem) {
            if ($ordItem->getBaseOriginalPrice() > 0) {
                $ordSku = strtolower(trim($ordItem->getSku()));
                $this->ordItems[$ordSku]['original_price'] = (float)$ordItem->getBaseOriginalPrice();
                $this->ordItems[$ordSku]['price'] = (float)$ordItem->getBasePrice();
                $discountEntity['discountAmount'] = (float)(abs($ordItem->getBaseDiscountAmount()));
                $discountEntity['discountType'] = 'discount';
                $this->ordItems[strtolower(trim($ordItem->getSku()))]['discount'] = $discountEntity;
            }
        }

        $component = $this->dataHelper->getComponent();
        $compArr = ['AX', 'D365FO'];
        foreach ($returnDetails->getData() as $items) {
            $returnedItems['name'] = $items['product_name'];
            try {
                $product = $this->productRepository->get($items['product_sku']);
                $returnedItems['typeId'] = $product->getTypeId();
                $returnedItems['parentSku'] = $this->returnHelper->getParentSku($product->getId());
                if (in_array($component, $compArr) && $returnedItems['parentSku'] != null) {
                    $returnedItems['variantId'] = $this->getVariantIdBySku($items['product_sku']);
                }
            } catch (Exception $exception) {
                throw new LocalizedException(__($exception->getMessage()));
            }

            $returnedItems['sku'] = $items['product_sku'];
            $returnedItems['price'] = $this->ordItems[strtolower(trim($items['product_sku']))]['original_price'];
            $returnedItems['specialPrice'] = $this->ordItems[strtolower(trim($items['product_sku']))]['price'];
            $returnedItems['discount'] = $this->ordItems[strtolower(trim($items['product_sku']))]['discount'];
            $returnedItems['qtyRequested'] = $items['qty_requested'];
            $returnedItems['qtyAuthorized'] = $items['qty_authorized'];
            $returnedItems['qtyApproved'] = $items['qty_approved'];
            $returnedItems['status'] = $items['status'];
            $selectedReason = $this->getSelectedReason($items['rma_entity_id'], $items['order_item_id']);
            $returnedItems['reason'] = $selectedReason;
            $returnedItems['returnEntityId'] = $items['rma_entity_id'];
            $returnedItems['attributes'] = $this->setProductAttributes($items);
            $allReturns[] = $returnedItems;
            $this->refundAmount += $returnedItems['price'] * $returnedItems['qtyRequested'];
        }
        return $allReturns;
    }

    /**
     * Get selected reason
     *
     * @param int $returnId
     * @param int $itemId
     * @return mixed
     */
    public function getSelectedReason($returnId, $itemId)
    {
        $collection = $this->processReasonLogic($returnId, $itemId, 'select');
        $reasonId = $collection->getFirstItem()->getData()['value'];

        $reason = $this->_rmaEav->getAttributeOptionValues('reason');
        if ($reasonId) {
            return $reason[$reasonId];
        } else {
            $collection = $this->processReasonLogic($returnId, $itemId, 'text');
            return $collection->getFirstItem()->getData()['value'] ?? '';
        }
    }

    /**
     * Processing reason logic
     *
     * @param int $returnId
     * @param int $itemId
     * @param string $fieldType
     * @return mixed
     */
    protected function processReasonLogic($returnId, $itemId, $fieldType)
    {
        if ($fieldType == 'select') {
            $tableName = 'magento_rma_item_entity_int';
            $attributeCode = 'reason';
        } else {
            $tableName = 'magento_rma_item_entity_varchar';
            $attributeCode = 'reason_other';
        }
        $returnDetails = $this->_itemsFactory->create()
            ->addFieldToFilter('rma_entity_id', $returnId)
            ->addFieldToFilter('order_item_id', $itemId)
            ->addFieldToSelect('item_int.value');
        $returnDetails->getSelect()->joinLeft(
            ['item_int' => $returnDetails->getTable($tableName)],
            'e.entity_id = item_int.entity_id',
            [
                'item_int.attribute_id',
                'item_int.entity_id',
                'item_int.value'
            ]
        );
        $returnDetails->getSelect()->joinLeft(
            ['eav_attr' => $returnDetails->getTable('eav_attribute')],
            'item_int.attribute_id = eav_attr.attribute_id',
            [
                'eav_attr.attribute_id',
                'eav_attr.attribute_code'
            ]
        )->where('eav_attr.attribute_code = "' . $attributeCode . '"');

        return $returnDetails;
    }

    /**
     * Set product attribute
     *
     * @param array $orderItem
     * @return array $attributesInfo
     */
    private function setProductAttributes($orderItem)
    {
        $productOptions = json_decode($orderItem['product_options'], 1);
        $attributesInfo = [];
        if (isset($productOptions['attributes_info']) && !empty($productOptions['attributes_info'])) {
            foreach ($productOptions['attributes_info'] as $key => $attributeInfo) {
                $attributesInfo[$key]['attributeCode'] =
                    isset($attributeInfo['label']) ? $attributeInfo['label'] : '';
                $attributesInfo[$key]['attributeValue'] =
                    isset($attributeInfo['value']) ? $attributeInfo['value'] : '';
            }
        }

        return $attributesInfo;
    }

    /**
     * Get product variant Id from SKU. For AX, D365FO
     *
     * @param type $sku
     * @return text
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    private function getVariantIdBySku($sku)
    {
        try {
            $product = $this->productRepository->get($sku);
            if ($product->getvariantId()) {
                return $product->getvariantId();
            }
            return null;
        } catch (NoSuchEntityException $ex) {
            throw new NoSuchEntityException($ex->getMessage());
        } catch (LocalizedException $ex) {
            throw new LocalizedException($ex->getMessage());
        }
    }
}

<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order;

use Exception;
use I95DevConnect\MessageQueue\Api\I95DevResponseInterface;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Helper\Generic;
use I95DevConnect\MessageQueue\Helper\OrderMismatchEmail;
use I95DevConnect\MessageQueue\Model\AbstractDataPersistence;
use I95DevConnect\MessageQueue\Model\ChequeNumber;
use I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Reverse\BillingAddress;
use I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Reverse\Item;
use I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Reverse\Payment;
use I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Reverse\ShippingAddress;
use I95DevConnect\MessageQueue\Model\DataPersistence\Validate;
use I95DevConnect\MessageQueue\Model\SalesOrderFactory;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\CatalogInventory\Observer\ItemsForReindex;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\Data\CurrencyInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterfaceFactory;
use Magento\Sales\Model\Order;
use Magento\Store\Api\StoreWebsiteRelationInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class for creating order in magento
 */
class Create extends QuoteManagement
{
    public const INCREMENT_ID = 'increment_id';
    public const PRICE = 'price';
    public const SHIPPINGMETHOD = 'shippingMethod';
    public const TARGETID = 'targetId';
    public const PAYMENT = 'payment';
    public const I95_OBSERVER_SKIP = 'i95_observer_skip';
    public const ENTITY_ID = 'entity_id';

    /**
     *
     * @var SalesOrderFactory
     */
    public $customSalesOrder;

    /**
     *
     * @var BillingAddress
     */
    public $orderBillingAddress;

    /**
     *
     * @var ShippingAddress
     */
    public $orderShippingAddress;

    /**
     *
     * @var Item
     */
    public $orderItem;

    /**
     *
     * @var Payment
     */
    public $orderPayment;

    /**
     *
     * @var AbstractDataPersistence
     */
    public $abstractDataPersistence;

    /**
     *
     * @var CartManagementInterface
     */
    public $quoteToOrder;

    /**
     *
     * @var Order
     */
    public $orderModel;

    /**
     *
     * @var DateTime
     */
    public $date;

    /**
     *
     * @var OrderMismatchEmail
     */
    public $orderMismatchEmail;

    /**
     *
     * @var Manager
     */
    public $eventManager;

    /**
     *
     * @var I95DevConnect\MessageQueue\Model\ChequeNumber
     */
    public $chequeNumberModel;

    /**
     *
     * @var  ProductRepositoryInterfaceFactory
     */
    public $productRepo;

    /**
     *
     * @var  OrderManagementInterfaceFactory
     */
    public $orderManagement;

    /**
     * Required fields for Order Sync
     * @var array
     */
    public $validateFields = [
        'targetCustomerId' => 'i95dev_order_001',
        self::SHIPPINGMETHOD => 'i95dev_order_004',
        'billingAddress' => 'i95dev_order_033',
        'shippingAddress' => 'i95dev_order_034',
        'orderItems' => 'i95dev_order_010',
        self::PAYMENT => 'i95dev_order_035'
    ];

    /**
     * @var array
     */
    public $orderData = [];

    /**
     *
     * @var array
     */
    public $orderObject = [];

    /**
     *
     * @var int
     */
    public $shippingAmount = 0;

    /**
     * @var bool
     */
    public $validationResult = null;

    /**
     * @var ItemsForReindex
     */
    public $itemsForReindex;

    /**
     * @var StoreWebsiteRelationInterface
     */
    private $storeWebsiteRelation;

    /**
     * @param LoggerInterfaceFactory $logger
     * @param Generic $genericHelper
     * @param QuoteFactory $quoteFactory
     * @param Data $dataHelper
     * @param SalesOrderFactory $customSalesOrder
     * @param CustomerFactory $magentoCustomerModel
     * @param BillingAddress $orderBillingAddress
     * @param ShippingAddress $orderShippingAddress
     * @param Item $orderItem
     * @param Payment $orderPayment
     * @param CustomerInterface $customerApiData
     * @param StoreManagerInterface $storeManager
     * @param CurrencyInterface $currencyApiInterface
     * @param CartManagementInterface $quoteToOrder
     * @param Order $orderModel
     * @param AbstractDataPersistence $abstractDataPersistence
     * @param DateTime $date
     * @param OrderMismatchEmail $orderMismatchEmail
     * @param Manager $eventManager
     * @param ChequeNumber $chequeNumber
     * @param ProductRepositoryInterfaceFactory $productRepo
     * @param OrderManagementInterfaceFactory $orderManagement
     * @param Validate $validate
     * @param ItemsForReindex $itemsForReindex
     * @param StoreWebsiteRelationInterface $storeWebsiteRelation
     */
    public function __construct( // NOSONAR
        LoggerInterfaceFactory $logger,
        Generic $genericHelper,
        QuoteFactory $quoteFactory,
        Data $dataHelper,
        SalesOrderFactory $customSalesOrder,
        CustomerFactory $magentoCustomerModel,
        BillingAddress $orderBillingAddress,
        ShippingAddress $orderShippingAddress,
        Item $orderItem,
        Payment $orderPayment,
        CustomerInterface $customerApiData,
        StoreManagerInterface $storeManager,
        CurrencyInterface $currencyApiInterface,
        CartManagementInterface $quoteToOrder,
        Order $orderModel,
        AbstractDataPersistence $abstractDataPersistence,
        DateTime $date,
        OrderMismatchEmail $orderMismatchEmail,
        Manager $eventManager,
        ChequeNumber $chequeNumber,
        ProductRepositoryInterfaceFactory $productRepo,
        OrderManagementInterfaceFactory $orderManagement,
        Validate $validate,
        ItemsForReindex $itemsForReindex,
        StoreWebsiteRelationInterface $storeWebsiteRelation
    ) {
        $this->customSalesOrder = $customSalesOrder;
        $this->orderBillingAddress = $orderBillingAddress;
        $this->orderShippingAddress = $orderShippingAddress;
        $this->orderItem = $orderItem;
        $this->orderPayment = $orderPayment;
        $this->quoteToOrder = $quoteToOrder;
        $this->orderModel = $orderModel;
        $this->abstractDataPersistence = $abstractDataPersistence;
        $this->date = $date;
        $this->orderMismatchEmail = $orderMismatchEmail;
        $this->eventManager = $eventManager;
        $this->chequeNumberModel = $chequeNumber;
        $this->productRepo = $productRepo;
        $this->orderManagement = $orderManagement;
        $this->itemsForReindex = $itemsForReindex;
        $this->storeWebsiteRelation = $storeWebsiteRelation;
        parent::__construct(
            $logger,
            $genericHelper,
            $quoteFactory,
            $dataHelper,
            $magentoCustomerModel,
            $customerApiData,
            $storeManager,
            $currencyApiInterface,
            $validate
        );
    }

    /**
     * Create order.
     *
     * @param array $stringData
     * @param string $entityCode
     * @param string $erp
     *
     * @return I95DevResponseInterface
     * @throws Exception
     */
    public function createOrder($stringData, $entityCode, $erp)
    {
        $this->stringData = $stringData;
        $this->entityCode = $entityCode;
        try {
            $status = $this->validateOrderData();
            if (is_object($status)) {
                return $status;
            }
            //@author Arushi BAnsal.fix added for-22646672-Order total mismatch due item prices(Catalog Price RuleIssue)
            $this->dataHelper->unsetGlobalValue('i95_skip_final_price');
            $this->dataHelper->setGlobalValue('i95_skip_final_price', true);

            $this->quoteModel = $this->quoteFactory->create();
            $this->setStoreDetails();
            if (isset($this->stringData['websiteId'])) {
                $storeId = [];
                $storeId = $this->storeWebsiteRelation->getStoreByWebsiteId($this->stringData['websiteId']);
                $this->quoteModel->setStoreId($storeId[0]);
            }
            if ($this->customer) {
                $this->setCustomerDetails();
            }
            $this->setAddressDetails();
            $this->quoteModel = $this->orderItem->addItemsToQuote($this->quoteModel);
            $this->setShippingDetails();
            $this->shippingAmount = isset($this->stringData['shippingAmount']) ?
                $this->stringData['shippingAmount'] : 0;
            $this->quoteModel->setIsActive(1);
            $this->quoteModel->setInventoryProcessed(true);
            $itemsToDoReindex = [];
            $this->itemsForReindex->setItems($itemsToDoReindex);
            $paymentData = $this->orderPayment->setPaymentInformation();
            $this->quoteModel->getPayment()->setData($paymentData);
            $objShippingAddress = $this->quoteModel->getShippingAddress();
            $objShippingAddress->setShippingAmount($this->shippingAmount);
            $objShippingAddress->setBaseShippingAmount($this->shippingAmount);
            $this->dataHelper->unsetGlobalValue(self::I95_OBSERVER_SKIP);
            $this->dataHelper->setGlobalValue(self::I95_OBSERVER_SKIP, true);
            $this->quoteModel->save();
            $this->dataHelper->unsetGlobalValue(self::I95_OBSERVER_SKIP);

            $this->updateShippingAmountInTotal($objShippingAddress);
            $discountEntity = $this->dataHelper->getValueFromArray("discount", $this->stringData);

            if (!empty($discountEntity) && is_array($discountEntity)) {
                $this->setDiscountForQuoteItems($discountEntity);
            }

            $beforeQuoteSave = 'erpconnect_messagequeuetomagento_beforesave_quote';
            $this->eventManager->dispatch($beforeQuoteSave, ['quoteObject' => $this]);
            $this->dataHelper->unsetGlobalValue(self::I95_OBSERVER_SKIP);
            $this->dataHelper->setGlobalValue(self::I95_OBSERVER_SKIP, true);
            $this->quoteModel->save();
            $this->dataHelper->unsetGlobalValue(self::I95_OBSERVER_SKIP);
            $afterQuoteSave = 'erpconnect_messagequeuetomagento_aftersave_quote';
            $this->eventManager->dispatch($afterQuoteSave, ['quoteObject' => $this]);

            $this->dataHelper->unsetGlobalValue(self::I95_OBSERVER_SKIP);
            $this->dataHelper->setGlobalValue(self::I95_OBSERVER_SKIP, true);
            //@author Divya Koona. In Magento 2.3.2 shipping charges and discount are not setting due to
            //collectTotals() is calling in placeOrder method. So used submit method instead of placeOrder.
            //This can be used for all Magento versions as generalized solution. But required to test once
            $order = $this->quoteToOrder->submit($this->quoteModel, $this->orderData);
            $this->dataHelper->unsetGlobalValue(self::I95_OBSERVER_SKIP);
            $this->deactivateQuote($this->quoteModel);
            $this->orderObject = $order->getData();
            if (!empty($this->orderObject)) {
                $this->orderSaveAfter($order);
                return $this->abstractDataPersistence->setResponse(
                    Data::SUCCESS,
                    "Record Successfully Synced",
                    $this->orderObject[self::INCREMENT_ID]
                );
            } else {
                $this->deactivateQuote($this->quoteModel);
                throw new LocalizedException(
                    __("Error occur during order creation"),
                    null,
                    105
                );
            }
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(
                __METHOD__,
                $erp . " :: " . $ex->getMessage(),
                LoggerInterface::I95EXC,
                'error'
            );
            
            if (isset($this->quoteModel)) {
                $this->deactivateQuote($this->quoteModel);
            }

            return $this->abstractDataPersistence->setResponse(
                Data::ERROR,
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
    }

    /**
     * Validate order details provided by ERP.
     *
     * @throws LocalizedException
     */
    public function validateOrderData()
    {
        try {
            if (isset($this->stringData["isValidated"]) && $this->stringData["isValidated"]) {
                return null;
            }

            $this->validate->validateFields = $this->validateFields;
            $this->validate->validateData($this->stringData);
            $loadCustomOrder = $this->customSalesOrder->create()->getCollection();
            $loadCustomOrder->addFieldToSelect(['source_order_id'])
                ->addFieldToFilter(
                    'target_order_id',
                    $this->dataHelper->getValueFromArray(self::TARGETID, $this->stringData)
                )
                ->setOrder('id', 'DESC');
            $loadCustomOrder->getSelect()->limit(1);

            if ($loadCustomOrder->getSize() > 0) {
                $this->validationResult = $this->abstractDataPersistence->setResponse(
                    Data::SUCCESS,
                    "Record Successfully Synced",
                    $loadCustomOrder->getFirstItem()->getSourceOrderId()
                );
            }

            $this->getCustomerData($this->dataHelper->getValueFromArray("targetCustomerId", $this->stringData));
            $this->orderBillingAddress->currentObject($this)->validateData($this->stringData);
            $this->stringData[self::SHIPPINGMETHOD] = $this->getShippingMethod();
            $this->orderShippingAddress->currentObject($this)->validateData($this->stringData);
            $this->orderItem->currentObject($this)->validateData($this->stringData);
            $this->stringData[self::PAYMENT] = $this->getPaymentMethods();
            $this->orderPayment->currentObject($this)->validateData($this->stringData);

            $afterOrderValidate = 'erpconnect_messagequeuetomagento_after_validate_' . $this->entityCode;
            $this->eventManager->dispatch($afterOrderValidate, ['orderValidateObject' => $this]);
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(
                __METHOD__,
                $ex->getMessage(),
                LoggerInterface::I95EXC,
                'critical'
            );
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }

        return $this->validationResult;
    }

    /**
     * Getting shipping method code
     *
     * @return string
     * *@author Arushi Bansal
     */
    public function getShippingMethod()
    {
        return $this->stringData[self::SHIPPINGMETHOD];
    }

    /**
     * Getting payment methods
     *
     * @return array
     * *@author Ranjith R
     */
    public function getPaymentMethods()
    {
        return $this->stringData[self::PAYMENT];
    }

    /**
     * Add shipping address and billing address to quote
     */
    public function setAddressDetails()
    {
        $billingAddress = $this->orderBillingAddress->addBillingAddress();
        $shippingAddress = $this->orderShippingAddress->addShippingAddress();
        $this->quoteModel->setShippingAddress($shippingAddress);
        $this->quoteModel->getBillingAddress()->addData($billingAddress);
    }

    /**
     * Re-set OriginalPrice, BaseOriginalPrice, BaseCost after order created successfully.
     *
     * Set order details in i95dev_flat_order table.
     *
     * Notify the customer by mail. Send order total Mismatch Email if any.
     *
     * @param OrderInterface $order
     * @throws LocalizedException*@throws \Exception
     * @throws Exception
     * @author Debashis S. Gopal
     */
    public function orderSaveAfter($order)
    {
        foreach ($order->getAllItems() as $item) {
            //@author Arushi Bansal - to fix original price zero issue at parent product level
            $product = $this->productRepo->create()->get($item->getSku());
            $productData = $product->getData();
            //@author kavya.koona - checking whether the product price is there or not
            $item->setOriginalPrice(isset($productData[self::PRICE]) ? $productData[self::PRICE] : '0');
            $item->setBaseOriginalPrice($productData[self::PRICE]);
            $basecost = (isset($productData['cost']) ? $productData['cost'] : '0');
            $item->setBaseCost($basecost);
            $item->save();
        }
        $this->saveCustomOrderInformation();
        $this->sendEmail($this->orderObject[self::ENTITY_ID]);
        $targetOrderDocumentAmount = $this->dataHelper->getValueFromArray("orderDocumentAmount", $this->stringData);
        $orderTotal = $this->orderObject['base_grand_total'];
        if (isset($targetOrderDocumentAmount) && $targetOrderDocumentAmount != ''
            && $targetOrderDocumentAmount != $orderTotal
        ) {
            $this->orderMismatchEmail->compareTargetValue(
                $this->orderObject['base_grand_total'],
                $targetOrderDocumentAmount,
                $this->orderObject[self::INCREMENT_ID],
                $this->orderObject['created_at'],
                false
            );
        }
        $aftereventname = 'erpconnect_messagequeuetomagento_aftersave_' . $this->entityCode;
        $this->eventManager->dispatch($aftereventname, ['orderObject' => $this]);
        $this->dataHelper->unsetGlobalValue(self::I95_OBSERVER_SKIP);
    }

    /**
     * Saving targetOrder details in customTable
     *
     * @throws LocalizedException
     * @author Divya Koona. Removed of inserting gp_orderprocess_flag column value to i95dev_sales_flat_order table
     */
    public function saveCustomOrderInformation()
    {
        try {
            $additional_info = [];
            $erpTax = $this->dataHelper->getValueFromArray("taxAmount", $this->stringData);
            $taxAmount = isset($erpTax) && $erpTax != '' ? $erpTax : 0;
            $customOrderModel = $this->customSalesOrder->create();
            $loadCustomOrder = $this->customSalesOrder->create()
                ->load($this->orderObject[self::INCREMENT_ID], 'source_order_id');
            if ($loadCustomOrder->getId()) {
                $customOrderModel->setId($loadCustomOrder->getId());
            }
            $additional_info['documentamount'] = $this->dataHelper->getValueFromArray(
                "orderDocumentAmount",
                $this->stringData
            );
            $additional_info['shippingamount'] = $this->shippingAmount;
            $additional_info['taxamount'] = $taxAmount;
            $additional_info = json_encode($additional_info, JSON_UNESCAPED_UNICODE);
            $customOrderModel->setTargetOrderId(
                $this->dataHelper->getValueFromArray(self::TARGETID, $this->stringData)
            );
            $origin = $this->dataHelper->getscopeConfig(
                'i95dev_messagequeue/I95DevConnect_settings/component',
                ScopeInterface::SCOPE_WEBSITE,
                $this->storeManager->getDefaultStoreView()->getWebsiteId()
            );
            $customOrderModel->setOrigin($origin);
            $customOrderModel->setAdditionalInfo($additional_info);
            $customOrderModel->setCreatedAt($this->date->gmtDate());
            $customOrderModel->setUpdatedAt($this->date->gmtDate());
            $customOrderModel->setUpdateBy('ERP');
            $customOrderModel->setSourceOrderId($this->orderObject[self::INCREMENT_ID]);
            $customOrderModel->setTargetOrderStatus('New');
            $customOrderModel->setUpdatedDt($this->date->gmtDate());
            $this->eventManager->dispatch(
                'i95dev_custom_sales_order_in_save_before',
                ['customOrder' => $customOrderModel, 'currentObject' => $this]
            );
            $customOrderModel->save();
            $payment = $this->dataHelper->getValueFromArray(self::PAYMENT, $this->stringData);
            $payment = isset($payment[0]) ? $payment[0] : '';
            $chequeNumber = $this->dataHelper->getValueFromArray("checkNumber", $payment);
            if ($chequeNumber) {
                $this->chequeNumberModel->setTargetChequeNumber($chequeNumber)
                    ->setSourceOrderId($this->orderObject[self::ENTITY_ID])
                    ->setTargetOrderId($this->dataHelper->getValueFromArray(
                        self::TARGETID,
                        $this->stringData
                    ))
                    ->save();
            }
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(
                __METHOD__,
                $ex->getMessage(),
                LoggerInterface::I95EXC,
                'critical'
            );
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
    }

    /**
     * Send mail to customer once order is placed
     *
     * @param int $id
     * @return bool
     * @author Divya Koona. Removed API call and converted to interfaces.
     */
    public function sendEmail($id)
    {
        return $this->orderManagement->create()->notify($id);
    }
}

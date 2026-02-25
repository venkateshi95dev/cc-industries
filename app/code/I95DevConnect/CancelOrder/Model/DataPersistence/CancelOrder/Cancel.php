<?php

/**
 * @noinspection ALL
 */

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_CancelOrder
 */

namespace I95DevConnect\CancelOrder\Model\DataPersistence\CancelOrder;

use Exception;
use I95DevConnect\MessageQueue\Api\Data\I95DevErpMQInterfaceFactory;
use I95DevConnect\MessageQueue\Api\I95DevErpDataRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Api\I95DevErpMQRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Api\I95DevResponseInterfaceFactory;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Helper\ErpOrderStatus;
use I95DevConnect\MessageQueue\Helper\Generic;
use I95DevConnect\MessageQueue\Model\AbstractDataPersistence;
use I95DevConnect\MessageQueue\Model\DataPersistence\Validate;
use I95DevConnect\MessageQueue\Model\ErrorUpdateDataFactory;
use I95DevConnect\MessageQueue\Model\ResourceModel\I95DevInvoiceHistory\CollectionFactory;
use I95DevConnect\MessageQueue\Model\SalesOrderFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Decoder;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Service\CreditmemoService;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Mail_Exception;

/**
 * Class for Cancel order
 */
class Cancel extends AbstractDataPersistence
{
    public const ITEMS = 'items';
    public const CUSTOMER = 'customer';
    public const CRITICAL = 'critical';
    public const STATUS = 'status';
    public const TARGETID = 'targetId';
    public const CREDITMEMOID = 'creditmemoId';
    public const MESSAGE = 'message';
    public const I95OSKIP = 'i95_observer_skip';
    public const TRANSTYPE = 'transaction_type';

    /**
     * @var string
     */
    public $errorMsg = 'Something went wrong. Please contact admin.';

    /**
     * @var string
     */
    public $orderId;

    /**
     * @var string
     */
    public $orderStatus;

    /**
     * @var Order
     */
    public $orderModel;

    /**
     * @var SalesOrderFactory
     */
    public $customSalesOrder;

    /**
     * @var OrderManagementInterface
     */
    public $orderManagement;

    /**
     * @var CreditmemoService
     */
    public $creditmemoService;

    /**
     * @var CreditmemoLoader
     */
    public $creditmemoLoader;

    /**
     * @var OrderCommentSender
     */
    public $commentEmailSender;

    /**
     * @var string
     */
    public $messagequeueData;

    /**
     * @var object
     */
    public $messageQueueFactory;

    /**
     * @var OrderRepositoryInterface
     */
    public $orderRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    public $customerRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    public $searchCriteriaBuilder;

    /**
     * @var CustomerRepositoryInterfaceFactory
     */
    public $customerRepositoryInterfaceFactory;

    /**
     * @var CreditmemoFactory
     */
    public $creditmemoFactory;

    /**
     * @var Data
     */
    public $dataHelper;
    /**
     * @var OrderFactory
     */
    public $orderFactory;
    /**
     * @var Order
     */
    public $order;
    /**
     * @var StoreManagerInterface
     */
    public $storeManager;
    /**
     * @var \I95DevConnect\CancelOrder\Helper\Data
     */
    public $data;

    /**
     * @var generic
     */
    public $generic;
    /**
     * @var ErpOrderStatus
     */
    public $erpOrderStatus;
    /**
     * @var CollectionFactory
     */
    protected $invoiceHistory;
    /**
     * @var SalesOrderFactory
     */
    protected $loadCustomOrder;
    
    /**
     * @var int
     */
    protected $creditMemoId;

    /**
     * Cancel constructor.
     *
     * @param OrderFactory                            $orderModel
     * @param SalesOrderFactory                       $customSalesOrder
     * @param OrderManagementInterface                $orderManagement
     * @param CreditmemoService                       $creditmemoService
     * @param OrderCommentSender                      $commentEmailSender
     * @param Decoder                                 $jsonDecoder
     * @param I95DevResponseInterfaceFactory          $i95DevResponse
     * @param ErrorUpdateDataFactory                  $messageErrorModel
     * @param I95DevErpMQInterfaceFactory             $i95DevErpMQ
     * @param LoggerInterfaceFactory                  $logger
     * @param I95DevErpMQRepositoryInterfaceFactory   $i95DevErpMQRepository
     * @param DateTime                                $date
     * @param Manager                                 $eventManager
     * @param Validate                                $validate
     * @param I95DevErpDataRepositoryInterfaceFactory $i95DevERPDataRepository
     * @param Data                                    $dataHelper
     * @param CreditmemoFactory                       $creditmemoFactory
     * @param StoreManagerInterface                   $storeManager
     * @param OrderRepositoryInterface                $orderRepository
     * @param \I95DevConnect\CancelOrder\Helper\Data  $data
     * @param CustomerRepositoryInterface             $customerRepository
     * @param SearchCriteriaBuilder                   $searchCriteriaBuilder
     * @param CustomerRepositoryInterfaceFactory      $customerRepositoryInterfaceFactory
     * @param Generic                                 $generic
     * @param CollectionFactory                       $invoiceHistory
     * @param ErpOrderStatus                          $erpOrderStatus
     */
    public function __construct( // NOSONAR
        OrderFactory $orderModel,
        SalesOrderFactory $customSalesOrder,
        OrderManagementInterface $orderManagement,
        CreditmemoService $creditmemoService,
        OrderCommentSender $commentEmailSender,
        Decoder $jsonDecoder,
        I95DevResponseInterfaceFactory $i95DevResponse,
        ErrorUpdateDataFactory $messageErrorModel,
        I95DevErpMQInterfaceFactory $i95DevErpMQ,
        LoggerInterfaceFactory $logger,
        I95DevErpMQRepositoryInterfaceFactory $i95DevErpMQRepository,
        DateTime $date,
        Manager $eventManager,
        Validate $validate,
        I95DevErpDataRepositoryInterfaceFactory $i95DevERPDataRepository,
        Data $dataHelper,
        CreditmemoFactory $creditmemoFactory,
        StoreManagerInterface $storeManager,
        OrderRepositoryInterface $orderRepository,
        \I95DevConnect\CancelOrder\Helper\Data $data,
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomerRepositoryInterfaceFactory $customerRepositoryInterfaceFactory,
        Generic $generic,
        CollectionFactory $invoiceHistory,
        ErpOrderStatus $erpOrderStatus
    ) {
        $this->orderFactory = $orderModel;
        $this->customSalesOrder = $customSalesOrder;
        $this->orderManagement = $orderManagement;
        $this->creditmemoService = $creditmemoService;
        $this->commentEmailSender = $commentEmailSender;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->dataHelper = $dataHelper;
        $this->storeManager = $storeManager;
        $this->orderRepository = $orderRepository;
        $this->data = $data;
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerRepositoryInterfaceFactory = $customerRepositoryInterfaceFactory;
        $this->generic = $generic;
        $this->invoiceHistory = $invoiceHistory;
        $this->erpOrderStatus = $erpOrderStatus;
        parent::__construct(
            $jsonDecoder,
            $i95DevResponse,
            $messageErrorModel,
            $i95DevErpMQ,
            $logger,
            $i95DevErpMQRepository,
            $date,
            $eventManager,
            $validate,
            $i95DevERPDataRepository
        );
    }

    /**
     * Cancel order
     *
     * @param  string $stringData
     * @return $this
     * @throws Exception
     */
    public function cancelOrder($stringData) //NOSONAR
    {
        if (!$this->data->isEnabled()) {
            return $this->setResponse(
                Data::ERROR,
                __("I95DevConnect Cancel Order extension is currently disabled. Enable the extension to proceed sync.")
            );
        }
        try {
            $this->setStringData($stringData);
            $this->validate();
            $this->orderId = $this->order->getEntityId();
            // @updatedBy Subhan. Removed orderstatus flag and added if auth_capture
            $paymentDetails = $this->order->getPayment();
            $transactionDetails = $paymentDetails->getAdditionalInformation();
            $transactionType = '';
            if (isset($transactionDetails[self::TRANSTYPE])) {
                $transactionType = $transactionDetails[self::TRANSTYPE];
            }
            if (strtolower($transactionType) === 'auth_capture') {
                $creditMemoId = $this->processProcessingStatusWithInvoice();
            } elseif (strtolower($paymentDetails->getMethod()) === 'paypal_express'
                && $this->order->hasInvoices()
            ) {
                $creditMemoId = $this->processProcessingStatusWithInvoice();
            } else {
                $result = $this->cancelMagentoOrder();
                if (!$result) {
                    throw new LocalizedException(
                        __($this->errorMsg)
                    );
                }
                //@hrusikesh Updating Customer Credit Limit After Cancel Order
                $this->updateCustomerCreditLimit();
                $creditMemoId = $this->order->getIncrementId();
            }

            if ($this->order->getStatus() !== 'canceled') {
                $this->commentEmailSender->send($this->order, true);
            }

            return $this->setResponse(
                Data::SUCCESS,
                "Record Successfully Synced",
                $creditMemoId
            );
        } catch (LocalizedException $ex) {
            if (strpos($ex->getMessage(), 'mail') !== false) {
                return $this->setResponse(
                    Data::SUCCESS,
                    "Record Successfully Synced",
                    $creditMemoId
                );
            } else {
                return $this->setResponse(
                    Data::ERROR,
                    __($ex->getMessage())
                );
            }
        }
    }

    /**
     * Validate data
     *
     * @throws LocalizedException
     */
    public function validate()
    {
        $loadCustomOrderDetails = $this->customSalesOrder
            ->create()
            ->getCollection()
            ->addFieldToSelect('id')
            ->addFieldtoFilter("target_order_id", $this->stringData[self::TARGETID])
            ->setOrder('id', 'DESC');
        $loadCustomOrderDetails->getSelect()->limit(1);

        $this->loadCustomOrder = $this->customSalesOrder->create()->load(
            $loadCustomOrderDetails->getFirstItem()->getId()
        );
        if ($this->loadCustomOrder->getId()) {
            $this->order = $this->orderFactory->create()->loadByIncrementId($this->loadCustomOrder->getSourceOrderId());
        } else {
            $message = "order not found";
            throw new LocalizedException(__($message));
        }
        if (!empty($this->order) && $this->order->getEntityId() > 0) {
            $this->orderStatus = $this->order->getStatus();
        } else {
            $message = "Order not exists in Magento.";
            throw new LocalizedException(__($message));
        }
        if (strtolower($this->orderStatus) == 'canceled') {
            $message = "Order already cancelled.";
            throw new LocalizedException(__($message));
        }
        if (strtolower($this->orderStatus) == 'closed') {
            $message = "Order already closed.";
            throw new LocalizedException(__($message));
        }
        //@Hrusieksh Added Completed Order Cancel Validation
        if (strtolower($this->orderStatus) == 'complete') {
            $message = "Completed order cannot be canceled.";
            throw new LocalizedException(__($message));
        }

        /*
         * @addedBy Subhan. To validate partial shipment and partial invoice
         */
        $this->data->validatePartialData($this);
    }

    /**
     * For processing order
     *
     * @return mixed
     * @throws Exception
     */
    public function processProcessingStatusWithInvoice()
    {
        if (!$this->order->canInvoice()) {
            $isPartiallyShipped = $this->checkIspartiallyShipped($this->order);
            if (!empty($isPartiallyShipped) && $isPartiallyShipped !== 0) {
                $isPartial = true;
            } else {
                $isPartial = false;
            }

            $creditmemoResult = $this->refundOrder($isPartial);
            if ($creditmemoResult) {
                if (!$creditmemoResult[self::STATUS]) {
                    $this->logger->create()->createLog(
                        '__METHOD__',
                        $creditmemoResult[self::MESSAGE],
                        LoggerInterface::I95EXC,
                        'error'
                    );
                    throw new LocalizedException(
                        __($creditmemoResult[self::MESSAGE])
                    );
                } else {
                    $this->updateCustomerCreditLimit();
                    $this->loadCustomOrder->setTargetOrderStatus('Canceled');
                    $this->loadCustomOrder->setUpdateBy("ERP");
                    $this->loadCustomOrder->save();
                }
            }
        } else {
            // partial invoiced order
            $creditmemoResult = $this->refundOrder($isPartial = true);
            $this->processProcessingStatusWithPartialInvoice($creditmemoResult);
        }
        return $this->creditMemoId;
    }

    /**
     * Check if order partially shipped
     *
     * @param  object $orderData
     * @return int
     */
    public function checkIspartiallyShipped($orderData)
    {
        $isPartial = 0;
        $orderObject = $this->orderRepository->get($orderData->getId());
        //@ Hrusieksh check wheather the order has Shipment or not
        if ($orderObject->hasShipments()) {
            foreach ($orderObject->getAllVisibleItems() as $item) {
                if ($item->getQtyInvoiced() != $item->getQtyShipped()) {
                    $isPartial++;
                }
            }
        }
        return $isPartial;
    }

    /**
     * Creates credit memo for cancel order
     *
     * @param  flag $isPartial
     * @return array
     * @throws LocalizedException
     */
    public function refundOrder($isPartial)
    {
        $orderObject = $this->order;
        $status = true;
        $paymentDetails = $orderObject->getPayment();
        $paymentMethod = $paymentDetails->getMethod($this->order);
        $paymentAdditionalInfo = $paymentDetails->getAdditionalInformation();

        $isAuthorize = 0;
        if (isset($paymentAdditionalInfo["payment_type"]) && $paymentDetails["payment_type"] == "authorize") {
            $isAuthorize = 1;
        }

        $dataArray = [
            'do_offline' => 0,
            'adjustment_positive' => 0,
            'base_shipping_amount' => 0,
            'adjustment_negative' => 0,
            'refund_customerbalance_return_enable' => 0,
            'send_email' => 1,
        ];

        if ($isPartial) {
            $QtytoReturn = $this->getQtytoReturn($this->order);
            $itemToCredit = $QtytoReturn[self::ITEMS];
            $qtys = $QtytoReturn['qtys'];
            $dataArray['shipping_amount'] = 0;
            $dataArray [self::ITEMS] = $itemToCredit;
            $dataArray['qtys'] = $qtys;
        }

        if (!empty($this->order->getInvoiceCollection())) {
            // @updatedBy Subhan
            $msg[] = 'success';
            return $this->createCreditMemo($paymentMethod, $isAuthorize, $status, $orderObject, $dataArray, $msg);
        } else {
            $msg[] = 'Sorry,Credit memo cant be created';
            $status = false;
            return [self::STATUS => $status, self::MESSAGE => $msg, self::CREDITMEMOID => $this->creditMemoId];
        }
    }

    /**
     * Calculate quantity to return
     *
     * @param  obj $orderData
     * @return array
     */
    public function getQtytoReturn($orderData)
    {
        $orderObject = $this->orderRepository->get($orderData->getId());
        if (isset($this->stringData['cancelItemEntity'])) {
            $cancelItems = $this->stringData['cancelItemEntity'];
            $orderItemData = [];
            foreach ($orderObject->getAllVisibleItems() as $item) {
                $orderItemData[strtolower($item->getSku())] = $item->getId();
            }

            $itemToCredit = [];
            $qtys = [];
            foreach ($cancelItems as $cancelItem) {
                $itemToCredit[$orderItemData[strtolower($cancelItem['orderItemId'])]] = [
                    'qty' => $cancelItem['quantityToCancel']
                ];
                $qtys[$orderItemData[strtolower($cancelItem['orderItemId'])]] = $cancelItem['quantityToCancel'];
            }

            return [self::ITEMS => $itemToCredit, 'qtys' => $qtys];
        } else {
            return $this->getCancelQtyFromInvoiceHistory($orderObject);
        }
    }

    /**
     * Get Canceled Item Qty from Custom Invoice History
     *
     * @param  object $orderObj
     * @return array[]
     */
    public function getCancelQtyFromInvoiceHistory($orderObj)
    {
        $orderItems = $orderObj->getAllItems();
        $itemToCredit = [];
        $qtys = [];
        foreach ($orderItems as $item) {
            $qtyInvoiced = $this->getQtyInvoicedFromHistory($this->stringData[self::TARGETID], $item->getSku());
            $qtyToCancel = $item->getQtyOrdered() - (int)$qtyInvoiced;
            if ($qtyToCancel > 0) {
                $itemToCredit[$item->getId()] = [
                    'qty' => $qtyToCancel
                ];
                $qtys[$item->getId()] = $qtyToCancel;
            }
        }
        return [self::ITEMS => $itemToCredit, 'qtys' => $qtys];
    }

    /**
     * Get Invoiced Item Qty from Invoice History
     *
     * @param   int    $orderTargetId
     * @param   string $itemSku
     * @return  mixed
     * @addedBy Subhan
     */
    public function getQtyInvoicedFromHistory($orderTargetId, $itemSku)
    {
        $collection = $this->invoiceHistory->create();
        $collection->getSelect()->join(
            ['itemHistory' => $collection->getTable('i95dev_sales_invoice_item_history')],
            'main_table.id = itemHistory.invoice_entity_id',
            ['sum(itemHistory.item_qty) as item_qty']
        );
        $collection->addFieldToFilter('main_table.target_order_id', $orderTargetId);
        $collection->addFieldToFilter('itemHistory.item_sku', $itemSku);
        return $collection->getData()[0]['item_qty'];
    }

    /**
     * Create credit memo
     *
     * @param  string $paymentMethod
     * @param  bool   $isAuthorize
     * @param  bool   $status
     * @param  object $order
     * @param  array  $dataArray
     * @param  string $msg
     * @return array
     */
    public function createCreditMemo($paymentMethod, $isAuthorize, $status, $order, $dataArray, $msg)
    {
        $offlinePaymentMethods = ['checkmo', 'cashondelivery', 'free', 'creditlimits'];

        if (in_array($paymentMethod, $offlinePaymentMethods) || $isAuthorize > 0) {
            $result = $this->cancelMagentoOrder();
            if (!$result) {
                throw new LocalizedException(__($this->errorMsg));
            }
            $this->creditMemoId = $this->order->getIncrementId();
            return [self::STATUS => $status, self::CREDITMEMOID => $this->creditMemoId];
        } else {
            $invoices = $order->getInvoiceCollection();
            foreach ($invoices as $invoice) {
                $invoiceincrementid = $invoice->getIncrementId();
            }

            $invoiceobj = $invoice->loadByIncrementId($invoiceincrementid);
            $creditmemo = $this->creditmemoFactory->createByOrder($order, $dataArray);
            $creditmemo->setInvoice($invoiceobj);

            try {
                if (!$creditmemo->isValidGrandTotal()) {
                    throw new LocalizedException(
                        __('The credit memo\'s total must be positive.')
                    );
                }
                $creditMemoData = $this->creditmemoService->refund($creditmemo);
                $this->creditMemoId = $creditMemoData->getIncrementId();
                if (!empty($msg)) {
                    $message = implode(',', $msg);
                } else {
                    $message = "";
                }
                return [self::STATUS => $status, self::MESSAGE => $message, self::CREDITMEMOID => $this->creditMemoId];
            } catch (LocalizedException $ex) {
                throw new LocalizedException(__($ex->getMessage()));
            }
        }
    }

    /**
     * Cancel order
     *
     * @return bool
     * @throws LocalizedException
     */
    public function cancelMagentoOrder()
    {
        try {
            /**
             * @author Debashis S. Gopal. Added observer skip
             **/
            $this->dataHelper->unsetGlobalValue(self::I95OSKIP);
            $this->dataHelper->setGlobalValue(self::I95OSKIP, true);
            $this->orderManagement->cancel($this->orderId);
            $this->updateCustomOrder();
            $this->dataHelper->unsetGlobalValue(self::I95OSKIP);
            return true;
        } catch (LocalizedException $ex) {
            $message = $ex->getMessage();
            throw new LocalizedException(__($message));
        }
    }

    /**
     * Updates custom order info
     */
    public function updateCustomOrder()
    {
        $status = $this->erpOrderStatus->getERPOrderStatus($this->orderId);
        $this->loadCustomOrder->setTargetOrderStatus($status);
        $this->loadCustomOrder->setUpdateBy("ERP");
        $this->loadCustomOrder->save();
    }

    /**
     * Updates customer credit limit
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function updateCustomerCreditLimit()
    {
        //@ Hrusikesh Changed targetCustomerId to targetId
        $targetCustomerId = isset($this->stringData[self::CUSTOMER][self::TARGETID]) ?
            $this->stringData[self::CUSTOMER][self::TARGETID] : "";
        $customerInfo = $this->getCustomerInfoByTargetId($targetCustomerId);
        if (!empty($customerInfo) && isset($this->stringData[self::CUSTOMER]['creditLimitType'])) {
            $creditLimitType = $this->stringData[self::CUSTOMER]['creditLimitType'];
            $creditLimitAmount = isset($this->stringData[self::CUSTOMER]['creditLimitAmount']) ?
                $this->stringData[self::CUSTOMER]['creditLimitAmount'] : 0;
            $availableLimit = isset($this->stringData[self::CUSTOMER]['availableLimit']) ?
                $this->stringData[self::CUSTOMER]['availableLimit'] : 0;
            $customerId = $customerInfo[0]->getId();
            $customer = $this->customerRepository->getById($customerId);
            $customer->setWebsiteId($this->storeManager->getStore()->getWebsiteId());
            $customer->setStoreId($this->storeManager->getStore()->getStoreId());
            $customer->setCustomAttribute('credit_limit_type', $creditLimitType);
            $customer->setCustomAttribute('credit_limit_amount', $creditLimitAmount);
            $customer->setCustomAttribute('available_limit', $availableLimit);
            try {
                $this->customerRepository->save($customer);
                return;
            } catch (LocalizedException $ex) {
                throw new LocalizedException(
                    __($ex->getMessage())
                );
            }
        }
    }

    /**
     * Get customer info by target id
     *
     * @param  string $targetId
     * @return obj $customerInfo
     * @throws LocalizedException
     */
    public function getCustomerInfoByTargetId($targetId)
    {
        try {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('target_customer_id', $targetId, 'eq')
                ->create();
            $searchResults = $this->customerRepositoryInterfaceFactory->create()->getList($searchCriteria);
            $customerInfo = $searchResults->getItems();
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(
                __METHOD__,
                $ex->getMessage(),
                LoggerInterface::I95EXC,
                self::CRITICAL
            );
            throw new LocalizedException(__($ex->getMessage()));
        }

        return $customerInfo;
    }

    /**
     * Process processing status with partial invoice
     *
     * @param  object $creditmemoResult
     * @throws Exception
     */
    public function processProcessingStatusWithPartialInvoice($creditmemoResult)
    {
        if ($creditmemoResult) {
            if (!$creditmemoResult[self::STATUS]) {
                $this->logger->create()->createLog(
                    '__METHOD__',
                    $creditmemoResult[self::MESSAGE],
                    LoggerInterface::I95EXC,
                    'error'
                );
                throw new LocalizedException(
                    __($creditmemoResult[self::MESSAGE])
                );
            } else {
                $this->updateCustomerCreditLimit();
                $this->updateCustomOrder();
                $this->updateOrder($this->order);
            }
        }
    }

    /**
     * Update order state after create credit memo
     *
     * @param  obj $order
     * @throws Exception
     */
    public function updateOrder($order)
    {
        /**
         * @author Debashis S. Gopal. Added observer skip
         **/
        $this->dataHelper->unsetGlobalValue(self::I95OSKIP);
        $this->dataHelper->setGlobalValue(self::I95OSKIP, true);
        $orderObj = $this->orderFactory->create()->load($order->getId());
        $orderObj->setState(Order::STATE_CLOSED);
        $orderObj->setStatus(Order::STATE_CLOSED);

        try {
            $orderObj->save();
            $this->commentEmailSender->send($orderObj, true, "");
        } catch (Zend_Mail_Exception | LocalizedException $ex) {
            $message = $ex->getMessage();
            $this->logger->create()->createLog(
                __METHOD__,
                $message,
                LoggerInterface::I95EXC,
                self::CRITICAL
            );
        }
        $this->dataHelper->unsetGlobalValue(self::I95OSKIP);
    }

    /**
     * Process processing status without invoice
     *
     * @return mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function processProcessingStatusWithoutInvoice()
    {
        if (!$this->order->hasShipments()) {
            $result = $this->cancelMagentoOrder();
            if (!$result) {
                throw new LocalizedException(
                    __($this->errorMsg)
                );
            }
            //@hrusikesh Updating Customer Credit Limit After Cancel Order
            $this->updateCustomerCreditLimit();

            return $this->order->getIncrementId();
        } else {
            throw new LocalizedException(
                __("Shipment quantity must be invoiced.")
            );
        }
    }

    /**
     * Get canceled item  quantity from order
     *
     * @param  type $orderObj
     * @return array
     * @author Hrusikesh Manna
     */
    public function getCancelQtyFromOrder($orderObj)
    {
        $orderItems = $orderObj->getAllItems();
        $itemToCredit = [];
        $qtys = [];
        foreach ($orderItems as $item) {
            $qtyToCancel = $item->getQtyOrdered() - $item->getQtyInvoiced();
            if ($qtyToCancel > 0) {
                $itemToCredit[$item->getId()] = [
                    'qty' => $qtyToCancel
                ];
                $qtys[$item->getId()] = $qtyToCancel;
            }
        }
        return [self::ITEMS => $itemToCredit, 'qtys' => $qtys];
    }

    /**
     * Get shipment ids
     *
     * @param  object $orderData
     * @return array
     */
    public function getShipmentIds($orderData)
    {
        $orderObject = $this->orderRepository->get($orderData->getId());

        $shipmentCollection = $orderObject->getShipmentsCollection();
        $shipmentIds = [];
        foreach ($shipmentCollection as $shipment) {
            $customshipment = $this->generic->getCustomShipmentById($shipment['increment_id']);
            $shipmentIds[] = $customshipment->gettargetShipmentId();
        }
        return $shipmentIds;
    }

    /**
     * Get invoice ids
     *
     * @param  object $orderData
     * @return array
     */
    public function getInvoiceIds($orderData)
    {
        $orderObject = $this->orderRepository->get($orderData->getId());

        $invoiceCollection = $orderObject->getInvoiceCollection();
        $invoiceIds = [];
        foreach ($invoiceCollection as $invoice) {
            $custominvoice = $this->generic->getCustomInvoiceById($invoice['increment_id']);
            $invoiceIds[] = $custominvoice->gettargetInvoiceId();
        }
        return $invoiceIds;
    }
}

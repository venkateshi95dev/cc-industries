<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_CancelOrder
 */

namespace I95DevConnect\Returns\Model\DataPersistence\ReturnCreditmemo;

use I95DevConnect\MessageQueue\Model\AbstractDataPersistence;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\Returns\Model\ReturnsCreditMemoIdsFactory;
use I95DevConnect\Returns\Model\RmaEntityFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Rma\Model\RmaFactory;
use Magento\Sales\Model\Order\CreditmemoNotifier;

/**
 * Class for ReturnCreditmemo
 */
class ReturnCreditmemo extends AbstractDataPersistence
{
    public const ITEMS = 'items';
    public const CUSTOMER = 'customer';
    public const CRITICAL = 'critical';
    public const STATUS = 'status';
    public const TARGETID = 'targetId';
    public const CREDITMEMOID = 'creditmemoId';
    public const TARGETRETURNID = 'targetReturnId';
    public const MESSAGE = 'message';
    public const I95OSKIP = 'i95_observer_skip';
    public const TRANSTYPE = 'transaction_type';
    /**
     * @var string
     */
    public $errorMsg = 'Something went wrong. Please contact admin.';

    /**
     * @var \Magento\Sales\Model\Order
     */
    public $orderModel;
    /**
     * @var \I95DevConnect\MessageQueue\Model\SalesOrderFactory
     */
    public $customSalesOrder;

    /**
     * @var \Magento\Sales\Api\OrderManagementInterface
     */
    public $orderManagement;

    /**
     * @var \Magento\Sales\Model\Service\CreditmemoService
     */
    public $creditmemoService;

    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader
     */
    public $creditmemoLoader;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender
     */
    public $commentEmailSender;
    /**
     * @var object
     */
    public $messagequeueData;
    /**
     * @var object
     */
    public $messageQueueFactory;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    public $orderRepository;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    public $customerRepository;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    public $searchCriteriaBuilder;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterfaceFactory
     */
    public $customerRepositoryInterfaceFactory;
    /**
     * @var \Magento\Sales\Model\Order\CreditmemoFactory
     */
    public $creditmemoFactory;
    /**
     * @var \I95DevConnect\MessageQueue\Helper\Data
     */
    public $dataHelper;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    public $orderFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;
    /**
     * @var \I95DevConnect\Returns\Helper\Data
     */
    public $data;

    /**
     * @var generic
     */
    public $generic;

    /**
     * @var \I95DevConnect\MessageQueue\Model\ResourceModel\I95DevInvoiceHistory\CollectionFactory
     */
    protected $invoiceHistory;
    /**
     * @var RmaEntityFactory
     */
    // @codingStandardsIgnoreLine
    protected $_returnsEntity;
    /**
     * @var ReturnsCreditMemoIdsFactory
     */
    // @codingStandardsIgnoreLine
    protected $_customCreditmemo;

    /**
     * @var RmaFactory
     */
    protected $coreRmaEntity;

    /**
     *
     * @var CreditmemoNotifier
     */
    public $creditmemoNotifier;

    /**
     * @var int
     */
    public $creditMemoId;

    /**
     * @var \Magento\Sales\Api\OrderManagementInterface
     */
    public $order;

    /**
     * @var string
     */
    public $orderStatus;

    /**
     * @var int
     */
    public $orderId;
    
    /**
     * Construct
     *
     * @param \Magento\Framework\Json\Decoder $jsonDecoder
     * @param \I95DevConnect\MessageQueue\Api\I95DevResponseInterfaceFactory $i95DevResponse
     * @param \I95DevConnect\MessageQueue\Model\ErrorUpdateDataFactory $messageErrorModel
     * @param \I95DevConnect\MessageQueue\Api\Data\I95DevErpMQInterfaceFactory $i95DevErpMQ
     * @param \I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory $logger
     * @param \I95DevConnect\MessageQueue\Api\I95DevErpMQRepositoryInterfaceFactory $i95DevErpMQRepository
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Event\Manager $eventManager
     * @param \I95DevConnect\MessageQueue\Model\DataPersistence\Validate $validate
     * @param \I95DevConnect\MessageQueue\Api\I95DevErpDataRepositoryInterfaceFactory $i95DevERPDataRepository
     * @param RmaEntityFactory $returnsEntity
     * @param RmaFactory $coreRmaEntity
     * @param CreditmemoNotifier $creditmemoNotifier
     * @param ReturnsCreditMemoIdsFactory $customCreditmemo
     * @param \Magento\Sales\Model\OrderFactory $orderModel
     * @param \I95DevConnect\MessageQueue\Model\SalesOrderFactory $customSalesOrder
     * @param \Magento\Sales\Api\OrderManagementInterface $orderManagement
     * @param \Magento\Sales\Model\Service\CreditmemoService $creditmemoService
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender $commentEmailSender
     * @param \I95DevConnect\MessageQueue\Helper\Data $dataHelper
     * @param \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \I95DevConnect\Returns\Helper\Data $data
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Customer\Api\CustomerRepositoryInterfaceFactory $customerRepositoryInterfaceFactory
     * @param \I95DevConnect\MessageQueue\Helper\Generic $generic
     * @param \I95DevConnect\MessageQueue\Model\ResourceModel\I95DevInvoiceHistory\CollectionFactory $invoiceHistory
     */
    public function __construct( // NOSONAR
        \Magento\Framework\Json\Decoder $jsonDecoder,
        \I95DevConnect\MessageQueue\Api\I95DevResponseInterfaceFactory $i95DevResponse,
        \I95DevConnect\MessageQueue\Model\ErrorUpdateDataFactory $messageErrorModel,
        \I95DevConnect\MessageQueue\Api\Data\I95DevErpMQInterfaceFactory $i95DevErpMQ,
        \I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory $logger,
        \I95DevConnect\MessageQueue\Api\I95DevErpMQRepositoryInterfaceFactory $i95DevErpMQRepository,
        DateTime $date,
        \Magento\Framework\Event\Manager $eventManager,
        \I95DevConnect\MessageQueue\Model\DataPersistence\Validate $validate,
        \I95DevConnect\MessageQueue\Api\I95DevErpDataRepositoryInterfaceFactory $i95DevERPDataRepository,
        RmaEntityFactory $returnsEntity,
        RmaFactory $coreRmaEntity,
        CreditmemoNotifier $creditmemoNotifier,
        ReturnsCreditMemoIdsFactory $customCreditmemo,
        \Magento\Sales\Model\OrderFactory $orderModel,
        \I95DevConnect\MessageQueue\Model\SalesOrderFactory $customSalesOrder,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Sales\Model\Service\CreditmemoService $creditmemoService,
        \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender $commentEmailSender,
        \I95DevConnect\MessageQueue\Helper\Data $dataHelper,
        \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \I95DevConnect\Returns\Helper\Data $data,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Customer\Api\CustomerRepositoryInterfaceFactory $customerRepositoryInterfaceFactory,
        \I95DevConnect\MessageQueue\Helper\Generic $generic,
        \I95DevConnect\MessageQueue\Model\ResourceModel\I95DevInvoiceHistory\CollectionFactory $invoiceHistory
    ) {
        $this->_returnsEntity = $returnsEntity;
        $this->_customCreditmemo = $customCreditmemo;
        $this->coreRmaEntity = $coreRmaEntity;
        $this->creditmemoNotifier = $creditmemoNotifier;
        $this->orderFactory = $orderModel;
        $this->customSalesOrder = $customSalesOrder;
        $this->orderManagement = $orderManagement;
        $this->creditmemoService = $creditmemoService;
        $this->commentEmailSender = $commentEmailSender;
        $this->dataHelper = $dataHelper;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->storeManager = $storeManager;
        $this->orderRepository = $orderRepository;
        $this->data = $data;
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerRepositoryInterfaceFactory = $customerRepositoryInterfaceFactory;
        $this->generic = $generic;
        $this->invoiceHistory = $invoiceHistory;
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
     * Return Creditmemo
     *
     * @param string $stringData
     * @return \I95DevConnect\MessageQueue\Api\I95DevResponseInterface
     * @throws \Exception
     */
    public function returnCreditmemo($stringData)
    {
            $this->setStringData($stringData);
            $this->logger->create()->createLog(
                __METHOD__,
                $this->stringData,
                LoggerInterface::MSGLOGNAME,
                'info'
            );
        try {
            $this->validate();
            $this->orderId = $this->order->getEntityId();
            $paymentDetails = $this->order->getPayment();
            $transactionDetails = $paymentDetails->getAdditionalInformation();
            $transactionType = '';
            if (isset($transactionDetails[self::TRANSTYPE])) {
                $transactionType = $transactionDetails[self::TRANSTYPE];
            }

            if (strtolower($transactionType) === 'auth_capture') {
                $creditMemoId = $this->updateOrderByReturn();
            } else {
                /*
                 * Subhan
                $result = $this->cancelMagentoOrder();
                if (!$result) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __($this->errorMsg)
                    );
                }
                $this->updateCustomerCreditLimit();
                */
                $creditMemoId = $this->order->getIncrementId();
            }

            $creditMemoId = $this->updateOrderByReturn();
            if ($this->order->getStatus() !== 'canceled') {
                $this->commentEmailSender->send($this->order, true);
            }

            return $this->setResponse(
                \I95DevConnect\MessageQueue\Helper\Data::SUCCESS,
                "Record Successfully Synced",
                $creditMemoId
            );
        } catch (LocalizedException $ex) {
            return $this->setResponse(
                \I95DevConnect\MessageQueue\Helper\Data::ERROR,
                __($ex->getMessage()),
                null
            );
        }
    }

    /**
     * Process processing status without invoice
     *
     * @return mixed
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
     * For processing order
     *
     * @return mixed
     * @throws \Exception
     */
    public function updateOrderByReturn()
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
     * Process processing status with partial invoice
     *
     * @param array $creditmemoResult
     * @throws \Exception
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
                $this->updateOrder($this->order);
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
        $loadReturns = $this->_returnsEntity
                        ->create()
                        ->load($this->stringData[self::TARGETRETURNID], 'target_return_id');

        $orderData = $this->customSalesOrder->create()->load($loadReturns->getTargetOrderId(), 'target_order_id');

        $this->order = $this->orderFactory->create()->loadByIncrementId($orderData->getSourceOrderId());
        $this->orderStatus = $this->order->getStatus();
        if (!$this->order->getEntityId()) {
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
     * Creates credit memo for cancel order
     *
     * @param string $isPartial
     * @return array
     * @throws LocalizedException
     */
    public function refundOrder($isPartial)
    {
        $order = $this->order;
        $status = true;
        $paymentDetails = $order->getPayment();
        $paymentMethod = $paymentDetails->getMethod($this->order);
        $paymentAdditionalInfo = $paymentDetails->getAdditionalInformation();

        $isAuthorize = 0;
        if (isset($paymentAdditionalInfo["payment_type"]) && $paymentDetails["payment_type"] == "authorize") {
            $isAuthorize = 1;
        }

        $refundShipping = $this->stringData['refundShipping'] ?? 0;

        $dataArray = [
            'do_offline' => 0,
            'adjustment_positive' => 0,
            'base_shipping_amount' => $refundShipping,
            'adjustment_negative' => 0,
            'refund_customerbalance_return_enable' => 0,
            'send_email' => 1,
        ];

        $QtytoReturn = $this->getQtytoReturn($this->order);
        $itemToCredit = $QtytoReturn[self::ITEMS];
        $qtys = $QtytoReturn['qtys'];
        $dataArray['shipping_amount'] = $refundShipping;
        $dataArray [self::ITEMS] = $itemToCredit;
        $dataArray['qtys'] = $qtys;

        if (!empty($this->order->getInvoiceCollection())) {
            // @updatedBy Subhan
            $msg[] = 'success';
            return $this->createCreditMemo($paymentMethod, $isAuthorize, $status, $order, $dataArray, $msg);
        } else {
            $msg[] = 'Sorry,Credit memo cant be created';
            $status = false;
            return [self::STATUS => $status, self::MESSAGE => $msg, self::CREDITMEMOID => $this->creditMemoId];
        }
    }

    /**
     * Create credit memo
     *
     * @param string $paymentMethod
     * @param string $isAuthorize
     * @param string $status
     * @param object $order
     * @param array $dataArray
     * @param string $msg
     * @return array
     */
    public function createCreditMemo($paymentMethod, $isAuthorize, $status, $order, $dataArray, $msg)
    {
        $invoices = $order->getInvoiceCollection();
        if (count($invoices) == 0) {
            throw new LocalizedException(
                __('No Invoice found')
            );
        }
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

            try {
                $this->creditmemoNotifier->notify($creditMemoData);
            } catch (LocalizedException $ex) {
                $this->logger->create()->createLog(
                    __METHOD__,
                    $ex->getMessage(),
                    LoggerInterface::I95EXC,
                    'error'
                );
            }

            $loadReturns = $this->_returnsEntity
                ->create()
                ->load($this->stringData[self::TARGETRETURNID], 'target_return_id');

            $customMemo = $this->_customCreditmemo->create()
                ->load($this->stringData['targetId'], 'creditmemo_id');
            $customMemo->setCreditmemoId($this->stringData['targetId']);
            $customMemo->setReturnId($loadReturns->getReturnId());
            $customMemo->setMagentoCreditmemoId($this->creditMemoId);
            $customMemo->setCreatedDt($this->date->gmtDate());
            $customMemo->setUpdatedDt($this->date->gmtDate());
            $customMemo->save();
            $coreRMA = $this->coreRmaEntity->create()->load($loadReturns->getReturnId());
            if ($coreRMA->getStatus() == "received") {
                if ($coreRMA->canClose()) {
                    $coreRMA->close()->save();
                }
            }

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

    /**
     * Updates custom order info
     */
    public function updateCustomOrder()
    {
        $this->loadCustomOrder->setTargetOrderStatus("Canceled");
        $this->loadCustomOrder->setUpdateBy("ERP");
        $this->loadCustomOrder->save();
    }

    /**
     * Updates customer credit limit
     *
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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
     * @param string $targetId
     * @return object $customerInfo
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
                \I95DevConnect\MessageQueue\Api\LoggerInterface::I95EXC,
                self::CRITICAL
            );
            throw new LocalizedException(__($ex->getMessage()));
        }

        return $customerInfo;
    }

    /**
     * Check if order partially shipped
     *
     * @param object $orderData
     * @return int
     */
    public function checkIspartiallyShipped($orderData)
    {
        $isPartial = 0;
        $order = $this->orderRepository->get($orderData->getId());
        //@ Hrusieksh check wheather the order has Shipment or not
        if ($order->hasShipments()) {
            foreach ($order->getAllVisibleItems() as $item) {
                if ($item->getQtyInvoiced() != $item->getQtyShipped()) {
                    $isPartial++;
                }
            }
        }
        return $isPartial;
    }

    /**
     * Calculate quantity to return
     *
     * @param object $orderData
     * @return array
     */
    public function getQtytoReturn($orderData)
    {
        $order = $this->orderRepository->get($orderData->getId());
        if (isset($this->stringData['cancelItemEntity'])) {
            $cancelItems = $this->stringData['cancelItemEntity'];
            $orderItemData = [];
            $bundleProducts = [];
            $configProducts = [];
            foreach ($order->getAllItems() as $item) {
                if ($item->getParentItemId() > 0 && $item->getParentItem()->getProductType() == 'configurable') {
                    $orderItemData[strtolower(trim($item->getSku()))] = $item->getParentItem()->getId();
                } elseif ($item->getProductType() == 'bundle') {
                    $bundleProducts[$item->getId()] = $item->getQtyOrdered();
                } elseif ($item->getProductType() == 'configurable') {
                    $configProducts[strtolower(trim($item->getSku()))] = $item->getId();
                } else {
                    $orderItemData[strtolower(trim($item->getSku()))] = $item->getId();
                }
            }
            $itemToCredit = [];
            $qtys = [];
            foreach ($cancelItems as $cancelItem) {
                $itemToCredit[$orderItemData[strtolower(trim($cancelItem['orderItemId']))]] = [
                    'qty' => $cancelItem['quantityToCancel']
                ];
                $qtys[$orderItemData[strtolower(trim($cancelItem['orderItemId']))]] = $cancelItem['quantityToCancel'];
            }
            if (!empty($bundleProducts)) {
                foreach ($bundleProducts as $itemId => $qty) {
                    $itemToCredit[$itemId] = [
                        'qty' => $qty
                    ];
                    $qtys[$itemId] = $qty;
                }
            }

            return [self::ITEMS => $itemToCredit, 'qtys' => $qtys];
        } else {
            return $this->getCancelQtyFromInvoiceHistory($order);
        }
    }

    /**
     * Update order state after create credit memo
     *
     * @param object $order
     * @throws \Exception
     */
    public function updateOrder($order)
    {
        /**
         * @author Debashis S. Gopal. Added observer skip
         **/
        $this->dataHelper->unsetGlobalValue(self::I95OSKIP);
        $this->dataHelper->setGlobalValue(self::I95OSKIP, true);
        $orderObj = $this->orderFactory->create()->load($order->getId());
        $orderObj->setState(\Magento\Sales\Model\Order::STATE_CLOSED);
        $orderObj->setStatus(\Magento\Sales\Model\Order::STATE_CLOSED);

        try {
            $orderObj->save();
            $this->commentEmailSender->send($orderObj, true, "");
        } catch (\Zend_Mail_Exception $ex) {
            $message = $ex->getMessage();
            $this->logger->create()->createLog(
                __METHOD__,
                $message,
                LoggerInterface::I95EXC,
                self::CRITICAL
            );
        } catch (LocalizedException $ex) {
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
     * Get canceled item  quantity from order
     *
     * @param object $orderObj
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
     * Get shipment Ids
     *
     * @param object $orderData
     * @return array
     */
    public function getShipmentIds($orderData)
    {
        $order = $this->orderRepository->get($orderData->getId());

        $shipmentCollection = $order->getShipmentsCollection();
        $shipmentIds = [];
        foreach ($shipmentCollection as $shipment) {
            $customshipment = $this->generic->getCustomShipmentById($shipment['increment_id']);
            $shipmentIds[] = $customshipment->gettargetShipmentId();
        }
        return $shipmentIds;
    }

    /**
     * Get invoiceIds
     *
     * @param object $orderData
     * @return array
     */
    public function getInvoiceIds($orderData)
    {
        $order = $this->orderRepository->get($orderData->getId());

        $invoiceCollection = $order->getInvoiceCollection();
        $invoiceIds = [];
        foreach ($invoiceCollection as $invoice) {
            $custominvoice = $this->generic->getCustomInvoiceById($invoice['increment_id']);
            $invoiceIds[] = $custominvoice->gettargetInvoiceId();
        }
        return $invoiceIds;
    }

    /**
     * Get Canceled Item Qty from Custom Invoice History
     *
     * @param object $orderObj
     * @return array[]
     * @addedBy Subhan
     */
    public function getCancelQtyFromInvoiceHistory($orderObj)
    {
        $orderItems = $orderObj->getAllItems();
        $itemToCredit = [];
        $qtys = [];
        foreach ($orderItems as $item) {
            $qtyInvoiced = $this->getQtyInvoicedFromHistory($this->stringData[self::TARGETID], $item->getSku());
            $qtyToCancel = $item->getQtyOrdered() - (int) $qtyInvoiced;
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
     * @param string $orderTargetId
     * @param string $itemSku
     * @return mixed
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
}

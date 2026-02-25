<?php
/**
 * Century Business Solutions
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the centurybizsolutions.com license that is
 * available through the URL:  https://www.centurybizsolutions/License.txt
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file to upgrade this extension to newer
 * version in the future please contact to CENTURY BUSINESS SOLUTIONS.
 *
 * @category    Ebizcharge
 * @package     Ebizcharge_Ebizcharge
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Model\Order;

use Exception;
use Ebizcharge\Ebizcharge\Api\Data\CustomerInterface;
use Ebizcharge\Ebizcharge\Api\Data\InvoiceInterface as EbizInvoiceInterface;
use Ebizcharge\Ebizcharge\Api\Data\OrderInterface;
use Ebizcharge\Ebizcharge\Api\Data\PaymentInterface;
use Ebizcharge\Ebizcharge\Api\Data\SurchargeInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Ebizcharge\Ebizcharge\Model\Order as EbizOrder;
use Ebizcharge\Ebizcharge\Model\OrderFactory as EbizOrderFactory;
use Ebizcharge\Ebizcharge\Model\Order\Invoice as EbizInvoiceModel;
use Ebizcharge\Ebizcharge\Model\ProductFactory;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Backend\Helper\Data;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Math\CalculatorFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Sales\Api\Data\InvoiceCommentInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\InvoiceManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice as InvoiceModel;
use Magento\Sales\Model\Order\Invoice\CommentFactory;
use Magento\Sales\Model\Order\Invoice\Config;
use Magento\Sales\Model\Order\Invoice\Item;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Invoice as ResourceModelInvoice;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Comment\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Item\CollectionFactory as InvoiceCollectionFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use SoapFault;

/**
 * Order Invoice Model class
 *
 * Class Invoice
 */
class Invoice extends InvoiceModel implements EbizInvoiceInterface
{
    /**
     * Invoice Type
     *
     * @const: INVOICE_TYPE
     */
    public const INVOICE_TYPE = 'Invoice';

    /**
     * States array
     *
     * @var array
     */
    protected static $_states;

    /**
     * Identifier for history item
     *
     * @var string
     */
    protected $entityType = 'invoice';

    /**
     * Calculator instances for delta rounding of prices
     *
     * @var array
     */
    protected $_rounders = [];

    /**
     * Bool
     *
     * @var bool
     */
    protected $_saveBeforeDestruct = false;

    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_order_invoice';

    /**
     * @var string
     */
    protected $_eventObject = 'invoice';

    /**
     * Whether the pay() was called
     *
     * @var bool
     */
    protected $_wasPayCalled = false;

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var Config
     */
    protected $_invoiceConfig;

    /**
     * @var OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var CalculatorFactory
     */
    protected $_calculatorFactory;

    /**
     * @var InvoiceCollectionFactory
     */
    protected $_invoiceItemCollectionFactory;

    /**
     * @var CommentFactory
     */
    protected $_invoiceCommentFactory;

    /**
     * @var CollectionFactory
     */
    protected $_commentCollectionFactory;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var TranApi
     */
    protected TranApi $_soapApiModel;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $_storeManager;

    /**
     * @var TransactionFactory
     */
    protected TransactionFactory $_transactionFactory;

    /**
     * @var InvoiceManagementInterface
     */
    protected InvoiceManagementInterface $_invoiceManagement;

    /**
     * @var OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $_orderRepository;

    /**
     * @var InvoiceService
     */
    protected InvoiceService $_invoiceService;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * @var Data
     */
    protected Data $_backendHelper;

    /**
     * @var ProductFactory
     */
    protected ProductFactory $_productFactory;

    /**
     * @var EbizOrderFactory
     */
    protected EbizOrderFactory $_ebizOrderFactory;

    /**
     * Invoice constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Config $invoiceConfig
     * @param OrderFactory $orderFactory
     * @param CalculatorFactory $calculatorFactory
     * @param InvoiceCollectionFactory $invoiceItemCollectionFactory
     * @param CommentFactory $invoiceCommentFactory
     * @param CustomerFactory $customerFactory
     * @param InvoiceManagementInterface $invoiceManagement
     * @param TransactionFactory $transactionFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceService $invoiceService
     * @param CollectionFactory $commentCollectionFactory
     * @param EbizchargeLogger $ebizchargeLogger
     * @param Data $backendHelper
     * @param ProductFactory $productFactory
     * @param TranApi $soapApiModel
     * @param StoreManagerInterface $storeManager
     * @param EbizOrderFactory $ebizOrderFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context                    $context,
        Registry                   $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory      $customAttributeFactory,
        Config                     $invoiceConfig,
        OrderFactory               $orderFactory,
        CalculatorFactory          $calculatorFactory,
        InvoiceCollectionFactory   $invoiceItemCollectionFactory,
        CommentFactory             $invoiceCommentFactory,
        CustomerFactory            $customerFactory,
        InvoiceManagementInterface $invoiceManagement,
        TransactionFactory         $transactionFactory,
        OrderRepositoryInterface   $orderRepository,
        InvoiceService             $invoiceService,
        CollectionFactory          $commentCollectionFactory,
        EbizchargeLogger           $ebizchargeLogger,
        Data                       $backendHelper,
        ProductFactory             $productFactory,
        TranApi                    $soapApiModel,
        StoreManagerInterface      $storeManager,
        EbizOrderFactory           $ebizOrderFactory,
        ?AbstractResource           $resource = null,
        ?AbstractDb                 $resourceCollection = null,
        array                      $data = []
    )
    {
        /** @var $_invoiceConfig */
        $this->_invoiceConfig = $invoiceConfig;
        /** @var $_orderFactory */
        $this->_orderFactory = $orderFactory;
        /** @var $_calculatorFactory */
        $this->_calculatorFactory = $calculatorFactory;
        /** @var $_invoiceItemCollectionFactory */
        $this->_invoiceItemCollectionFactory = $invoiceItemCollectionFactory;
        /** @var $_invoiceCommentFactory */
        $this->_invoiceCommentFactory = $invoiceCommentFactory;
        /** @var $_commentCollectionFactory */
        $this->_commentCollectionFactory = $commentCollectionFactory;
        /** @var $_ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var $_soapApiModel */
        $this->_soapApiModel = $soapApiModel;
        /** @var $_storeManager */
        $this->_storeManager = $storeManager;
        /** @var $_transactionFactory */
        $this->_transactionFactory = $transactionFactory;
        /** @var $_invoiceManagement */
        $this->_invoiceManagement = $invoiceManagement;
        /** @var $_orderRepository */
        $this->_orderRepository = $orderRepository;
        /** @var $_invoiceService */
        $this->_invoiceService = $invoiceService;
        /** @var $_customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var $_backendHelper */
        $this->_backendHelper = $backendHelper;
        /** @var  $_productFactory */
        $this->_productFactory = $productFactory;
        /** @var $_orderFactory */
        $this->_ebizOrderFactory = $ebizOrderFactory;

        /**
         * Parent:construct
         */
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $invoiceConfig,
            $orderFactory,
            $calculatorFactory,
            $invoiceItemCollectionFactory,
            $invoiceCommentFactory,
            $commentCollectionFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Get Software Id
     *
     * @return string
     */
    public function getSoftwareId(): string
    {
        return $this->getData(self::EBIZCHARGE_SOFTWARE_ID);
    }

    /**
     * Set Software Id
     *
     * @param mixed $ecSoftwareId
     * @return EbizInvoiceInterface
     */
    public function setSoftwareId($ecSoftwareId): EbizInvoiceInterface
    {
        return $this->setData(self::EBIZCHARGE_SOFTWARE_ID, $ecSoftwareId);
    }

    /**
     * Get Division Id
     *
     * @return string
     */
    public function getDivisionId(): string
    {
        return $this->getData(self::EBIZCHARGE_DIVISION_ID);
    }

    /**
     * Get Ec Customer Id
     *
     * @return mixed|null
     */
    public function getEcCustId(): mixed
    {
        return $this->getData(self::EBIZ_CUSTOMER_ID);
    }

    /**
     * Get EBizCharge Invoice Id
     *
     * @return mixed|null
     */
    public function getEcInvoiceId(): mixed
    {
        return $this->getData(self::EBIZ_INVOICE_ID);
    }

    /**
     * Set EBizCharge Invoice Id
     *
     * @param mixed $ebizInvoiceId
     * @return Invoice
     */
    public function setEcInvoiceId($ebizInvoiceId): Invoice
    {
        return $this->setData(self::EBIZ_INVOICE_ID, $ebizInvoiceId);
    }

    /**
     * Set EBizCharge Invoice Internal Id
     *
     * @param mixed $ebizInvoiceInternalId
     * @return Invoice
     */
    public function setEcInvoiceInternalid(mixed $ebizInvoiceInternalId): Invoice
    {
        return $this->setData(self::EBIZ_INVOICE_INTERNAL_ID, $ebizInvoiceInternalId);
    }

    /**
     * Get Ec Invoice Last Sync
     *
     * @return mixed|null
     */
    public function getEcInvoiceLastsyncdate(): mixed
    {
        return $this->getData(self::EBIZ_INVOICE_LASTSYNCDATE);
    }

    /**
     * Set Ebiz Invoice Last Syunc Date
     *
     * @param mixed $ebizInvoiceLastSyncDate
     * @return Invoice
     */
    public function setEcInvoiceLastsyncdate(mixed $ebizInvoiceLastSyncDate): Invoice
    {
        return $this->setData(self::EBIZ_INVOICE_LASTSYNCDATE, $ebizInvoiceLastSyncDate);
    }

    /**
     * Get EBizCharge Invoice Sync Status
     *
     * @return mixed|null
     */
    public function getEcInvoiceSyncStatus(): mixed
    {
        return $this->getData(self::EBIZ_INVOICE_SYNC_STATUS);
    }

    /**
     * Set EBizCharge Invoice Sync Status
     *
     * @param mixed $ebizInvoiceStatus
     * @return Invoice
     */
    public function setEcInvoiceSyncStatus(mixed $ebizInvoiceStatus): Invoice
    {
        return $this->setData(self::EBIZ_INVOICE_SYNC_STATUS, $ebizInvoiceStatus);
    }

    /**
     * Load invoice by increment id
     *
     * @param string $incrementId
     * @return $this
     * @throws LocalizedException
     */
    public function loadByIncrementId($incrementId): static
    {
        $ids = $this->getCollection()->addAttributeToFilter('increment_id', $incrementId)->getAllIds();

        if (!empty($ids)) {
            reset($ids);
            $this->load(current($ids));
        }
        return $this;
    }

    /**
     * Return order history item identifier
     *
     * @return string
     */
    public function getEntityType(): string
    {
        return $this->entityType;
    }

    /**
     * Check invoice cancel state
     *
     * @return bool
     */
    public function isCanceled(): bool
    {
        return $this->getState() == self::STATE_CANCELED;
    }

    /**
     * Check invoice capture action availability
     *
     * @return bool
     */
    public function canCapture(): bool
    {
        return $this->getState() != self::STATE_CANCELED &&
            $this->getState() != self::STATE_PAID &&
            $this->getOrder()->getPayment()->canCapture();
    }

    /**
     * Retrieve the order the invoice for created for
     *
     * @return Order
     */
    public function getOrder(): Order
    {
        if (!$this->_order instanceof Order) {
            $this->_order = $this->_ebizOrderFactory->create()->load($this->getOrderId());
        }
        return $this->_order->setHistoryEntityName($this->entityType);
    }

    /**
     * Declare order for invoice
     *
     * @param Order $order
     * @return $this
     */
    public function setOrder(Order $order): static
    {
        $this->_order = $order;
        $this->setOrderId($order->getId())->setStoreId($order->getStoreId());
        return $this;
    }

    /**
     * Check invoice void action availability
     *
     * @return bool
     */
    public function canVoid(): bool
    {
        if ($this->getState() == self::STATE_PAID) {
            if ($this->getCanVoidFlag() === null) {
                return (bool)$this->getOrder()->getPayment()->canVoid();
            }
        }
        return (bool)$this->getCanVoidFlag();
    }

    /**
     * Check invoice cancel action availability
     *
     * @return bool
     */
    public function canCancel(): bool
    {
        return $this->getState() == self::STATE_OPEN;
    }

    /**
     * Check invoice refund action availability
     *
     * @return bool
     */
    public function canRefund(): bool
    {
        if ($this->getState() != self::STATE_PAID) {
            return false;
        }
        if (abs($this->getBaseGrandTotal() - $this->getBaseTotalRefunded()) < .0001) {
            return false;
        }
        return true;
    }

    /**
     * Pay invoice
     *
     * @return $this
     */
    public function pay(): static
    {
        if ($this->_wasPayCalled) {
            return $this;
        }
        $this->_wasPayCalled = true;

        $this->setState(self::STATE_PAID);

        $order = $this->getOrder();
        $order->getPayment()->pay($this);
        $totalPaid = $this->getGrandTotal();
        $baseTotalPaid = $this->getBaseGrandTotal();
        $invoiceList = $order->getInvoiceCollection();
        // calculate all totals
        if (count($invoiceList->getItems()) > 1) {
            $totalPaid += $order->getTotalPaid();
            $baseTotalPaid += $order->getBaseTotalPaid();
        }
        $order->setTotalPaid($totalPaid);
        $order->setBaseTotalPaid($baseTotalPaid);
        $this->_eventManager->dispatch('sales_order_invoice_pay', [$this->_eventObject => $this]);
        return $this;
    }

    /**
     * Whether pay() method was called (whether order and payment totals were updated)
     *
     * @return bool
     */
    public function wasPayCalled(): bool
    {
        return $this->_wasPayCalled;
    }

    /**
     * Void invoice
     *
     * @return $this
     */
    public function void(): static
    {
        $this->getOrder()->getPayment()->void($this);
        $this->cancel();
        return $this;
    }

    /**
     * Invoice totals collecting
     *
     * @return $this
     */
    public function collectTotals(): static
    {
        foreach ($this->getConfig()->getTotalModels() as $model) {
            $model->collect($this);
        }
        return $this;
    }

    /**
     * Retrieve invoice configuration model
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->_invoiceConfig;
    }


    /**
     * Round price considering delta
     * Indicates if we perform addition (true) or subtraction (false) of rounded value
     *
     * @param mixed $price
     * @param mixed $type
     * @param mixed $negative
     * @return float
     */
    public function roundPrice(mixed $price, mixed $type = 'regular', mixed $negative = false): float
    {
        if ($price) {
            if (!isset($this->_rounders[$type])) {
                $this->_rounders[$type] = $this->_calculatorFactory->create(['scope' => $this->getStore()]);
            }
            $price = $this->_rounders[$type]->deltaRound($price, $negative);
        }
        return $price;
    }

    /**
     * Retrieve store model instance
     *
     * @return Store
     */
    public function getStore(): Store
    {
        return $this->getOrder()->getStore();
    }

    /**
     * Get Invoice Item by id.
     *
     * @param  $itemId
     * @return bool|Item
     */
    public function getItemById(mixed $itemId): Item|bool
    {
        foreach ($this->getItemsCollection() as $item) {
            if ($item->getId() == $itemId) {
                return $item;
            }
        }
        return false;
    }

    /**
     * Retrieve invoice state name by state identifier
     *
     * @param mixed $stateId
     * @return Phrase
     */
    public function getStateName(mixed $stateId = null): Phrase
    {
        if ($stateId === null) {
            $stateId = $this->getState();
        }

        if (null === static::$_states) {
            static::getStates();
        }
        if (isset(static::$_states[$stateId])) {
            return static::$_states[$stateId];
        }
        return __('Unknown State');
    }

    /**
     * Retrieve invoice states array
     *
     * @return array
     * @phpcs:disable
     */
    public static function getStates(): array
    {
        // phpcs:enable
        if (null === static::$_states) {
            static::$_states = [
                self::STATE_OPEN => __('Pending'),
                self::STATE_PAID => __('Paid'),
                self::STATE_CANCELED => __('Canceled'),
            ];
        }
        return static::$_states;
    }

    /**
     * Checking if the invoice is last
     *
     * @return bool
     */
    public function isLast(): bool
    {
        foreach ($this->getAllItems() as $item) {
            if (!$item->isLast()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get all Invoice Items.
     *
     * @return array
     */
    public function getAllItems(): array
    {
        $items = [];
        foreach ($this->getItemsCollection() as $item) {
            if (!$item->isDeleted()) {
                $items[] = $item;
            }
        }
        return $items;
    }

    /**
     * Reset invoice object
     *
     * @return $this
     */
    public function reset()
    {
        $this->unsetData();
        $this->_origData = null;
        $this->setItems(null);
        $this->setComments(null);
        $this->_order = null;
        $this->_saveBeforeDestruct = false;
        $this->_wasPayCalled = false;
        return $this;
    }

    /**
     * Return invoice comments
     *
     * @return InvoiceCommentInterface[]|null
     */
    public function getComments()
    {
        if ($this->getData(InvoiceInterface::COMMENTS) === null && $this->getId()) {
            $collection = $this->_commentCollectionFactory->create()->setInvoiceFilter($this->getId());
            foreach ($collection as $comment) {
                $comment->setInvoice($this);
            }
            $this->setData(InvoiceInterface::COMMENTS, $collection->getItems());
        }
        return $this->getData(InvoiceInterface::COMMENTS);
    }

    /**
     *  Upload Invoices to EbizCharge
     *
     * @param EbizOrder|null $order
     * @return array
     * @throws NoSuchEntityException
     */
    public function uploadInvoicesToEbizchargeTEMP(?EbizOrder $order = null): array
    {
        /** @var  $salesInvoiceResp */
        $salesInvoiceResp = [
            'status' => 'Error',
            'message' => __(''),
            'error' => true,
            "ebiz_internal_id" => "",
            'ebiz_order_number' => $order->getIncrementId(),
            OrderInterface::EBIZCHARGE_DIVISION_ID => $this->_soapApiModel->getDivisionId(),
            OrderInterface::EBIZCHARGE_SOFTWARE_ID => $this->_soapApiModel->getSoftwareId()
        ];

        try {
            $storeId = $this->getStoreId();
            /** @var  $securityToken */
            $securityToken = $this->_soapApiModel->getUeSecurityToken($storeId);

            /** @var  $orderNumber */
            $orderNumber = $order->getIncrementId();

            $order = $this->_ebizOrderFactory->create()->loadByIncrementId($orderNumber);
            /** @var  $invoices */
            $invoices = $order->getInvoiceCollection();

            if (count($invoices) > 0) {
                foreach ($invoices as $invoice) {
                    /** @var  $invoiceParams */
                    $invoiceParams = $this->prepareInvoiceParams($invoice);

                    /** @var $ebizchargeInvoiceParams */
                    $ebizchargeInvoiceParams = [
                        'securityToken' => $securityToken,
                        'invoice' => $invoiceParams
                    ];

                    /** @var $orderResponse */
                    $invoiceResponse = $this->_soapApiModel->getClient()->AddInvoice($ebizchargeInvoiceParams);
                    $invoiceResponse = (array)$invoiceResponse->AddInvoiceResult;

                    if (isset($invoiceResponse["Status"]) && $invoiceResponse["StatusCode"] === 1) {

                        $salesInvoiceResp = [
                            'status' => 'Success',
                            'message' => __('Success, the invoice has been created successfully'),
                            'error' => false,
                            "ebiz_internal_id" => $invoiceResponse['InvoiceInternalId'],
                            'ebiz_order_number' => $orderNumber,
                            OrderInterface::EBIZCHARGE_DIVISION_ID => $this->_soapApiModel->getDivisionId(),
                            OrderInterface::EBIZCHARGE_SOFTWARE_ID => $this->_soapApiModel->getSoftwareId()
                        ];
                    }
                }
            }
        } catch (LocalizedException $exception) {
            $this->_ebizchargeLogger->addCritical(__(
                "Exception occurred during upload of Invoice: " . $exception->getMessage()
            ));
        }
        return $salesInvoiceResp;
    }

    /**
     * Prepare Invocie Params
     *
     * @param Invoice|null $invoice
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function prepareInvoiceParams(?EbizInvoiceModel $invoice = null): array
    {
        /** @var  $storeId */
        $storeId = $this->getStoreId();
        /** @var $store */
        $store = $this->_storeManager->getStore();
        $orderId = $invoice->getOrderId();
        $orderFactory = $this->_ebizOrderFactory->create();
        $order = $orderFactory->load($orderId);
        $storeId = $order->getStoreId() ? $order->getStoreId() : $store->getId();

        /** @var $securityToken */
        $securityToken = $this->_soapApiModel->getUeSecurityToken($storeId);
        $orderNumber = $order->getIncrementId();

        /** @var  $orderItems */
        $orderItems = $order->getAllItems();
        $taxAmount = $order->getTaxAmount();

        /** @var $shippingDescription */
        $shippingDescription = $order->getShippingDescription();
        $storeName = $this->_storeManager->getStore()->getName();
        $shippingMethod = $order->getShippingMethod();

        /** @var $ebizCustomerInternalId */
        $customerEmail = $order->getCustomerEmail();

        /** Get Customer Email and get Ebizcharge Customer Id */
        if (!$customerEmail) {
            throw new LocalizedException(__('Customer Email doest not exist in this order'));
        }
        /** @var  $customer */
        $customer = $this->_customerFactory->create()->loadByEmail($customerEmail);
        /** @var  $ebizCustomerId */
        $ebizCustomerInternalId = $customer->getEcCustInternalId();
        $ebizCustomerId = $customer->getEbizCustomerById();

        if (!$ebizCustomerInternalId) {
            /** @var $ebizCustomer */
            $ebizCustomer = $this->_customerFactory->create()->saveLocalCustomerToEbizcharge($customer);

            /** Ebiz Customer  */
            if ($ebizCustomer['status'] === 'Success') {
                $ebizCustomerInternalId = $ebizCustomer[CustomerInterface::EBIZCHARGE_CUSTOMER_INTERNAL_ID] ?? '';
            }
        }

        /**
         * Preparing Invoice Params
         */
        /** @var  $invoiceParams */
        $invoiceParams = [
            'MerchantId' => $this->getStore()->getName(),
            'CustomerId' => $orderFactory->getOrderCustomerId($customer, $ebizCustomerInternalId),
            'SubCustomerId' => $orderFactory->getSubCustomerId($order),
            'InvoiceNumber' => $order->getIncrementId(),
            'InvoiceDate' => $order->getCreatedAt(),
            'Currency' => $order->getBaseCurrencyCode(),
            'InvoiceAmount' => (double)$order->getGrandTotal(),
            'InvoiceDueDate' => $order->getCreatedAt(),
            'AmountDue' => (double)$order->getTotalDue(),
            'PoNum' => $order->getIncrementId(),
            'SoNum' => $order->getIncrementId(),
            'DivisionId' => $this->_soapApiModel->getDivisionId($storeId),
            'TypeId' => "",
            'UploadedBy' => "",
            'UpdatedBy' => "",
            'DateUploaded' => $order->getCreatedAt(),
            'DateUpdated' => $order->getUpdatedAt(),
            'isDeleted' => "",
            'DeletedBy' => "",
            'DateDeleted' => "",
            'Items' => $orderFactory->addOrderItemToEbizcharge($orderItems),
            'Software' => $this->_soapApiModel->getSoftwareId(),
            'NotifyCustomer' => 0,
            'EmailTemplateID' => 2,
            'InvoiceURL' => $orderFactory->getWebsiteUrl(),
            'TotalTaxAmount' => (double)$order->getTaxAmount(),
            'InvoiceUniqueId' => $order->getIncrementId(),
            'InvoiceDescription' => "EBizCharge Invoice",
            'BillingAddress' => $orderFactory->prepareBillingAddress($order),
            'ShippingAddress' => $orderFactory->prepareShippingAddress($order),
            'InvoiceCustomerMessage' => "Invoice has been created",
            'InvoiceMemo' => "",
            'InvoiceShipDate' => $order->getCreatedAt(),
            'InvoiceShipVia' => $order->getShippingMethod(),
            'InvoiceTermsId' => "",
            'InvoiceSalesRepId' => "",
            'InvoiceIsToBeEmailed' => "",
            'InvoiceIsToBePrinted' => 0,
            'InvoiceLastSyncDateTime' => $invoice->getCreatedAt(),
            'DeliveryNumber' => "",
            'TotalDiscountAmount' => (double)$invoice->getDiscountAmount(),
            'TotalShippingAmount' => (double)$invoice->getShippingAmount(),
            'LocationId' => "",
            'RemitToAddress' => $orderFactory->prepareShippingAddress($order),
            'PoDate' => $invoice->getCreatedAt(),
            'OrderedBy' => $order->getCustomerName(),
            'InvoiceClass' => "",
            'InvoiceFOB' => "",
            'InvoiceTermsDescription' => $invoice->getDiscountDescription() ?? "",
            'InvoiceCustomFields' => [
                'EbizCustomField' => [
                    'FieldId' => "DivisionId",
                    'FieldCaption' => "Division Id",
                    'FieldName' => "DivisionId",
                    'FieldValue' => $this->_soapApiModel->getDivisionId($storeId),
                    'FieldType' => "text",
                    'FieldDataType' => "text",
                    'FieldDescription' => "Division Description",
                ]
            ]
        ];

        return $invoiceParams;
    }

    /**
     * Create Invoice With Transaction
     *
     * @param mixed $orderId
     * @return Invoice
     */
    public function createInvoiceWithTransaction(mixed $orderId): static
    {
        try {
            $order = $this->_orderRepository->get($orderId);
            /** upload invoices to EBizCharge */

            if ($order->canInvoice()) {
                /** @var  $invoice */
                $invoice = $this->_invoiceService->prepareInvoice($order);
                $invoice->setRequestedCaptureCase(Invoice::CAPTURE_ONLINE);

                /** register Invoice */
                $invoice->register();
                $invoice->getOrder()->setCustomerNoteNotify(false);
                $invoice->getOrder()->setIsInProcess(true);

                /** Order add comment to Status History */
                $order->addCommentToStatusHistory(__('Automatically Invoiced'), false);
                $invoice->setComments(__("Invoice has been generated at EBizCharge Hub."))
                    ->save()
                ;

                /** @var $transactionSave */
                $transactionSave = $this->_transactionFactory->create();
                $transactionSave->addObject($invoice)->addObject($invoice->getOrder());

                /** saving Invoice */
                $transactionSave->save();

                /** saving EBizCharge params to Invoice */
                $invoiceId = $invoice->getId();
                $invoice = $this->load($invoiceId);

                /** upload invoices to Ebizcharge */
             //   $this->createInvoiceToEbizcharge($orderId);

                /** @var  $transactions */
                //      $ebizTransactions = $this->addEbizPaymentTransactionsToOrder($orderId);

                $this->_ebizchargeLogger->info(__('Invoice created for order ' . $orderId));
                $this->_ebizchargeLogger->addInfo(__("Invoice created for order " . $orderId .
                    " EBizCharge Payment Gateway"));
            } else {
                $this->_ebizchargeLogger->addInfo("Invoice can not be created for order " . $orderId);
            }

        } catch (Exception $exception) {
            $this->_ebizchargeLogger->addCritical(__("Exception occurred during creating invoice Error:" .
                $exception->getMessage()));


        }
        return $this;
    }

    /**
     * Upload Invoice to EBizCharge
     *
     * @param $invoice
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function uploadInvoiceToEbizcharge($invoice = null)
    {
        /** @var  $invoiceResultResp */
        $invoiceResultResp = [
            'status' => "Error",
            'error' => true,
            'error_code' => "",
            'message' => "",
            'order_id' => "",
            'ec_invoice_id' => "",
            'ec_invoice_internalid' => "",
            'ec_cust_id' => ""
        ];

        try {
            if (!$invoice) {
                $invoiceResultResp["error"] = true;
                $invoiceResultResp["message"] = __("No invoice found to upload, please try again.");
                return $invoiceResultResp;
            }

            $order = $invoice->getOrder() ?? false;

            if (!$order) {
                $invoiceResultResp["error"] = true;
                $invoiceResultResp["message"] = __("No Order found against this invoice to upload, please try again.");
                return $invoiceResultResp;
            }
            $orderId = $order->getEntityId();
            $orderNumber = $order->getIncrementId();
            $orderPayment = $order->getPayment();

            if($orderPayment) {
                $paymentMethodCode = $orderPayment->getMethod();
                if (!str_contains($paymentMethodCode, "ebizcharge")) {
                    $invoiceResultResp["error"] = false;
                    $invoiceResultResp["message"] = __("Invoice does not contain EBizCharge payment method.");
                    $this->_ebizchargeLogger->addInfo($invoiceResultResp["message"]);
                    return $invoiceResultResp;
                }
            }

            /** @var  $order */
            $orderFactory = $this->_ebizOrderFactory->create();
            $order = $this->_ebizOrderFactory->create()->load($orderId);
            /** @var $store */
            $store = $this->_storeManager->getStore();
            $storeId = $order->getStoreId() ? $order->getStoreId() : $store->getId();


            /** @var $shippingDescription */
            $shippingDescription = $order->getShippingDescription() ?? "";
            $storeName = $store->getName()?? "";
            $shippingMethod = $order->getShippingMethod() ?? "";


            /** @var $ebizCustomerInternalId */
            $customerEmail = $order->getCustomerEmail() ?? "";
            $invoiceCustomerId = $order->getCustomerId() ?? "GUEST";
            $ebizCustomerId = "";
            $customerName = "";

            if ($customerEmail) {
                /** @var  $customer */
                $customer = $this->_customerFactory->create()->load($invoiceCustomerId);
                if ($customer) {
                    /** @var  $ebizCustomerId */
                    $ebizCustomerInternalId = $customer->getEcCustInternalId() ?? "";
                    $ebizCustomerId = $customer->getEcCustId() ?? "";
                    $customerName = $customer->getFirstname()." ".$customer->getLastname();
                }
            }

            $lineNumber = 0;
            $invoiceId = $order->getIncrementId() ?? "";
            $invoiceNumber = $order->getIncrementId() ?? "";
            $store = $this->_storeManager->getStore();
            $storeId = $order->getStoreId()?  $order->getStoreId(): $store->getId();
            $customerToken = $customer->getEcCustToken();
            $additionalInformations = $order->getPayment()->getAdditionalInformation();

            /** @var $securityToken get security token */
            $securityToken = $this->_soapApiModel->getUeSecurityToken($storeId);

            /** @var $invoiceParams */
            $invoiceParams = [
                'MerchantId' => (string)$store->getName() ?? "",
                'CustomerId' => (string)$ebizCustomerId ?? "",
                'SubCustomerId' => "",
                'InvoiceNumber' => $invoiceNumber ,
                'InvoiceDate' => $order->getCreatedAt() ?? "",
                'Currency' => $order->getBaseCurrencyCode() ?? "",
                'InvoiceAmount' => (float)$order->getGrandTotal() ?? "",
                'InvoiceDueDate' => $order->getCreatedAt() ?? "",
                'AmountDue' => (float)$order->getTotalDue() ?? "",
                'PoNum' => $order->getIncrementId() ?? "",
                'SoNum' => $order->getIncrementId() ?? "",
                'DivisionId' => $order->getDivisionId() ? $order->getDivisionId() : $this->_soapApiModel->getDivisionId($storeId),
                'TypeId' => EbizInvoiceInterface::INVOICE_TYPE,
                'Software' => $order->getSoftwareId() ? $order->getSoftwareId() : $this->_soapApiModel->getSoftwareId(),
                'NotifyCustomer' => false,
                'EmailTemplateID' => $order->getEmailTemplateId() ?? "",
                'InvoiceURL' => $this->getPdfUrl((int)$invoiceId) ?? "",
                'TotalTaxAmount' => $invoice->getTaxAmount() ?? "",
                'InvoiceUniqueId' => (string)$invoiceNumber ?? "",
                'ShippingAddress' => $this->getInvoiceShippingAddress($order) ?? "",
                'BillingAddress' => $this->getInvoiceBillingAddress($order) ?? "",
                'InvoiceCustomFields' => $this->getInvoiceCustomFields($invoice) ?? "",
                'TotalDiscountAmount' => (float)$invoice->getDiscountAmount() ?? "",
                'TotalShippingAmount' => (float)$invoice->getShippingAmount() ?? "",
                'LocationId' => "",
                'RemitToAddress' => $order->prepareShippingAddress($order) ?? "",
                'PoDate' => $order->getCreatedAt() ?? date("Y-m-d H:i:s"),
                'OrderedBy' => $customerName,
                'InvoiceClass' => "",
                'InvoiceFOB' => "",
                'InvoiceTermsDescription' => $invoice->getDiscountDescription() ?? ""
            ];

            /** @var  $invoicedItemsParams */
            $invoicedItemsParams = [];
            $invoicedItems = $invoice->getItems() ?? [];
            $invoiceParams['Items'] = $order->addOrderItemToEbizcharge($invoicedItems);


            /** @var $ebizInvoiceInternalId */
            $ebizInvoiceInternalId = $invoice->getEcInvoiceInternalid() ?? "";

            if (!$ebizInvoiceInternalId) {

                /** @var $invoiceParams */
                $ebizInvoiceParams = [
                    'securityToken' => $securityToken,
                    'invoice' => $invoiceParams
                ];
                /** @var $ebizchargeInvoiceResult */
                $ebizchargeInvoiceResult = $this->_soapApiModel->getClient()->AddInvoice($ebizInvoiceParams);

                if (is_object($ebizchargeInvoiceResult->AddInvoiceResult)) {
                    /** @var $invoiceResponse */
                    $invoiceResponse = (array)$ebizchargeInvoiceResult->AddInvoiceResult;

                    if (isset($invoiceResponse["Status"]) && $invoiceResponse["StatusCode"] === 1) {
                        $invoiceResultResp = [
                            'status' => 'Success',
                            'error' => false,
                            'error_code' => $invoiceResponse['ErrorCode'],
                            'message' => __('Success, the Invoice has been added to EBizCharge Gateway'),
                            'order_id' => $orderId,
                            'ec_invoice_id' => $invoiceNumber,
                            'ec_invoice_internalid' => $invoiceResponse['InvoiceInternalId'],
                            'ec_cust_id' => $invoiceCustomerId
                        ];
                        /** Invoice udpate with local database */
                        $invoice
                            ->setEcCustId($invoiceCustomerId)
                            ->setDivisionId($this->_soapApiModel->getDivisionId())
                            ->setSoftwareId($this->_soapApiModel->getSoftwareId())
                            ->setEcInvoiceSyncStatus(1)
                            ->setEcInvoiceLastsyncdate(date('Y-m-d h:i:s'))
                            ->setEcInvoiceInternalid($invoiceResponse['InvoiceInternalId'])
                            ->setEcInvoiceId($invoice->getIncrementId())
                        ;
                        $invoice->save() ;

                        $invoiceParams["InvoiceInternalId"] = $invoiceResponse['InvoiceInternalId'] ?? "";
                        $invoiceParams["InvoiceNumber"] = $invoiceParams['InvoiceNumber'] ?? "";
                        $invoiceParams["CustomerId"] = $invoiceParams['CustomerId'];
                        $invoiceParams["CustomerToken"] = $customerToken;
                        $invoiceParams["PaidAmount"] = $invoiceParams['AmountDue'];
                        $invoiceParams["Currency"] = $currency ?? "";
                        $invoiceParams["RefNum"] = $paymentRefNumber ?? "";
                        $invoiceParams["AdditionalInformations"] = $additionalInformations;

                        $methodOptionType = $additionalInformations["ebzc_option_type"] ?? "";

                        if($methodOptionType === "credit_card" || $methodOptionType === "cc"){
                            $methodOptionType = "CreditCard";
                        }

                        $invoiceParams["PaymentMethodId"] = $additionalInformations["ebzc_method_id"] ?? "";
                        $invoiceParams["PaymentMethodType"] = $methodOptionType;

                        /**
                         * Adding quick invoice payment to Gateway
                         */
                     //   $syncedPayment = $this->_soapApiModel->addQuickInvoicePayment($invoiceParams);

                    //    if($syncedPayment["error"] === false){
                      //      $this->ebizchargeLogger->addInfo(__("Quick Invoice Payment has been synced to EBizCharge Payment."));
                      //  }

                    } else {
                        $invoiceResultResp['status'] = 'Error';
                        $invoiceResultResp['error'] = true;
                        $invoiceResultResp['error_code'] = $invoiceResponse['ErrorCode'];
                        $invoiceResultResp['message'] = __(
                            'Error occurred, during add | Update Invoice to Ebizcharge Gateway'
                        );
                    }
                } else {
                    $invoiceResultResp['status'] = 'Error';
                    $invoiceResultResp['error'] = true;
                    $invoiceResultResp['error_code'] = 0;
                    $invoiceResultResp['message'] = __(
                        'Error occurred, during add | Update Invoice to Ebizcharge Gateway'
                    );
                }
            } else {

                /** @var $ebizchargeInvoiceResult */
                $ebizInvoiceParams = [
                    'securityToken' => $securityToken,
                    'invoice' => $invoiceParams,
                    'customerId' => $invoiceCustomerId,
                    'invoiceInternalId' => $ebizInvoiceInternalId,
                    'invoiceNumber' => $invoiceNumber,

                ];

                $ebizchargeInvoiceResult = $this->_soapApiModel->getClient()->UpdateInvoice($ebizInvoiceParams);

                /** @var $invoiceResponse */
                $invoiceResponse = (array)$ebizchargeInvoiceResult->UpdateInvoiceResult;

                if (isset($invoiceResponse["Status"]) && $invoiceResponse["StatusCode"] === 1) {
                    $invoiceResultResp = [
                        'status' => 'Success',
                        'error' => false,
                        'error_code' => $invoiceResponse['ErrorCode'],
                        'message' => __('Success, the Invoice has been Updated to Ebizcharge Gateway'),
                        'order_id' => $orderId,
                        'ebiz_invoice_internal_id' => $invoiceResponse['InvoiceInternalId'],
                        'ec_invoice_id' => $invoiceNumber,
                        'ec_invoice_internalid' => $invoiceResponse['InvoiceInternalId'],
                        'ec_cust_id' => $invoiceCustomerId
                    ];

                    /** Invoice udpate with local database */
                    $invoice
                        ->setEcCustId($invoiceCustomerId)
                        ->setDivisionId($this->_soapApiModel->getDivisionId())
                        ->setSoftwareId($this->_soapApiModel->getSoftwareId())
                        ->setEcInvoiceSyncStatus(1)
                        ->setEcInvoiceLastsyncdate(date('Y-m-d h:i:s'))
                        ->setEcInvoiceInternalid($invoiceResponse['InvoiceInternalId'])
                        ->setEcInvoiceId($invoice->getIncrementId());
                    $invoice->save();
                    $invoiceParams["InvoiceInternalId"] = $invoiceResponse['InvoiceInternalId'] ?? "";
                    $invoiceParams["InvoiceNumber"] = $invoiceParams['InvoiceNumber'] ?? "";
                    $invoiceParams["CustomerId"] = $invoiceParams['CustomerId'];
                    $invoiceParams["CustomerToken"] = $customerToken;
                    $invoiceParams["PaidAmount"] = $invoiceParams['AmountDue'];
                    $invoiceParams["Currency"] = $currency ?? "";
                    $invoiceParams["RefNum"] = $paymentRefNumber ?? "";
                    $invoiceParams["AdditionalInformations"] = $additionalInformations;
                    $methodOptionType = $additionalInformations["ebzc_option_type"] ?? "";

                    if($methodOptionType === "credit_card" || $methodOptionType === "cc"){
                        $methodOptionType = "CreditCard";
                    }

                    $invoiceParams["PaymentMethodId"] = $additionalInformations["ebzc_method_id"] ?? "";
                    $invoiceParams["PaymentMethodType"] = $methodOptionType;


                    /**
                     * Adding quick invoice payment to Gateway
                     */
                    //$syncedPayment = $this->_soapApiModel->addQuickInvoicePayment($invoiceParams);
                    //if($syncedPayment["error"] === false){
                  //      $this->ebizchargeLogger->addInfo(__("Quick Invoice Payment has been synced and has updated to EBizCharge Payment."));
                  //  }

                } else {
                    $invoiceResultResp['status'] = 'Error';
                    $invoiceResultResp['error'] = true;
                    $invoiceResultResp['error_code'] = $invoiceResponse['ErrorCode'];
                    $invoiceResultResp['message'] = __(
                        'Error occurred, during add | Update Invoice to EBizCharge Gateway'
                    );
                    $invoiceResultResp['order_id'] = $orderId;
                    $invoiceResultResp['ec_invoice_id'] = $invoiceNumber;
                    $invoiceResultResp['ec_invoice_internalid'] = '';
                    $invoiceResultResp['ec_cust_id'] = '';
                }
            }

        } catch (SoapFault $soapFault) {
            $this->_ebizchargeLogger->addCritical(__(
                "Exception occurred during placing invoice with EBizCharge Error: " . $soapFault->getMessage()
            ));
            $invoiceResultResp['message'] = __(
                'Exception occurred during placing invoice with EBizCharge Error: ' . $soapFault->getMessage()
            );

        }
        return $invoiceResultResp;
    }


    /**
     * Get Invoice Shipping Address
     *
     * @param $order
     * @return array
     */
    public function getInvoiceShippingAddress($order = null): array
    {
        $orderShippingAddress = $order->getShippingAddress() ? $order->getShippingAddress() : $order->getBillingAddress();

        if ($orderShippingAddress) {

            // phpcs:disable
            $shippingFirstName = @$orderShippingAddress->getFirstname() ?? '';
            $shippingLastName = @$orderShippingAddress->getLastname() ?? '';
            $shippingCompanyName = @$orderShippingAddress->getCompany() ?? '';
            $shippingStreetAddress = @$orderShippingAddress->getStreet();
            $shippingCity = @$orderShippingAddress->getCity();
            $shippingCountry = @$orderShippingAddress->getCountryId();
            $shippingState = @$orderShippingAddress->getRegion();
            $shippingZipCode = @$orderShippingAddress->getPostcode();
            $isDefault = 1;
            $shippingAddressId = @$orderShippingAddress->getId();
            // phpcs:enable

            return [
                'FirstName' => $shippingFirstName,
                'LastName' => $shippingLastName,
                'CompanyName' => $shippingCompanyName,
                'Address1' => isset($shippingStreetAddress[0]) ? $shippingStreetAddress[0] : '',
                'Address2' => isset($shippingStreetAddress[1]) ? $shippingStreetAddress[1] : '',
                'City' => $shippingCity,
                'State' => $shippingState,
                'ZipCode' => $shippingZipCode,
                'Country' => $shippingCountry,
                'IsDefault' => $isDefault,
                'AddressId' => $shippingAddressId
            ];
        } else {
            return $this->getInvoiceBillingAddress($order);
        }
    }

    /**
     * Get Invoice Billing Address
     *
     * @param EbizOrder|null $order
     * @return array
     */
    public function getInvoiceBillingAddress(?EBizOrder  $order =null): array
    {
        $orderBillingAddress = $order->getBillingAddress();
        $billingFirstName = '';
        $billingLastName = '';
        $billingCompanyName = '';
        $billingStreetAddress = '';
        $billingCity = '';
        $billingCountry = '';
        $billingState = '';
        $billingZipCode = '';
        $isDefault = 1;
        $billingAddressId = '';

        if ($orderBillingAddress) {
            // phpcs:disable
            $billingFirstName = @$orderBillingAddress->getFirstname() ?? '';
            $billingLastName = @$orderBillingAddress->getLastname() ?? '';
            $billingCompanyName = @$orderBillingAddress->getCompany() ?? '';
            $billingStreetAddress = @$orderBillingAddress->getStreet();
            $billingCity = @$orderBillingAddress->getCity();
            $billingCountry = @$orderBillingAddress->getCountryId();
            $billingState = @$orderBillingAddress->getRegion();
            $billingZipCode = @$orderBillingAddress->getPostcode();
            $isDefault = 1;
            $billingAddressId = @$orderBillingAddress->getId();
            // phpcs:enable
        }

        $invoiceBillingAddress = [
            'FirstName' => $billingFirstName,
            'LastName' => $billingLastName,
            'CompanyName' => $billingCompanyName,
            'Address1' => isset($billingStreetAddress[0]) ? $billingStreetAddress[0] : '',
            'Address2' => isset($billingStreetAddress[1]) ? $billingStreetAddress[1] : '',
            'City' => $billingCity,
            'State' => $billingState,
            'ZipCode' => $billingZipCode,
            'Country' => $billingCountry,
            'IsDefault' => $isDefault,
            'AddressId' => $billingAddressId
        ];

        return $invoiceBillingAddress;
    }

    /**
     * Get Invoice Custom Fields
     *
     * @param $invoice
     * @return array
     */
    public function getInvoiceCustomFields($invoice = null): array
    {
        $invoiceCustomFields = [];
        $orderCustomAttributes = $invoice->getCustomAttributes();

        if (count($orderCustomAttributes) > 0) {
            foreach ($orderCustomAttributes as $key => $attribute) {
                $invoiceCustomFields['EbizCustomField'][$key] = [
                    'FieldId' => $attribute->getId(),
                    'FieldCaption' => $attribute->getValue(),
                    'FieldName' => $attribute->getValue(),
                    'FieldValue' => '',
                    'FieldDescription' => ''
                ];
            }
        }

        return $invoiceCustomFields;
    }

    /**
     * @param int $storeId
     * @return bool
     */
    public function getUnitOfMeasure(int $storeId = 0): bool
    {
        return $this->_ebizOrderFactory->create()->getUnitOfMeasure($storeId);
    }

    /**
     * Returns increment id
     *
     * @return string
     */
    public function getIncrementId(): string
    {
        return $this->getData('increment_id');
    }

    /**
     * Get PDF URL
     *
     * @param int $invoiceId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getPdfUrl(int $invoiceId = 0): string
    {
        $store = $this->_storeManager->getStore();
        $param = [
            'invoice_id' => $invoiceId
        ];
        return $this->getAdminUrl($param);
    }

    /**
     * Get Admin URl
     *
     * @param array $params
     * @return string
     */
    public function getAdminUrl(array $params = []): string
    {
        return $this->_backendHelper->getUrl('sales/order_invoice/print', $params);
    }

    //@codeCoverageIgnoreStart

    /**
     * Returns invoice items
     *
     * @return InvoiceItemInterface[]
     */
    public function getItems(): array
    {
        if ($this->getData(InvoiceInterface::ITEMS) === null && $this->getId()) {
            $collection = $this->_invoiceItemCollectionFactory->create()->setInvoiceFilter($this->getId());
            foreach ($collection as $item) {
                $item->setInvoice($this);
            }
            $this->setData(InvoiceInterface::ITEMS, $collection->getItems());
        }
        return $this->getData(InvoiceInterface::ITEMS);
    }

    /**
     * Get EBizCharge Invoice Internal Id
     *
     * @return mixed|null
     */
    public function getEcInvoiceInternalid(): mixed
    {
        return $this->getData(self::EBIZ_INVOICE_INTERNAL_ID);
    }

    /**
     * Set Division Id
     *
     * @param mixed $ecDivisionId
     * @return EbizInvoiceInterface
     */
    public function setDivisionId($ecDivisionId): EbizInvoiceInterface
    {
        return $this->setData(self::EBIZCHARGE_DIVISION_ID, $ecDivisionId);
    }

    /**
     * Set EBizCharge Customer Id
     *
     * @param mixed $ebizCustomerId
     * @return Invoice
     */
    public function setEcCustId(mixed $ebizCustomerId): Invoice
    {
        return $this->setData(self::EBIZ_CUSTOMER_ID, $ebizCustomerId);
    }

    /**
     * Get EBizCharge Surcharge Amount
     *
     * @return mixed|null
     */
    public function getEcSurchargeAmount(): mixed
    {
        return $this->getData(SurchargeInterface::EC_SURCHARGE_AMOUNT);
    }

    /**
     * Get EBizCharge Surcharge Percentage
     *
     * @return mixed|null
     */
    public function getEcSurchargePercentage(): mixed
    {
        return $this->getData(SurchargeInterface::EC_SURCHARGE_PERCENTAGE);
    }

    /**
     * Get EBizCharge Surcharge Percentage
     *
     * @return mixed|null
     */
    public function getEcSurchargeIneligible(): mixed
    {
        return $this->getData(SurchargeInterface::EC_SURCHARGE_INELIGIBLE);
    }

    /**
     * Set EBizCharge Surcharge Amount
     *
     * @param mixed $ebizSurchargeAmount
     * @return EbizInvoiceInterface
     */
    public function setEcSurchargeAmount(mixed $ebizSurchargeAmount): EbizInvoiceInterface
    {
        return $this->setData(SurchargeInterface::EC_SURCHARGE_AMOUNT, $ebizSurchargeAmount);
    }

    /**
     * Set EBizCharge Surcharge Percentage
     *
     * @param mixed $ebizSurchargePercentage
     * @return EbizInvoiceInterface
     */
    public function setEcSurchargePercentage(mixed $ebizSurchargePercentage): EbizInvoiceInterface
    {
        return $this->setData(SurchargeInterface::EC_SURCHARGE_PERCENTAGE, $ebizSurchargePercentage);
    }

    /**
     * Set Ec Surcharge Ineligible
     *
     * @param float|null $surchargeIneligible
     * @return EbizInvoiceInterface
     */
    public function setEcSurchargeIneligible($surchargeIneligible): EbizInvoiceInterface
    {
        return $this->setData(SurchargeInterface::EC_SURCHARGE_INELIGIBLE, $surchargeIneligible);
    }

    /**
     * Add Ebiz Payment Transactions To Order
     *
     * @param mixed|null $orderId
     */
    public function addEbizPaymentTransactionsToOrder(mixed $orderId = null): void
    {
        /** @var $order */
        $order = $this->_ebizOrderFactory->create()->load($orderId);

        //if ($order->getId()) {
        //}
    }

    /**
     * Get Invoice Payments
     *
     * @param string $mageCustomerId
     * @param string $customerInternalId
     * @param string $fromDate
     * @param string $toDate
     * @param int $limit
     * @param int $position
     * @return bool|array
     * @throws NoSuchEntityException
     */
    public function getEbizInvoicePayments(
        string $mageCustomerId = '',
        string $customerInternalId = '',
        string $fromDate = '',
        string $toDate = '',
        int    $limit = 1000,
        int $position = 0
    ): bool|array
    {
        try {
            /** getting EBizCharge $securityToken */
            $securityToken = $this->_soapApiModel->getUeSecurityToken();

            $ebzcInvoice = '';
            $maxSize = 0;
            $start = $position !== 0 ? $position : 0;
            $defaultFromDate = date('Y-m-d', strtotime('-30 days', time()));
            $defaultToDate = date('Y-m-d');

            /**
             * Define Invoice Payments Object
             */
            /** @var $invoicePaymentsObj */
            $invoicePaymentsObj = [];

            do {
                /** Search Invoices Payments Params for EBizCharge API SOAP */
                /** @var  $searchInvoicePaymentsParams */
                $searchInvoicePaymentsParams = [
                    'securityToken' => $securityToken,
                    'customerId' => $mageCustomerId,
                    'customerInternalId' => $customerInternalId,
                    'fromDateTime' => $fromDate !== '' ? $fromDate : $defaultFromDate,
                    'toDateTime' => $toDate !== '' ? $toDate : $defaultToDate,
                    'start' => $start,
                    'limit' => $limit,
                    'sort' => ''
                ];

                /**
                 * Sending request Ebizcharge get the latest Invoices
                 * @Ebizcharge SOAP Api Gateway
                 */
                /** @var  $ebizchargeInvoicePayments */
                $ebizchargeInvoicePayments = $this->_soapApiModel->getClient()
                    ->GetPayments($searchInvoicePaymentsParams);

                /** fetching customer results */
                if (!isset($ebizchargeInvoicePayments->GetPaymentsResponse->GetPaymentsResult)) {
                    $invoicePaymentsObj = [];
                    $resultCount = 0;
                } elseif ((is_array($ebizchargeInvoicePayments->GetPaymentsResponse->GetPaymentsResult)) &&
                    (count($ebizchargeInvoicePayments->GetPaymentsResponse->GetPaymentsResult)) > 1) {
                    $ebzcInvoicePayment = $ebizchargeInvoicePayments->GetPaymentsResponse->GetPaymentsResult;
                    $resultCount = count($ebizchargeInvoicePayments->GetPaymentsResponse->GetPaymentsResult);

                    $invoicePaymentsObj = array_merge($invoicePaymentsObj, $ebzcInvoicePayment);
                } else {
                    $invoicePaymentsObj = $ebizchargeInvoicePayments->GetPaymentsResponse->GetPaymentsResult;
                    $resultCount = 1;
                }
                /** result count */
                if ($resultCount < 1000) {
                    $maxSize = 1;
                }
                $start = $start + 1000;
            } while ($maxSize == 0);

            /**
             * Fetch all Records in an array list End
             */
            return ($invoicePaymentsObj);
        } catch (SoapFault $soapFault) {
            $this->_ebizchargeLogger->addCritical(__(
                'Exception occured during getting Invoices Error: ' . $soapFault->getMessage()
            ));
            return false;
        }
    }

    /**
     * Get Ebiz Invoice Collection
     *
     * @param string $mageCustomerId
     * @param string $mageInvoiceInternalId
     * @param int $limit
     * @param int $position
     * @return array|false
     * @throws NoSuchEntityException
     */
    public function getEbizInvoiceCollection(
        string $mageCustomerId = '',
        string $mageInvoiceInternalId = '',
        int    $limit = 1000,
        int $position = 0
    ): false|array
    {
        try {
            /** getting EBizCharge $securityToken */
            $securityToken = $this->_soapApiModel->getUeSecurityToken();

            $ebzcInvoice = '';
            $maxSize = 0;
            $start = $position !== 0 ? $position : 0;

            /**
             * Define Invoices Object
             */
            /** @var $invoicesObj */
            $invoicesObj = [];

            do {
                /** @var  $invoiceFilters */
                $invoiceFilters = [
                    'SearchFilter' => [
                        'FieldName' => 'SoftwareId',
                        'ComparisonOperator' => 'eq',
                        'FieldValue' => $this->_soapApiModel->getSoftwareId()
                    ]
                ];

                /** Search Customer Params for EBizCharge API SOAP */

                /** @var  $searchInvoiceParams */
                $searchInvoiceParams = [
                    'securityTokenCustomer' => $securityToken,
                    'customerId' => $mageCustomerId,
                    'subCustomerId' => '',
                    'invoiceNumber' => '',
                    'invoiceInternalId' => $mageInvoiceInternalId,
                    'start' => $start,
                    'limit' => $limit,
                    'sort' => '',
                    'includeItems' => 1,
                    'filters' => $invoiceFilters
                ];

                /**
                 * Sending request Ebizcharge get the latest Invoices
                 * @Ebizcharge SOAP Api Gateway
                 */
                /** @var  $ebizchargeInvoices */
                $ebizchargeInvoices = $this->_soapApiModel->getClient()->SearchInvoices($searchInvoiceParams);

                /** fetching customer results */
                if (!isset($ebizchargeInvoices->SearchInvoicesResponse->SearchInvoicesResult)) {
                    $invoicesObj = [];
                    $resultCount = 0;
                } elseif ((is_array($ebizchargeInvoices->SearchInvoicesResponse->SearchInvoicesResult)) &&
                    (count($ebizchargeInvoices->SearchInvoicesResponse->SearchInvoicesResult)) > 1) {
                    $ebzcInvoice = $ebizchargeInvoices->SearchInvoicesResponse->SearchInvoicesResult;
                    $resultCount = count($ebizchargeInvoices->SearchInvoicesResponse->SearchInvoicesResult);

                    $invoicesObj = array_merge($invoicesObj, $ebzcInvoice);
                } else {
                    $invoicesObj = $ebizchargeInvoices->SearchInvoicesResponse->SearchInvoicesResult;
                    $resultCount = 1;
                }
                /** result count */
                if ($resultCount < 1000) {
                    $maxSize = 1;
                }
                $start = $start + 1000;
            } while ($maxSize == 0);

            /**
             * Fetch all Records in an array list End
             */
            return ($invoicesObj);
        } catch (SoapFault $soapFault) {
            $this->_ebizchargeLogger->addCritical(__(
                'Exception occurred during getting Invoices Error: ' . $soapFault->getMessage()
            ));

            return false;
        }
    }

    /**
     * Initialize invoice resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(ResourceModelInvoice::class);
    }
}

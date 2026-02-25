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

namespace Ebizcharge\Ebizcharge\Model;

use DateTime;
use Ebizcharge\Ebizcharge\Api\Data\CustomerInterface;
use Ebizcharge\Ebizcharge\Api\Data\OrderInterface;
use Ebizcharge\Ebizcharge\Api\Data\PaymentInterface;
use Ebizcharge\Ebizcharge\Api\Data\ProductInterface;
use Ebizcharge\Ebizcharge\Api\Data\SoapApiModelInterface;
use Ebizcharge\Ebizcharge\Api\Data\SurchargeInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\Config as ConfigResource;
use Ebizcharge\Ebizcharge\Model\Config as EbizchargeConfigModel;
use Ebizcharge\Ebizcharge\Model\Customer as CustomerModel;
use Ebizcharge\Ebizcharge\Model\Order\Invoice as EbizInvoice;
use Ebizcharge\Ebizcharge\Model\ResourceModel\Order\Grid\Collection as orderGridResourceModelCollection;
use Magento\Backend\Model\Session\Quote as AdminQuoteSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Observer\ItemsForReindex;
use Magento\Checkout\Model\Session;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\AddressFactory;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Directory\Model\Currency;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Indexer\Model\Indexer\CollectionFactory as IndexerCollectionFactory;
use Magento\Indexer\Model\IndexerFactory;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartInterfaceFactory;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\Quote\Model\Cart\Currency as quoteCurrency;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderAddressInterfaceFactory;
use Magento\Sales\Api\Data\OrderInterface as OrderInterfaceAlias;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\InvoiceManagementInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\AdminOrder\Create as AdminOrderCreate;
use Magento\Sales\Model\Order as CoreOrder;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\ItemFactory as OrderItemFactory;
use Magento\Sales\Model\Order\ProductOption;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory as AddressCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as SalesOrderCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory as MemoCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory as InvoiceCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory as OrderItemCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory as PaymentCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory as TrackCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory as HistoryCollectionFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Service\OrderService;
use Magento\SalesSequence\Model\Profile;
use Magento\SalesSequence\Model\Sequence;
use Magento\Setup\Exception;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\Calculation as TaxCalculation;
use Magento\Tax\Model\ClassModel as TaxClassModel;
use SoapClient;
use SoapFault;

//use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;


/**
 * EbizCharge Order Model Class
 *
 * Class Order
 */
class Order extends CoreOrder implements OrderInterface
{
    /**
     * Default Sequency Profile Order Id
     */
    public const DEFAULT_SEQUENCE_PROFILE_ORDER_ID = 5;

    /**
     * Deafult Country Id
     *
     * @const DEFAULT_COUNTRY_ID
     */
    public const DEFAULT_COUNTRY_ID = 'US';

    /**
     * Not Available
     *
     * @const NOT_AVAILABLE
     */
    public const NOT_AVAILABLE = '*';

    /**
     * Default Region Id
     *
     * @const DEFAULT_REGION_ID
     */
    public const DEFAULT_REGION_ID = 12;

    /**
     * Default Shipping Method
     *
     * @const DEFAULT_SHIPPING_METHOD
     */
    public const DEFAULT_SHIPPING_METHOD = 'flatrate_flatrate';

    /**
     * Default Currency Rate
     *
     * @const DEFAULT_CURRENCY_RATE
     */
    public const DEFAULT_CURRENCY_RATE = 1.00;

    /**
     * Default Currency Code
     *
     * @const DEFAULT_CURRENCY_CODE
     */
    public const DEFAULT_CURRENCY_CODE = 'USD';

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var ExtensionAttributesFactory
     */
    protected ExtensionAttributesFactory $_extensionFactory;

    /**
     * @var AttributeValueFactory
     */
    protected AttributeValueFactory $_customAttributeFactory;

    /**
     * @var TimezoneInterface
     */
    protected TimezoneInterface $_timezone;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CoreOrder\Config
     */
    protected $_orderConfig;

    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $_productRepository;

    /**
     * @var OrderItemCollectionFactory
     */
    protected $_orderItemCollectionFactory;

    /**
     * @var Visibility
     */
    protected $_productVisibility;

    /**
     * @var InvoiceManagementInterface
     */
    protected InvoiceManagementInterface $_invoiceManagement;

    /**
     * @var CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * @var EavConfig
     */
    protected EavConfig $_eavConfig;

    /**
     * @var HistoryFactory
     */
    protected $_orderHistoryFactory;

    /**
     * @var AddressCollectionFactory
     */
    protected $_addressCollectionFactory;

    /**
     * @var PaymentCollectionFactory
     */
    protected $_paymentCollectionFactory;

    /**
     * @var HistoryCollectionFactory
     */
    protected $_historyCollectionFactory;

    /**
     * @var InvoiceCollectionFactory
     */
    protected $_invoiceCollectionFactory;

    /**
     * @var ShipmentCollectionFactory
     */
    protected $_shipmentCollectionFactory;

    /**
     * @var MemoCollectionFactory
     */
    protected $_memoCollectionFactory;

    /**
     * @var TrackCollectionFactory
     */
    protected $_trackCollectionFactory;

    /**
     * @var SalesOrderCollectionFactory
     */
    protected SalesOrderCollectionFactory $_salesOrderCollectionFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected PriceCurrencyInterface $_priceCurrency;

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $_productListFactory;

    /**
     * @var AbstractResource|null
     */
    protected $_resource;

    /**
     * @var AbstractDb|null
     */
    protected $_resourceCollection;

    /**
     * @var array
     */
    protected $_data;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var EbizchargeConfigModel
     */
    protected EbizchargeConfigModel $_ebizchargeConfigModel;

    /**
     * @var TranApi
     */
    protected TranApi $_soapApiModel;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * @var SoapClient
     */
    protected SoapCli $_soapClient;

    /**
     * @var CustomerRepositoryInterface
     */
    protected CustomerRepositoryInterface $_customerRepository;

    /**
     * @var QuoteFactory
     */
    protected QuoteFactory $_quoteFactory;

    /**
     * @var QuoteManagement
     */
    protected QuoteManagement $_quoteManagement;

    /**
     * @var OrderSender
     */
    protected OrderSender $_orderSender;

    /**
     * @var ProductFactory
     */
    protected ProductFactory $_productFactory;


    /**
     * @var SyncAssetsFactory
     */
    protected SyncAssetsFactory $_syncAssetsFactory;

    /**
     * @var CarrierFactory
     */
    protected CarrierFactory $_carrierFactory;

    /**
     * @var ItemsForReindex
     */
    protected ItemsForReindex $_itemsForReindex;

    /**
     * @var Currency
     */
    protected Currency $_directoryCurrency;

    /**
     * @var quoteCurrency
     */
    protected quoteCurrency $_quoteCurrency;

    /**
     * @var Customer
     */
    protected Customer $_customerModel;

    /**
     * @var OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $_orderRepository;

    /**
     * @var InvoiceService
     */
    protected InvoiceService $_invoiceService;

    /**
     * @var TransactionFactory
     */
    protected TransactionFactory $_transactionFactory;

    /**
     * @var Collection
     */
    protected Collection $_orderCollection;

    /**
     * @var CoreOrder\AddressRepository
     */
    protected CoreOrder\AddressRepository $_addressRepository;

    /**
     * @var Config
     */
    protected Config $_configResource;

    /**
     * @var CustomerInterfaceFactory
     */
    protected $_customerInterfaceFactory;

    /**
     * @var CartManagementInterface
     */
    protected CartManagementInterface $_cartManagementInterface;

    /**
     * @var CartRepositoryInterface
     */
    protected CartRepositoryInterface $_cartRepositoryInterface;

    /**
     * @var OrderService
     */
    protected OrderService $_orderService;

    /**
     * @var orderGridResourceModelCollection
     */
    protected orderGridResourceModelCollection $_orderGridResourceModelCollection;

    /**
     * @var EbizInvoice
     */
    protected EbizInvoice $_ebizInvoice;

    /**
     * @var FutureSubscriptionFactory
     */
    protected FutureSubscriptionFactory $_futureRecurringFactory;

    /**
     * @var RecurringFactory
     */
    protected RecurringFactory $_recurringFactory;

    /**
     * @var OrderSubscriptionFactory
     */
    protected OrderSubscriptionFactory $_orderSubscriptionFactory;

    /**
     * @var AddressRepositoryInterface
     */
    protected AddressRepositoryInterface $_addressRepositoryInterface;

    /**
     * @var AddressFactory
     */
    protected AddressFactory $_addressFactory;

    /**
     * @var Sequence
     */
    protected Sequence $_sequenceModel;

    /**
     * @var Profile
     */
    protected Profile $_sequenceProfileModel;

    /**
     * @var CoreOrder\ItemRepository
     */
    protected CoreOrder\ItemRepository $_orderItemRepository;

    /**
     * @var Session
     */
    protected Session $_checkoutSession;

    /**
     * @var CustomerSession
     */
    protected CustomerSession $_customerSession;

    /**
     * @var ItemFactory
     */
    protected ItemFactory $quoteItemFactory;

    /**
     * @var IndexerFactory
     */
    protected IndexerFactory $indexFactory;

    /**
     * @var IndexerCollectionFactory
     */
    protected IndexerCollectionFactory $indexCollection;
    /**
     * @var OrderManagementInterface
     */
    protected OrderManagementInterface $orderManagementInterface;
    /**
     * @var AdminOrderCreate
     */
    protected AdminOrderCreate $adminCreateOrder;
    /**
     * @var OrderAddressInterface
     */
    protected OrderAddressInterface $orderAddressInterface;
    /**
     * @var OrderAddressInterfaceFactory
     */
    protected OrderAddressInterfaceFactory $orderAddressInterfaceFactory;
    /**
     * @var OrderItemFactory
     */
    protected OrderItemFactory $orderItemFactory;
    /**
     * @var CartInterfaceFactory
     */
    protected CartInterfaceFactory $cartInterfaceFactory;
    /**
     * @var CartInterface
     */
    protected CartInterface $cartInterface;
    /**
     * @var CartItemInterfaceFactory
     */
    protected CartItemInterfaceFactory $cartItemInterfaceFactory;
    /**
     * @var OrderAddressInterface
     */
    protected OrderAddressInterface $OrderAddressInterface;
    /**
     * @var SessionManagerInterface
     */
    protected SessionManagerInterface $sessionManagerInterface;
    /**
     * @var OrderFactory
     */
    protected OrderFactory $orderFactory;

    /**
     * @var AdminQuoteSession
     */
    protected AdminQuoteSession $adminQuoteSession;

    /**
     * @var AddressInterface
     */
    protected AddressInterface $_quoteAddressInterface;

    /**
     * @var Surcharge
     */
    protected Surcharge $surchargeModel;

    /**
     * @var TaxCalculation
     */
    protected TaxCalculation $taxCalculation;
    /**
     * @var TaxClassModel
     */
    protected TaxClassModel $taxClassModel;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param TimezoneInterface $timezone
     * @param StoreManagerInterface $storeManager
     * @param OrderConfig $orderConfig
     * @param ProductRepositoryInterface $productRepository
     * @param OrderItemCollectionFactory $orderItemCollectionFactory
     * @param Visibility $productVisibility
     * @param InvoiceManagementInterface $invoiceManagement
     * @param CurrencyFactory $currencyFactory
     * @param EavConfig $eavConfig
     * @param HistoryFactory $orderHistoryFactory
     * @param AddressCollectionFactory $addressCollectionFactory
     * @param PaymentCollectionFactory $paymentCollectionFactory
     * @param HistoryCollectionFactory $historyCollectionFactory
     * @param InvoiceCollectionFactory $invoiceCollectionFactory
     * @param ShipmentCollectionFactory $shipmentCollectionFactory
     * @param MemoCollectionFactory $memoCollectionFactory
     * @param TrackCollectionFactory $trackCollectionFactory
     * @param SalesOrderCollectionFactory $salesOrderCollectionFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param Config $ebizchargeConfigModel
     * @param EbizchargeLogger $ebizchargeLogger
     * @param Customer $customerModel
     * @param CollectionFactory $productListFactory
     * @param Currency $directoryCurrency
     * @param quoteCurrency $quoteCurrency
     * @param TranApi $tranApi
     * @param OrderSubscriptionFactory $orderSubscriptionFactory
     * @param CustomerFactory $customerFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param QuoteFactory $quoteFactory
     * @param QuoteManagement $quoteManagement
     * @param OrderSender $orderSender
     * @param ProductFactory $productFactory
     * @param SyncAssetsFactory $syncAssetsFactory
     * @param ItemsForReindex $itemsForReindex
     * @param CarrierFactory $carrierFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceService $invoiceService
     * @param TransactionFactory $transactionFactory
     * @param Collection $orderCollection
     * @param Config $configResource
     * @param CoreOrder\AddressRepository $addressRepository
     * @param CustomerInterfaceFactory $customerInterfaceFactory
     * @param CartManagementInterface $cartManagementInterface
     * @param CartRepositoryInterface $cartRepositoryInterface
     * @param EbizInvoice $ebizInvoice
     * @param FutureSubscriptionFactory $futureSubscriptionFactory
     * @param RecurringFactory $recurringFactory
     * @param orderGridResourceModelCollection $orderGridResourceModelCollection
     * @param AddressFactory $addressFactory
     * @param AddressRepositoryInterface $addressRepositoryInterface
     * @param Sequence $sequenceModel
     * @param Profile $profileModel
     * @param OrderService $orderService
     * @param CoreOrder\ItemRepository $orderItemRepository
     * @param Session $checkoutSession
     * @param CustomerSession $customerSession
     * @param ItemFactory $quoteItemFactory
     * @param IndexerFactory $indexFactory
     * @param IndexerCollectionFactory $indexCollection
     * @param SessionManagerInterface $sessionManagerInterface
     * @param OrderManagementInterface $orderManagementInterface
     * @param AdminOrderCreate $adminCreatOrder
     * @param OrderAddressInterface $OrderAddressInterface
     * @param OrderAddressInterfaceFactory $orderAddressInterfaceFactory
     * @param OrderItemFactory $orderItemFactory
     * @param CartInterfaceFactory $cartInterfaceFactory
     * @param CartInterface $cartInterface
     * @param CartItemInterfaceFactory $cartItemInterfaceFactory
     * @param OrderFactory $orderFactory
     * @param AdminQuoteSession $adminQuoteSession
     * @param AddressInterface $quoteAddressInterface
     * @param Surcharge $surchargeModel
     * @param TaxCalculation $taxCalculation
     * @param TaxClassModel $taxClassModel
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param ResolverInterface|null $localeResolver
     * @param ProductOption|null $productOption
     * @param OrderItemRepositoryInterface|null $itemRepository
     * @param SearchCriteriaBuilder|null $searchCriteriaBuilder
     * @param ScopeConfigInterface|null $scopeConfig
     * @param RegionFactory|null $regionFactory
     * @param array $data
     */
    public function __construct(
        Context                          $context,
        Registry                         $registry,
        ExtensionAttributesFactory       $extensionFactory,
        AttributeValueFactory            $customAttributeFactory,
        TimezoneInterface                $timezone,
        StoreManagerInterface            $storeManager,
        OrderConfig                      $orderConfig,
        ProductRepositoryInterface       $productRepository,
        OrderItemCollectionFactory       $orderItemCollectionFactory,
        Visibility                       $productVisibility,
        InvoiceManagementInterface       $invoiceManagement,
        CurrencyFactory                  $currencyFactory,
        EavConfig                        $eavConfig,
        HistoryFactory                   $orderHistoryFactory,
        AddressCollectionFactory         $addressCollectionFactory,
        PaymentCollectionFactory         $paymentCollectionFactory,
        HistoryCollectionFactory         $historyCollectionFactory,
        InvoiceCollectionFactory         $invoiceCollectionFactory,
        ShipmentCollectionFactory        $shipmentCollectionFactory,
        MemoCollectionFactory            $memoCollectionFactory,
        TrackCollectionFactory           $trackCollectionFactory,
        SalesOrderCollectionFactory      $salesOrderCollectionFactory,
        PriceCurrencyInterface           $priceCurrency,
        EbizchargeConfigModel            $ebizchargeConfigModel,
        EbizchargeLogger                 $ebizchargeLogger,
        CustomerModel                    $customerModel,
        CollectionFactory                $productListFactory,
        Currency                         $directoryCurrency,
        quoteCurrency                    $quoteCurrency,
        TranApi                          $tranApi,
        OrderSubscriptionFactory         $orderSubscriptionFactory,
        CustomerFactory                  $customerFactory,
        CustomerRepositoryInterface      $customerRepository,
        QuoteFactory                     $quoteFactory,
        QuoteManagement                  $quoteManagement,
        OrderSender                      $orderSender,
        ProductFactory                   $productFactory,
        SyncAssetsFactory                $syncAssetsFactory,
        ItemsForReindex                  $itemsForReindex,
        CarrierFactory                   $carrierFactory,
        OrderRepositoryInterface         $orderRepository,
        InvoiceService                   $invoiceService,
        TransactionFactory               $transactionFactory,
        Collection                       $orderCollection,
        ConfigResource                   $configResource,
        CoreOrder\AddressRepository      $addressRepository,
        CustomerInterfaceFactory         $customerInterfaceFactory,
        CartManagementInterface          $cartManagementInterface,
        CartRepositoryInterface          $cartRepositoryInterface,
        EbizInvoice                      $ebizInvoice,
        FutureSubscriptionFactory        $futureSubscriptionFactory,
        RecurringFactory                 $recurringFactory,
        orderGridResourceModelCollection $orderGridResourceModelCollection,
        AddressFactory                   $addressFactory,
        AddressRepositoryInterface       $addressRepositoryInterface,
        Sequence                         $sequenceModel,
        Profile                          $profileModel,
        OrderService                     $orderService,
        CoreOrder\ItemRepository         $orderItemRepository,
        Session                          $checkoutSession,
        CustomerSession                  $customerSession,
        ItemFactory                      $quoteItemFactory,
        IndexerFactory                   $indexFactory,
        IndexerCollectionFactory         $indexCollection,
        SessionManagerInterface          $sessionManagerInterface,
        OrderManagementInterface         $orderManagementInterface,
        AdminOrderCreate                 $adminCreatOrder,
        OrderAddressInterface            $OrderAddressInterface,
        OrderAddressInterfaceFactory     $orderAddressInterfaceFactory,
        OrderItemFactory                 $orderItemFactory,
        CartInterfaceFactory             $cartInterfaceFactory,
        CartInterface                    $cartInterface,
        CartItemInterfaceFactory         $cartItemInterfaceFactory,
        OrderFactory                     $orderFactory,
        AdminQuoteSession                $adminQuoteSession,
        AddressInterface                 $quoteAddressInterface,
        Surcharge                        $surchargeModel,
        TaxCalculation                   $taxCalculation,
        TaxClassModel                    $taxClassModel,
        AbstractResource                 $resource = null,
        AbstractDb                       $resourceCollection = null,
        ResolverInterface                $localeResolver = null,
        ProductOption                    $productOption = null,
        OrderItemRepositoryInterface     $itemRepository = null,
        SearchCriteriaBuilder            $searchCriteriaBuilder = null,
        ScopeConfigInterface             $scopeConfig = null,
        RegionFactory                    $regionFactory = null,
        array                            $data = []
    )
    {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $timezone,
            $storeManager,
            $orderConfig,
            $productRepository,
            $orderItemCollectionFactory,
            $productVisibility,
            $invoiceManagement,
            $currencyFactory,
            $eavConfig,
            $orderHistoryFactory,
            $addressCollectionFactory,
            $paymentCollectionFactory,
            $historyCollectionFactory,
            $invoiceCollectionFactory,
            $shipmentCollectionFactory,
            $memoCollectionFactory,
            $trackCollectionFactory,
            $salesOrderCollectionFactory,
            $priceCurrency,
            $productListFactory,
            $resource,
            $resourceCollection,
            $data,
            $localeResolver,
            $productOption,
            $itemRepository,
            $searchCriteriaBuilder,
            $scopeConfig,
            $regionFactory
        );

        /** @var _registry */
        $this->_registry = $registry;
        /** @var _extensionFactory */
        $this->_extensionFactory = $extensionFactory;
        /** @var _customAttributeFactory */
        $this->_customAttributeFactory = $customAttributeFactory;
        /** @var _timezone */
        $this->_timezone = $timezone;
        /** @var _storeManager */
        $this->_storeManager = $storeManager;
        /** @var _ebizInvoice */
        $this->_ebizInvoice = $ebizInvoice;
        /** @var _orderConfig */
        $this->_orderConfig = $orderConfig;
        /** @var _productRepository */
        $this->_productRepository = $productRepository;
        /** @var _orderItemCollectionFactory */
        $this->_orderItemCollectionFactory = $orderItemCollectionFactory;
        /** @var _productVisibility */
        $this->_productVisibility = $productVisibility;
        /** @var _invoiceManagement */
        $this->_invoiceManagement = $invoiceManagement;
        /** @var _currencyFactory */
        $this->_currencyFactory = $currencyFactory;
        /** @var _eavConfig */
        $this->_eavConfig = $eavConfig;
        /** @var _orderHistoryFactory */
        $this->_orderHistoryFactory = $orderHistoryFactory;
        /** @var _addressCollectionFactory */
        $this->_addressCollectionFactory = $addressCollectionFactory;
        /** @var _paymentCollectionFactory */
        $this->_paymentCollectionFactory = $paymentCollectionFactory;
        /** @var _historyCollectionFactory */
        $this->_historyCollectionFactory = $historyCollectionFactory;
        /** @var _invoiceCollectionFactory */
        $this->_invoiceCollectionFactory = $invoiceCollectionFactory;
        /** @var _shipmentCollectionFactory */
        $this->_shipmentCollectionFactory = $shipmentCollectionFactory;
        /** @var _memoCollectionFactory */
        $this->_memoCollectionFactory = $memoCollectionFactory;
        /** @var _trackCollectionFactory */
        $this->_trackCollectionFactory = $trackCollectionFactory;
        /** @var _orderGridResourceModelCollection */
        $this->_orderGridResourceModelCollection = $orderGridResourceModelCollection;
        /** @var _salesOrderCollectionFactory */
        $this->_salesOrderCollectionFactory = $salesOrderCollectionFactory;
        /** @var _priceCurrency */
        $this->_priceCurrency = $priceCurrency;
        /** @var _productListFactory */
        $this->_productListFactory = $productListFactory;
        /** @var _resource */
        $this->_resource = $resource;
        /** @var  _addressRepositoryInterface */
        $this->_addressRepositoryInterface = $addressRepositoryInterface;
        /** @var _resourceCollection */
        $this->_resourceCollection = $resourceCollection;
        /** @var _data */
        $this->_data = $data;
        /** @var _localeResolver */
        $this->_localeResolver = $localeResolver;
        /** @var _productOption */
        $this->_productOption = $productOption;
        /** @var _itemRepository */
        $this->_itemRepository = $itemRepository;
        /** @var _searchCriteriaBuilder */
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        /** @var scopeConfig */
        $this->_scopeConfig = $scopeConfig;
        /** @var regionFactory */
        $this->_regionFactory = $regionFactory;
        /** @var _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var _ebizchargeConfigModel */
        $this->_ebizchargeConfigModel = $ebizchargeConfigModel;
        /** @var _soapApiModel */
        $this->_soapApiModel = $tranApi;
        /** @var _customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var _customerRepository */
        $this->_customerRepository = $customerRepository;
        /** @var _quoteFactory */
        $this->_quoteFactory = $quoteFactory;
        /** @var _quoteManagement */
        $this->_quoteManagement = $quoteManagement;
        /** @var _orderSender */
        $this->_orderSender = $orderSender;
        /** @var _productFactory */
        $this->_productFactory = $productFactory;
        /** @var _syncAssetsFactory */
        $this->_syncAssetsFactory = $syncAssetsFactory;
        /** @var _carrierFactory */
        $this->_carrierFactory = $carrierFactory;
        /** @var _itemsForReindex */
        $this->_itemsForReindex = $itemsForReindex;
        /** @var _directoryCurrency */
        $this->_directoryCurrency = $directoryCurrency;
        /** @var _quoteCurrency */
        $this->_quoteCurrency = $quoteCurrency;
        /** @var _customerModel */
        $this->_customerModel = $customerModel;
        /** @var _orderRepository */
        $this->_orderRepository = $orderRepository;
        /** @var _invoiceService */
        $this->_invoiceService = $invoiceService;
        /** @var _transactionFactory */
        $this->_transactionFactory = $transactionFactory;

        /** @var _orderCollection */
        $this->_orderCollection = $orderCollection;
        /** @var _addressRepository */
        $this->_addressRepository = $addressRepository;
        /** @var _configResource */
        $this->_configResource = $configResource;
        /** @var _customerInterfaceFactory */
        $this->_customerInterfaceFactory = $customerInterfaceFactory;
        /** @var _cartManagementInterface */
        $this->_cartManagementInterface = $cartManagementInterface;
        /** @var _cartRepositoryInterface */
        $this->_cartRepositoryInterface = $cartRepositoryInterface;
        /** @var _orderService */
        $this->_orderService = $orderService;
        /** @var _futureRecurringFactory */
        $this->_futureRecurringFactory = $futureSubscriptionFactory;
        /** @var _recurringFactory */
        $this->_recurringFactory = $recurringFactory;
        /** @var  _parentOrder */
        $this->_parentOrder = null;
        /** @var _orderSubscriptionFactory */
        $this->_orderSubscriptionFactory = $orderSubscriptionFactory;
        /** @var _addressFactory */
        $this->_addressFactory = $addressFactory;
        /** @var  _sequenceModel */
        $this->_sequenceModel = $sequenceModel;
        /** @var  _sequenceProfileModel */
        $this->_sequenceProfileModel = $profileModel;
        /** @var  _orderItemRepository */
        $this->_orderItemRepository = $orderItemRepository;
        /** @var  _checkoutSession */
        $this->_checkoutSession = $checkoutSession;
        /** @var  _customerSession */
        $this->_customerSession = $customerSession;
        /** @var  quoteItemFactory */
        $this->quoteItemFactory = $quoteItemFactory;
        /** @var  indexFactory */
        $this->indexFactory = $indexFactory;
        /** @var  indexCollection */
        $this->indexCollection = $indexCollection;
        /** @var  sessionManagerInterface */
        $this->sessionManagerInterface = $sessionManagerInterface;
        /** @var  orderManagementInterface */
        $this->orderManagementInterface = $orderManagementInterface;
        /** @var  adminCreateOrder */
        $this->adminCreateOrder = $adminCreatOrder;
        /** @var  OrderAddressInterface */
        $this->OrderAddressInterface = $OrderAddressInterface;
        /** @var  OrderAddressInterfaceFactory */
        $this->orderAddressInterfaceFactory = $orderAddressInterfaceFactory;
        /** @var  orderIteFactory */
        $this->orderItemFactory = $orderItemFactory;
        /** @var  cartInterfaceFactory */
        $this->cartInterfaceFactory = $cartInterfaceFactory;
        /** @var  cartInterface */
        $this->cartInterface = $cartInterface;
        /** @var  cartItemInterfaceFactory */
        $this->cartItemInterfaceFactory = $cartItemInterfaceFactory;
        /**
         * Order Factory
         */
        $this->orderFactory = $orderFactory;
        /**
         * admin Quote Session
         */
        $this->adminQuoteSession = $adminQuoteSession;
        /**
         * Quote address interface
         */
        $this->_quoteAddressInterface = $quoteAddressInterface;
        /**
         * Surcharge Model
         */
        $this->surchargeModel = $surchargeModel;

        $this->taxCalculation = $taxCalculation;
        $this->taxClassModel = $taxClassModel;

    }

    /**
     * Is Order Need To be Updated
     *
     * @param mixed $startDateModified
     * @param mixed $endDateModified
     * @return bool
     * @throws Exception
     */
    public function isOrderNeedTobeUpdated($startDateModified = null, $endDateModified = null)
    {
        //var_dump($startDateModified, $endDateModified);
        $isNeedTobeUpdated = false;
        $startDateTime = new DateTime($startDateModified);
        $endDateTime = new DateTime($endDateModified);
        $diffDateTime = $startDateTime->diff($endDateTime);

        $diffDays = $diffDateTime->days;
        $diffHours = $diffDateTime->h;
        $diffMinutes = $diffDateTime->i;

        if ((int)$diffDays > 0 || (int)$diffHours > 0 || (int)$diffMinutes) {
            $isNeedTobeUpdated = true;
        }

        return $isNeedTobeUpdated;
    }

    /**
     * Prepare Sequency Order Profile
     *
     * @return string
     */
    public function preparePreAuthOrderNumber()
    {
        /** @var  $sequenceProfileModel */
        $sequenceProfileModel = $this->_sequenceProfileModel->load(self::DEFAULT_SEQUENCE_PROFILE_ORDER_ID);

        /** @var  $seqOrderPrefix */
        $seqOrderPrefix = $sequenceProfileModel->getData("prefix");
        /** @var  $seqOrderMaxValue */
        $seqOrderMaxValue = $sequenceProfileModel->getData("max_value");
        /** @var  $seqOrderWarningValue */
        $seqOrderWarningValue = $sequenceProfileModel->getData("warning_value");

        /**
         * last Order
         */
        $lastOrder = $this->getCollection()->addFieldToSelect(Order::INCREMENT_ID)
            ->setOrder(Order::ENTITY_ID, "DESC");
        /**
         * Last Order Id
         */
        $lastOrderItem = $lastOrder->getFirstItem();

        if ($lastOrderItem) {
            $orderNumber = $lastOrderItem->getIncrementId() . "-auth-";
        } else {
            $orderNumber = $seqOrderPrefix . "-" . rand((int)$seqOrderMaxValue, (int)$seqOrderWarningValue) . "-auth-";
        }

        return $orderNumber;
    }

    /**
     * Prepare Recurring Orders
     *
     * @param string $startDate
     * @param bool $isCron
     * @param string $outType
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function prepareRecurringOrders(DateTime $startDate = null, bool $isCron = false, string $outType = 'cli'): array
    {
        /** @var $recurringOrdersCollection */
        $recurringOrdersCollection = [];

        /** @var  $fromDate */
        $fromDate = (array)$startDate;
        /** @var $futureRecurringOrders */
        $futureRecurringOrders = $this->_futureRecurringFactory->create()
            ->prepareFutureRecurringOrdersCollection($startDate);

        $totalRecurringOrders = count($futureRecurringOrders);

        // phpcs:disable
        print_r("Found total " . $totalRecurringOrders . " recurring orders at EBizCharge Gateway From:" .
            $fromDate['date'] . " till today : ");
        print_r("\n" . "\r");

        /** delete orders all for temp */
        //  $this->deleteOrders();

        /** if future Recurring Orders  */
        if (count($futureRecurringOrders) > 0) {
            foreach ($futureRecurringOrders as $recurringOrder) {
                $customerId = isset($recurringOrder['customer_id']) ? $recurringOrder['customer_id'] : 0;
                $orderNumber = isset($recurringOrder['order_number']) ? $recurringOrder['order_number'] : 0;

                $customer = $this->_customerFactory->create()->load($customerId);

                print_r("Placing recurring Order " . $orderNumber . " for " . $customer->getEmail() .
                    "[" . $customerId . "]");
                print_r("\n" . "\r");

                if (!$customer->getEntityId()) {
                    print_r("The Customer " . $customer->getEmail() . "[" . $customerId .
                        "] does not found locally, hence skipped ");
                    print_r("\n" . "\r");
                    continue;
                }
                // phpcs:enable

                /** Create a Recurring Orders Collection */
                $recurringOrdersCollection[] = $this->placeRecurringOrder($recurringOrder);
            }
        }
        return $recurringOrdersCollection;
    }

    /**
     * Place Recurring Order
     *
     * @param array $recurringParams
     * @return mixed
     * @throws \Exception
     */
    public function placeRecurringOrder(array $recurringParams = []): mixed
    {
        $recurringOrderResp = [
            'status' => false,
            'error' => true,
            'message' => __("Error occurred during placing recurring order."),
            'response' => []
        ];
        $recurringOrderParams = [];

        try {

            /** @var  $recurringOrderParams */
            $recurringOrderParams = $this->prepareRecurringOrderParams($recurringParams);
            $futureRecurringId = $recurringParams['future_recurring_id'];

            if ($recurringOrderParams['error'] === false) {
                /** @var  $currentOrderParams */
                $currentOrderParams = $recurringOrderParams['recurring_order_params'];
                $isRecurring = true;
                /** @var  $orderCreated */
                $orderCreated = $this->createOrder($currentOrderParams, $isRecurring);

                if ($orderCreated["error"] === false) {
                    $recurringOrderParams['new_increment_id'] = $orderCreated['response']['increment_id'];
                    $this->_futureRecurringFactory->create()
                        ->load($futureRecurringId)
                        ->setOrderedStatus("0")
                        ->save();
                }
            }
        } catch (LocalizedException $exception) {
            $this->_ebizchargeLogger->addCritical(__("Exception occurred during placing order Exception: " .
                $exception->getMessage()));
            $recurringOrderResp['message'] = $recurringOrderParams['message'];
        }

        /**
         * adding recurring order logs
         */
        $this->_orderSubscriptionFactory->create()->addOrderSubscriptionLogs($recurringOrderParams);

        return $recurringOrderResp;
    }

    /**
     * Prepare Recurring Order Params
     *
     * @param array $recurringOrder
     * @return array
     * @throws LocalizedException
     */
    public function prepareRecurringOrderParams($recurringOrder = []): array
    {
        /** @var $orderNumber */
        $orderNumber = (string)$recurringOrder['mage_order_id'];
        $recurringOrderParams = $recurringOrder;

        /** @var $recurringOrderResp */
        $recurringOrderResp = [
            'status' => false,
            'message' => __("Error occurred during placing this " . $orderNumber . " recurring order "),
            'error' => true,
            'recurring_order_params' => []
        ];

        $store = $this->getStore();
        $customerId = isset($recurringOrder['customer_id']) ? $recurringOrder['customer_id'] : 0;

        /** @var  $orderCurrency */
        $orderCurrency = $store->getCurrentCurrency()->getCurrencyCode();

        /** @var  $order */
        $order = $this->load($orderNumber);

        if ($order->getId()) {
            $orderCurrency = $order->getBaseCurrency();
        }

        /** @var $recurringDate */
        $recurringDate = $recurringOrder['recurring_date'];
        $dateToday = date('Y-m-d');
        /** @var  $recurringItemId */
        $recurringId = $recurringOrder['recurring_id'];

        /** @var  $orderedItems */
        $orderedItems = $recurringOrder['items'];

        /** @var Recurring Order */
        $recurringOrder['currency'] = $orderCurrency;

        /** @var $magCustomerId */
        $magCustomerId = isset($recurringOrder['customer_id']) ? $recurringOrder['customer_id'] : 0;

        /** @var $savedShippingMethod */
        $shippingMethod = isset($recurringOrder['shipping_method']) ? $recurringOrder['shipping_method'] : '';

        /** Recurring Order Params */

        $items = [];

        try {
            $orderedItems = $orderedItems ?: [];

            foreach ($orderedItems as $key => $orderedItem) {
                $orderedItem['ebiz_transaction_payment'] = [];
                $recurringOrderParams['recurring_item_id'] = $orderedItem['item_id'];
                $recurringOrderParams['ebiz_recurring_payment_id'] = '';

                /** if ordered recurring
                 * remaining items turn to zero
                 */
                if (isset($orderedItem['recurring_remaining']) && $orderedItem['recurring_remaining'] === 0) {
                    /** @var $recurringName */
                    $recurringName = $orderedItem['item_name'];

                    // Suspend subscription on Econnect
                    $suspendRecurringPaymentStatus = $this->_recurringFactory->create()
                        ->suspendScheduledRecurringPaymentStatus(
                            $recurringOrder['ebiz_scheduled_payment_reference_id'],
                            SoapApiModelInterface::RECURRING_PAYMENT_STATUS_SUSPENDED
                        );

                    if ($suspendRecurringPaymentStatus['error'] === false) {
                        /** logging Cron Orders completed */
                        $this->_ebizchargeLogger->addInfo(__('All recurring Orders has been completed for ' .
                            $recurringName . '. and the Status of this has marked as suspended on Gateway.'));

                        /** @var $recurringObject */
                        $recurringObject = $this->_recurringFactory->create()->load($recurringId);

                        if ($recurringObject) {
                            $recurringObject->setRecStatus(SoapApiModelInterface::RECURRING_PAYMENT_STATUS_SUSPENDED)
                                ->save();
                        }
                        $recurringOrderResp['error'] = true;
                        $recurringOrderResp['status'] = false;
                        $recurringOrderResp['message'] = __(
                            'All orders for the recurring item has been completed'
                        );
                    } else {
                        $recurringOrderResp['message'] = __('Error occurred during suspending transaction');
                    }
                }

                /** @var  $scheduledRecurringPaymentInternalId */
                $scheduledRecurringPaymentInternalId = $orderedItem['ebiz_scheduled_payment_reference_id'] ?? '';

                /** @var  $ebizRecurringPaymentInternalId */
                $ebizRecurringPaymentInternalIdResp = $this->_recurringFactory->create()->searchRecurringPayment(
                    '',
                    $scheduledRecurringPaymentInternalId,
                    $recurringDate,
                    $dateToday
                );

                if ($ebizRecurringPaymentInternalIdResp['error'] === false) {

                    $orderedItem['ebiz_transaction_payment'] = $ebizRecurringPaymentInternalIdResp['payments'];
                    $ebizPaymentInternalId = $orderedItem['ebiz_transaction_payment'][0]['PaymentInternalId'] ?? '';
                    $deductedAmount = $this->priceCurrency->convert($recurringOrder['item_amount']);
                    $itemQty = $orderedItem['qty'] ?? 0;
                    $recurringOrderResp['message'] = __('Success: ' . $itemQty . ' ' .
                        $recurringOrder['recurring_item_name'] . ' totaling ' . $deductedAmount .
                        ' has been successfully processed with Internal Payment ID: ' .
                        $ebizPaymentInternalId . ' ');
                    $recurringOrderParams['ebiz_recurring_payment_id'] = $ebizPaymentInternalId;

                    /** Subscription Order */
                    $this->_ebizchargeLogger->addInfo(__(
                        'Success: Added the item to the final recurred order  item id = ' .
                        $orderedItem['item_id']
                    ));
                    $recurringOrderResp['status'] = true;
                    $recurringOrderResp['error'] = false;

                } else {
                    $recurringOrderResp['message'] = __('Error: The payment info for "' .
                        $recurringOrder['recurring_item_name'] . '" with total amount of ' .
                        $recurringOrder['item_amount'] . ' does not exists with EBizCharge Payment Gateway. ');
                    $recurringOrderResp['message'] = __(
                        'Error occurred during Creating Order: The payment info for "' .
                        $this->getBaseCurrency()->getCode() . "" . $recurringOrder['recurring_item_name'] .
                        '" with total amount of "' . $recurringOrder['item_amount'] .
                        '" does not exists with EBizCharge Payment Gateway.'
                    );
                    $recurringOrderResp['status'] = false;
                    $recurringOrderResp['error'] = true;
                    // phpcs:ignore
                    $this->_ebizchargeLogger->addError(__('Transaction not found at EBizCharge Gateway and so it cannot be added to recurred order  item id = ' . $orderedItem['item_id']));
                }

                $items[] = $orderedItem;
            }

            $recurringOrderParams['items'] = $items;

            /** @var $customerData */
            $customer = $this->_customerFactory->create()->load((int)$magCustomerId);

            /** @var $customerEmail */
            $customerEmail = $customer->getEmail() ? $customer->getEmail() : '';

            if (empty($customerEmail)) {
                $this->_ebizchargeLogger->addError(__(
                    "Error occurred during fetching customer, no customer email found  Email: " . $customerEmail
                ));
                $recurringOrderResp['message'] = __(
                    "Error occurred during fetching customer, no customer email found  Email: " . $customerEmail
                );
                $recurringOrderResp['status'] = false;
                $recurringOrderResp['error'] = true;
            }

            /** adding customer data */
            $recurringOrderParams['customer']['email'] = $customerEmail;
            $recurringOrderParams['customer']['ebiz_customer_id'] = $customer->getEcCustId();
            $recurringOrderParams['customer']['ebiz_internal_id'] = $customer->getEcCustInternalId();
            $recurringOrderParams['customer']['ebiz_customer_token'] = $customer->getEcCustToken();
            $recurringOrderParams['customer']['default_billing_address_id'] = $recurringOrder['billing_address_id']
                ?? 0;
            $recurringOrderParams['customer']['default_shipping_address_id'] = $recurringOrder['shipping_address_id']
                ?? 0;

            /** @var $alreadyPlacedOrder */

            $alreadyPlacedOrder = $this->loadByIncrementIdAndStoreId((string)$orderNumber, $store->getId());

            /** already placed order  */
            if ($alreadyPlacedOrder && $alreadyPlacedOrder->getId()) {
                /**
                 * @var $savedOrderPayment OrderPaymentInterface
                 */
                $savedOrderPayment = $alreadyPlacedOrder->getPayment();

                if (!$alreadyPlacedOrder || !$savedOrderPayment) {
                    $erroMessage = __(
                        'Error occurred during Creating Order: Error in loading parent saved Order (' .
                        $orderNumber . ')'
                    );

                    /** Logging the error */
                    $this->_ebizchargeLogger->addError($erroMessage);

                    /** adding recurring Order Subscription Logs */
                    $recurringOrderResp['message'] = $erroMessage;
                }

                $billingAddressId = $alreadyPlacedOrder->getBillingAddressId();
                $shippingAddressId = $alreadyPlacedOrder->getShippingAddress()->getId();
                $shippingMethod = $alreadyPlacedOrder->getShippingMethod();

            } else {

                /** load admin subscription info */
                $billingAddressId = $recurringOrder['billing_address_id'];
                $shippingAddressId = $recurringOrder['shipping_address_id'];

            }

            /** if saved Shipping Address  */
            if (!$billingAddressId || !$shippingAddressId) {
                /** @var $erroMessage */
                $erroMessage = __('Error occurred during creating Order: Shipping address is empty or invalid.');

                /** logging to the logger */
                $this->_ebizchargeLogger->addError($erroMessage);

                /** adding recurring Order Subscription Logs */
                $recurringOrderResp['message'] = $erroMessage;
                $recurringOrderResp['status'] = false;
                $recurringOrderResp['error'] = true;

                // Suspend subscription on Econnect
                // //0 Active
                // //1 Suspended
                // //2 Expired
                // //3 Canceled
            }

            $itemId = isset($recurringOrder['mage_item_id']) ? $recurringOrder['mage_item_id'] : 0;

            /** @var  $product */
            $product = $this->_productFactory->create()->load($itemId);

            if (!$product->getId() || !$product->isSaleable()) {
                /** @var $erroMessage */
                $erroMessage = __(
                    'Error occurred during Creating Order: Product not found or has configurable type.'
                );

                /** logging to the logger */
                $this->_ebizchargeLogger->addError($erroMessage);

                /** adding recurring Order Subscription Logs */
                $recurringOrderResp['message'] = $erroMessage;
                $recurringOrderResp['status'] = false;
                $recurringOrderResp['error'] = true;

            }

            if (!$shippingMethod) {
                $erroMessage = __(
                    'Error occurred during Create Order: Shipping method is empty and order cannot be created.'
                );

                /** logging to the logger */
                $this->_ebizchargeLogger->addError($erroMessage);

                /** adding recurring Order Subscription Logs */
                $recurringOrderResp['message'] = $erroMessage;
                $recurringOrderResp['status'] = false;
                $recurringOrderResp['error'] = true;

            }

            $excludeAmount = 0;
            $dateToday = date("Y-m-d");
            $recurringDate = date("Y-m-d", strtotime($recurringOrder['recurring_date']));

            /** Cron occurence logging */
            $this->_ebizchargeLogger->addInfo(__('Cron: Today = ' . $dateToday . ', Recurring due date = ' .
                $recurringDate));

            /** Shipping Method */
            $recurringOrderParams['shipping_method'] = $shippingMethod;

        } catch (Exception $exp) {
            /** @var $erroMessage */
            $erroMessage = __('Exception occurred during creating recurring Order Error:' . $exp->getMessage());

            $recurringOrderParams['shipping_method'] = '';
            /** logging to the logger */
            $this->_ebizchargeLogger->addError($erroMessage);
            /** adding recurring Order Subscription Logs */
            $recurringOrderResp['message'] = $erroMessage;
            $recurringOrderResp['status'] = false;
            $recurringOrderResp['error'] = true;
        }

        /** finising the params */
        $recurringOrderResp['recurring_order_params'] = $recurringOrderParams;

        return $recurringOrderResp;
    }

    /**
     * Create Order
     *
     * @param array $orderParams
     * @param bool $isRecurring
     * @return array
     * @throws \Exception
     */
    public function createOrder(array $orderParams = [], bool $isRecurring = false): array
    {
        /** @var  $ebizSalesOrderNumber */
        $ebizSalesOrderNumber = isset($orderParams['salesordernumber']) ? $orderParams['salesordernumber'] : "";
        $orderResponse = [
            'error' => true,
            'status' => false,
            'message' => __("Error occurred during order creation."),
            'response' => [
                'increment_id' => $ebizSalesOrderNumber,
                'order_id' => 0
            ]
        ];

        try {

            /** @var  $isRecurring */
            $isRecurring = $isRecurring ? true : false;

            /** create Order by Order Params */
            $store = $this->getStore();
            $storeId = $this->getStoreId();
            $websiteId = $this->getStore()->getWebsiteId();
            $customerIsGuest = isset($orderParams['customer']['is_guest']) ? true : false;
            $customerParams = isset($orderParams["customer"]) ? $orderParams["customer"] : [];
            $ebizCustomerId = isset($customerParams["ebiz_customer_id"]) ? $customerParams["ebiz_customer_id"] : "";
            $ebizCustomerId = isset($orderParams["customerid"]) ? $orderParams["customerid"] : $ebizCustomerId;

            /**
             * Create Customer Factory
             */
            $customerFactory = $this->_customerFactory->create();

            /** @var $storeCurrencyCode get store Currency Code */
            $storeCurrencyCode = $store->getCurrentCurrencyCode();

            /*** create quote Factory **/
            $quote = $this->_quoteFactory->create();
            $quote->setStore($store); //set store for our quote
            $customerBillingAddress = null;
            $customerShippingAddress = null;


            /** check customer if guest then create customer as guest */
            if ($customerIsGuest) {
                $guestCustomerId = $this->_customerFactory->create()->getGuestCustomer();
                $guestCustomer = $this->_customerFactory->create()->load($guestCustomerId);

                /** setting quote if customer is guest */
                $customerBillingAddress = $guestCustomer->getDefaultBillingAddress();
                $customerShippingAddress = $guestCustomer->getDefaultShippingAddress();

                /** @var $customer */
                $customer = $this->_customerRepository->getById($guestCustomer->getId());
                $quote->assignCustomer($customer); //Assign quote to customer

            } else {
                $customer = $this->_customerFactory->create()->loadByEbizCustomerId($ebizCustomerId);

                // $customer->delete(); //tmep
                /** if customer
                 * does not exists locally
                 * create customer locally
                 */
                if (!$customer->getId()) {
                    $ebizCustomer = $this->_customerFactory->create()->getEbizCustomerById($ebizCustomerId);

                    if ($ebizCustomer && isset($ebizCustomer->GetCustomerResult)) {
                        $ebizCustomer = $ebizCustomer->GetCustomerResult;
                        $customerId = $this->_customerFactory->create()->saveEbizchargeCustomerToLocal($ebizCustomer);
                        $customer = $this->_customerFactory->create()->load($customerId);

                        $this->_ebizchargeLogger->addInfo(__(
                            "New Customer from EBizCharge Gateway has been saved to local system and loaded..."
                        ));
                    } else {
                        $this->_ebizchargeLogger->addError(__(
                            "Error occurred, this customer could not found at EBizCharge Gateway..."
                        ));
                        return $orderResponse;
                    }
                }

                $customerBillingAddressId = $orderParams['billing_address_id'] ?? 0;
                $customerShippingAddressId = $orderParams['shipping_address_id'] ?? 0;

                $customerId = $customer->getEntityId();

                if ($customerBillingAddressId !== 0) {
                    $customerShippingAddress = $this->_addressFactory->create()->load($customerBillingAddressId);
                    $customerShippingAddress->setCustomerId($customerId)
                        ->setIsDefaultBilling(0)
                        ->setIsDefaultShipping(0)
                        ->setSaveInAddressBook(0)
                        ->save();
                }

                if ($customerBillingAddressId !== 0) {
                    $customerShippingAddress = $this->_addressFactory->create()->load($customerShippingAddressId);
                    $customerShippingAddress->setCustomerId($customerId)
                        ->setIsDefaultBilling(0)
                        ->setIsDefaultShipping(0)
                        ->setSaveInAddressBook(0)
                        ->save();
                }

                /** @var $customerBillingAddress */
                $customerBillingAddress = $customer->getDefaultBillingAddress();

                if (!$customerBillingAddress) {
                    return $orderResponse;
                }
                /** @var $customerShippingAddress */
                $customerShippingAddress = (object)$customer->getDefaultShippingAddress();

                if (!$customerShippingAddress) {
                    return $orderResponse;
                }

                /** saving region Id in case there is no Region Id */
                if (($customerBillingAddress && !$customerBillingAddress->getRegionId()) || !$customerBillingAddress->getCountryId()) {
                    $customerBillingAddress->setRegionId(self::DEFAULT_REGION_ID);
                    $customerBillingAddress->setCountryId(self::DEFAULT_COUNTRY_ID);
                    $customerBillingAddress->save();
                }

                /** saving Country and Region Id in case of no Region Id */
                if ((!$customerShippingAddress && !$customerShippingAddress->getRegionId()) || !$customerShippingAddress->getCountryId()) {
                    $customerShippingAddress->setRegionId(self::DEFAULT_REGION_ID);
                    $customerShippingAddress->setCountryId(self::DEFAULT_COUNTRY_ID);
                    $customerShippingAddress->save();
                }
            }

            $ebizCustomerId = $customer->getEcCustId();
            /** @var  $customer */
            $customer = $this->_customerRepository->getById($customer->getEntityId());
            $quote->assignCustomer($customer); //Assign quote to customer

            /** setting quote items */
            $orderedItems = isset($orderParams['items']) ? $orderParams['items'] : [];
            $recurringPaymentData = [];

            /** count ordered items and if items exists */
            if (count($orderedItems) > 0) {

                foreach ($orderedItems as $orderedItem) {

                    $itemId = trim(preg_replace('/\s+/', ' ', $orderedItem['itemid'] ?? ''));

                    if ($isRecurring === true) {
                        $itemId = trim(preg_replace(
                            '/\s+/',
                            ' ',
                            $orderedItem['product_id'] ?? ''
                        ));
                        $recurringPaymentData = $orderedItem['ebiz_transaction_payment'] ?: [];
                    }
                    $itemId = str_replace(" ", "", $itemId);

                    /** @var $product */
                    $product = $this->_productFactory->create()->load($itemId);

                    if (!$product->getId()) {
                        /** add product to local Magento */
                        $productParams = $this->_productFactory->create()
                            ->prepareProductParamsFromEbizOrderItem($orderedItem);

                        $productId = $this->_syncAssetsFactory->create()
                            ->importEbizchargeItemToMagento($productParams);

                        if ($productId) {
                            $product = $this->_productFactory->create()->load($productId);

                        }
                    }

                    $indexerId = "cataloginventory_stock";
                    /**
                     * if we do not have a local product
                     */
                    if ($product->getId()) {

                        /** @var  $productSku */
                        $productSku = $product->getSku();

                        if ($product->getSku() && !empty($productSku)) {

                            /** @var get salaable quantity $salable */
                            // $salable = $this->_getSalableQuantityDataBySku->execute($productSku);
                            //  $productQty = (double)$salable[0]['qty'];
                            $productQty = 0;
                            $productStocks = $product->getQuantityAndStockStatus() ?? [];

                            if (is_array($productStocks) && isset($productStocks["is_in_stock"]) && (bool)$productStocks["is_in_stock"]) {
                                $productQty = isset($productStocks["qty"]) ? (float)$productStocks["qty"] : 0;
                            }

                            if ($productQty > 0) {

                                /** if product then add stock so that order can be placed */
                                $stockParams = [
                                    'is_in_stock' => ProductInterface::DEFAULT_IS_PRODUCT_IN_STOCK,
                                    'manage_stock' => ProductInterface::DEFAULT_MANAGE_STOCK,
                                    'is_salable' => 1,
                                    'use_config_notify_stock_qty' => ProductInterface::DEFAULT_IS_PRODUCT_IN_STOCK,
                                    // phpcs:ignore
                                    'qty' => (float)($orderedItem['qty']) + (float)$productQty
                                ];

                                /** update Salable Stock Qty */
                                $this->_productFactory->create()->setSalableStockQty($productSku, $stockParams);
                            }
                            /**
                             * for MSI only
                             */
                            /** Make available Product inventory for placing order  */
                            //  $this->_productFactory->create()->makeAvailableInventoryforOrder($product);
                            /**
                             * for MSI only
                             */
                            /**
                             * $sourceStock = [
                             * 'source' => "default",
                             * 'status' => 1,
                             * // phpcs:ignore
                             * 'quantity' => (double)intval($orderedItem['qty']) + (double)$productQty
                             * ];
                             * **/
                            // $this->_productFactory->create()->setProductSourceStock($productSku, $sourceStock);

                            /**
                             * running indexer
                             */
                            $this->_productFactory->create()->runIndexer($indexerId);
                            $indexerId = "inventory";
                            $this->_productFactory->create()->runIndexer($indexerId);

                            /** adding product to quote */
                            // phpcs:ignore
                            $itemQty = (float)($orderedItem['qty']) * 1;
                            $product->setIsSuperMode(true);

                            $quote->addProduct($product, $itemQty);

                            $orderResponse['message'] = __(
                                "Success, the product has been added to cart successfully."
                            );
                            $orderResponse['error'] = false;
                            $orderResponse['status'] = true;
                        } else {
                            $this->_ebizchargeLogger->addCritical(__(
                                "Critical error occurred, Failed to create order No product Exists "
                            ));
                            $orderResponse['message'] = __(
                                "Critical error occurred, Failed to create order No product Exists "
                            );
                            $orderResponse['error'] = true;
                            $orderResponse['status'] = false;
                        }

                    } else {
                        $this->_ebizchargeLogger->addCritical(__(
                            "Critical error occurred, Failed to create order No product Exists "
                        ));
                        $orderResponse['message'] = __(
                            "Critical error occurred, Failed to create order No product Exists "
                        );
                        $orderResponse['error'] = true;
                        $orderResponse['status'] = false;
                    }
                }
            } else {
                $this->_ebizchargeLogger->addCritical(__(
                    "Critical error occurred, Failed to create order as no order items exists "
                ));
                $orderResponse['message'] = __(
                    "Critical error occurred, Failed to create order as no order items exists "
                );
                $orderResponse['error'] = true;
                $orderResponse['status'] = false;
            }
            if (!$customerBillingAddress || !$customerShippingAddress || !is_object($customerBillingAddress) || !is_object($customerShippingAddress)) {
                $orderResponse['message'] = __(
                    "Critical error occurred, Failed to create order as no order items exists "
                );
                $orderResponse['error'] = true;
                $orderResponse['status'] = false;
            }


            if ($orderResponse['error'] === false) {

                $quoteBillingAddress = isset($orderParams["BillingAddress"]) ? $orderParams["BillingAddress"] : [];
                $quoteShippingAddress = isset($orderParams["ShippingAddress"]) ? $orderParams["ShippingAddress"] : [];

                if (method_exists($customerBillingAddress, "getData")) {
                    $quoteBillingAddress = $customerBillingAddress->getData();
                }
                if (method_exists($customerShippingAddress, "getData")) {
                    $quoteShippingAddress = $customerShippingAddress->getData();
                }


                /** setting Customer Billing Address */
                $quote->getBillingAddress()->addData($quoteBillingAddress);

                /** setting Customer Shipping Address */
                $quote->getShippingAddress()->addData($quoteShippingAddress);


                /** Carrier Factory */
                $carriers = $this->_ebizchargeConfigModel->getActiveCarriers($store);
                $defaultShippingMethod = self::DEFAULT_SHIPPING_METHOD;

                /** @var $shippingMethod */
                $shippingMethod = isset($orderParams['shipping_method']) ? $orderParams['shipping_method'] : $orderParams["shipvia"];

                /** if shipping method */
                if ($shippingMethod) {
                    $defaultShippingMethod = $shippingMethod;
                } else {
                    if (count($carriers) > 0) {
                        foreach ($carriers as $carrierKey => $carrier) {
                            $shippingMethod = $carrierKey . '_' . $carrierKey;
                        }
                    }
                    $defaultShippingMethod = $shippingMethod;
                }
                $this->_ebizchargeLogger->addInfo(__(
                    "The Shipping Method $defaultShippingMethod has been added to the quote"
                ));

                /**
                 * Payment method will be added
                 * EBizCharge later
                 * $paymentMethod = PaymentInterface::CODE;
                 * **/
                $paymentMethod = PaymentInterface::CODE;

                $this->_ebizchargeLogger->addInfo(__(
                    "The Payment Method $paymentMethod has been added to the quote"
                ));

                /**  set shipping method **/
                $shippingAddress = $quote->getShippingAddress();
                $shippingAddress->setCollectShippingRates(true);
                $shippingAddress->collectShippingRates();
                $shippingAddress->setShippingMethod($defaultShippingMethod); //shipping method
                $shippingAddress->save();

                $quote->setPaymentMethod($paymentMethod);
                $quote->setInventoryProcessed(false);
                /** decrease item stock equal to qty */
                $itemsForReindex = [];
                $this->_itemsForReindex->setItems($itemsForReindex);
                $quote->setInventoryProcessed(false);

                $quote->save();

                /** @var  $transactionData */
                $customFields = isset($orderParams["custom_fields"]) ? $orderParams["custom_fields"] : [];
                $transactionId = isset($orderParams['cc_trans_id']) ? $orderParams['cc_trans_id'] : "";
                $paymentApplicationId = $orderParams["payment_application_id"] ?? [];
                $additionalInfo = isset($orderParams["additional_info"]) ? $orderParams["additional_info"] : "";

                if ($additionalInfo) {
                    $additionalInfo = isset($orderParams["additional_info"]) ?
                        // phpcs:ignore
                        unserialize($orderParams["additional_info"]) : [];
                }

                if (count($customFields) > 0) {
                    foreach ($customFields as $customField) {
                        $customFieldKey = isset($customField["FieldId"]) ? $customField["FieldId"] : "";

                        if ($customFieldKey === "transaction_id") {
                            $transactionId = isset($customField['FieldValue']) ? $customField['FieldValue'] : "";
                        }
                        if ($customFieldKey === "cc_trans_id") {
                            $transactionId = isset($customField['FieldValue']) ? $customField['FieldValue'] : "";
                        }
                        if ($customFieldKey === "payment_application_id") {
                            $paymentApplicationId = $customField['FieldValue'] ?? "";
                        }
                        if ($customFieldKey === "additional_info") {
                            $additionalInfo = isset($customField['FieldValue']) ?
                                // phpcs:ignore
                                unserialize($customField['FieldValue']) : [];
                        }
                    }
                }

                /** saving Quote  */
                if ($isRecurring === true) {
                    $tranId = "";
                    if (count($recurringPaymentData) > 0) {
                        $transactionId = $recurringPaymentData[0]['RefNum'];
                        $paymentApplicationId = $orderParams['payment_application_id'] ?? "";
                        $additionalInfo = isset($orderParams['additional_info']) ?
                            // phpcs:ignore
                            unserialize($orderParams['additional_info']) : [];
                    }
                }

                $paymentMethodData = [
                    'method' => $paymentMethod,
                    'cc_trans_id' => $transactionId,
                    'additional_information' => $additionalInfo,
                    'payment_application_id' => $paymentApplicationId,
                    'ebizCustomerId' => $ebizCustomerId,
                    'is_recurring' => $isRecurring,
                    'ebzc_parent_order_id' => $ebizSalesOrderNumber,
                    'ebzc_option' => PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_DOWNLOAD_ORDER,
                    'ebzc_option_type' => PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_DOWNLOAD_ORDER
                ];

                /** Set Sales Order Payment, We have taken check/money order **/
                $quote->getPayment()->importData($paymentMethodData);

                /** Collect Quote Totals & Save **/
                $quote->collectTotals()->save();

                /** Create Order From Quote Object */
                $order = $this->_quoteManagement->submit($quote);
                $orderIncrementId = $order->getId() ? $order->getIncrementId() : "";
                $orderId = $order->getId() ? $order->getId() : 0;

                $order = $this->load($orderId);
                $ebizSalesOrderNumber = $orderParams["salesordernumber"] ?? "";

                if ($ebizSalesOrderNumber) {
                    $order->setIncrementId($ebizSalesOrderNumber);
                }

                $ebizSalesInternalId = $orderParams["salesorderinternalid"] ?? "";
                $divisionId = isset($orderParams["divisionid"]) ? $orderParams["divisionid"] : "";
                $softwareId = isset($orderParams["software"]) ? $orderParams["software"] : "";
                $dateUploaded = $orderParams["dateuploaded"] ?? $this->_soapApiModel->getCurrentDateTime();
                $dueDate = $orderParams["dateuploaded"] ?? $this->_soapApiModel->getCurrentDateTime();

                $order->setEcOrderId($ebizSalesOrderNumber);
                $order->setEcOrderInternalId($ebizSalesInternalId);
                $order->setEcOrderCreatedIn($softwareId);
                $order->setDivisionId($divisionId);
                $order->setEcDivisionId($divisionId);
                $order->setEcPoNumber($ebizSalesOrderNumber);
                $order->setEcOrderPoNumber($ebizSalesOrderNumber);
                $order->setEcDateUploaded($dateUploaded);
                $order->setEcDueDate($dueDate);
                $order->save();
                $order->setEmailSent(0);
                $this->_ebizchargeLogger->addInfo(__("Order created and has been notified to the customer"));

                /** for send order email to customer email id */
                // $this->_orderSender->send($order);

                $this->_ebizchargeLogger->addInfo(__(
                    "Order [$orderIncrementId] downloaded from EBizCharge Gateway and Created at Local System"
                ));

                /** create invoice for the order */
                $amountDue = isset($orderParams['amountdue']) ? (double)$orderParams['amountdue'] : 0;
                $grandTotal = isset($orderParams['amount']) ? (double)$orderParams['amount'] : 0;

                /** create Invoice */
                $this->_ebizInvoice->createInvoiceWithTransaction($orderId);
                $orderResponse['message'] = __("Success, the order has been placed successfully.");
                $orderResponse['response'] = [
                    'increment_id' => $orderIncrementId,
                    'order_id' => $orderId
                ];

            }
        } catch (LocalizedException $exception) {
            $this->_ebizchargeLogger->addCritical(__("Exception during creating order : " .
                $exception->getMessage()));
            $orderResponse['message'] = __("Exception during create order : " .
                $exception->getMessage());
            $orderResponse['error'] = true;
            $orderResponse['status'] = false;
        }
        return $orderResponse;
    }

    /**
     * Get Ec Customer Id
     *
     * @return float|mixed|string|null
     */
    public function getEcCustId()
    {
        return $this->getData(OrderInterface::EC_CUST_ID);
    }

    /**
     * Get Current Date Time
     *
     * @param string $format
     * @return string
     * @throws \Exception
     */
    public function getCurrentDateTime(string $format = "Y-m-d h:i:s")
    {
        return $this->_soapApiModel->getCurrentDateTime($format);
    }

    /**
     * Set Ec Order Id
     *
     * @param mixed $ecOrderId
     * @return Order|mixed
     */
    public function setEcOrderId($ecOrderId)
    {
        return $this->setData(OrderInterface::EC_ORDER_ID, $ecOrderId);
    }

    /**
     * Set Ec Order Internal Id
     *
     * @param mixed $ecOrderInternalId
     * @return Order
     */
    public function setEcOrderInternalId($ecOrderInternalId)
    {
        return $this->setData(OrderInterface::EC_ORDER_INTERNALID, $ecOrderInternalId);
    }

    /**
     * Set Ec Order Created In
     *
     * @param mixed $ecOrderCreatedIn
     * @return Order
     */
    public function setEcOrderCreatedIn($ecOrderCreatedIn)
    {
        return $this->setData(OrderInterface::EC_ORDER_CREATED_IN, $ecOrderCreatedIn);
    }

    /**
     * Set Division Id
     *
     * @param mixed $ecDivisionId
     * @return OrderInterface
     */
    public function setDivisionId($ecDivisionId): OrderInterface
    {
        return $this->setData(self::EBIZCHARGE_DIVISION_ID, $ecDivisionId);
    }

    /**
     * Set Ec Division Id
     *
     * @param mixed $ecDivisionId
     * @return Order
     */
    public function setEcDivisionId($ecDivisionId)
    {
        return $this->setData(OrderInterface::EC_ORDER_DIVISION_ID, $ecDivisionId);
    }

    /**
     * Get EC PO Number
     *
     * @param mixed $poNumber
     * @return Order
     */
    public function setEcPoNumber($poNumber)
    {
        return $this->setData(self::EC_ORDER_PO_NUMBER, $poNumber);
    }

    /**
     * Set Ec Order PO Number
     *
     * @param mixed $ecOrderPoNumber
     * @return Order
     */
    public function setEcOrderPoNumber($ecOrderPoNumber)
    {
        return $this->setData(OrderInterface::EC_ORDER_PO_NUMBER, $ecOrderPoNumber);
    }

    /**
     * Set Ec Date Uploaded
     *
     * @param mixed $ecDateUploaded
     * @return Order
     */
    public function setEcDateUploaded($ecDateUploaded)
    {
        return $this->setData(OrderInterface::EC_ORDER_DATE_UPLOADED, $ecDateUploaded);
    }

    /**
     * Set Ec Due Date
     *
     * @param mixed $ecDueDate
     * @return Order
     */
    public function setEcDueDate($ecDueDate)
    {
        return $this->setData(OrderInterface::EC_ORDER_DUE_DATE, $ecDueDate);
    }

    /**
     * Add Application Transaction Data
     *
     * @param null|mixed $orderId
     * @param array $appParams
     * @return array
     */
    public function addApplicationTransactionData($orderId = null, $appParams = [])
    {
        /** @var  $appDataResponse */
        $appDataResponse = [
            "status" => false,
            "error" => true,
            "response" => [],
            "message" => __("Application data transaction error. ")
        ];
        /** @var  $order */
        $order = $this->load($orderId);
        $payment = $order->getPayment();
        $orderTransactionId = "";

        if ($payment) {
            $orderTransactionId = $payment->getCcTransId();
        }
        $customerId = $order->getCustomerId() ?? $order->getQuoteId();
        $customer = $this->_customerFactory->create()->load($customerId);
        $customerInternalId = "";

        if ($customer->getId()) {
            $customerInternalId = $customer->getEcCustInternalId();
        }else{
            $ebizCustomer = $this->_customerFactory->create()->getEbizCustomerById($customerId);
            if(is_object($ebizCustomer)){
                $customerInternalId = $ebizCustomer->CustomerInternalId;
            }
        }

        try {
            $storeId = $this->_configResource->getStore()->getId();
            $paymentMethodId = "";
            $expiryMonth = "";
            $expiryYear = "";

            $transactionType = $this->_configResource->transactionCommandType($storeId);
            $transType = $transactionType === "authorize" ? "Authorization" : "Capture";
            $transactionId = $orderTransactionId;
            $transactionTypeId = $transType;
            $linkedToInternalId = $order->getEcOrderInternalId();
            $transactionNotes = "Payment transaction authorization detail for Order : " . $order->getIncrementId();
            $linkedToTypeId = "SalesOrder";
            $linkedToExternalUniqueId = $order->getIncrementId();

            if ($payment) {
                $paymentType = $order->getPayment()->getAdditionalInformation("ebzc_option_type") ?? "";
                $paymentMethodId = $order->getPayment()->getAdditionalInformation("ebzc_method_id") ?? "";
            }

            $applicationCustomParams = [];

            if ($paymentType === PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_CREDIT_CARD &&
                $paymentMethodId) {

                if ($payment) {
                    $paymentMethodId = $order->getPayment()->getAdditionalInformation("ebzc_method_id") ?? "";
                    $expiryMonth = $order->getPayment()->getAdditionalInformation("exp_month") ?? "";
                    $expiryYear = $order->getPayment()->getAdditionalInformation("exp_year") ?? "";
                }

                $applicationCustomParams = [
                    "PaymentMethodID" => [
                        "FieldId" => "PaymentMethodID",
                        "FieldCaption" => "Payment Method ID",
                        "FieldName" => "PaymentMethodID",
                        "FieldValue" => $paymentMethodId,
                        "FieldType" => "text",
                        "FieldDataType" => "varchar",
                        "FieldDescription" => "Payment Method ID"
                    ],
                    "ExpirationMonth" => [
                        "FieldId" => "ExpirationMonth",
                        "FieldCaption" => "Expiry Month",
                        "FieldName" => "ExpirationMonth",
                        "FieldValue" => $expiryMonth,
                        "FieldType" => "text",
                        "FieldDataType" => "varchar",
                        "FieldDescription" => "Credit Card Expiry Month"],
                    "ExpirationYear" => [
                        "FieldId" => "ExpirationYear",
                        "FieldCaption" => "Expiry Year",
                        "FieldName" => "ExpirationYear",
                        "FieldValue" => $expiryYear,
                        "FieldType" => "text",
                        "FieldDataType" => "varchar",
                        "FieldDescription" => "Credit Card Expiry Year"
                    ]
                ];
            }

            $transactionCustomFields = $this->prepareCustomFields($applicationCustomParams);

            /** @var  $applicationDataParams */
            $applicationDataParams = [
                "CustomerInternalId" => $customerInternalId,
                "TransactionId" => $transactionId,
                "TransactionTypeId" => $transactionTypeId,
                "LinkedToInternalId" => $linkedToInternalId,
                "TransactionNotes" => $transactionNotes,
                "LinkedToTypeId" => $linkedToTypeId,
                "LinkedToExternalUniqueId" => $linkedToExternalUniqueId,
                "TransactionCustomFields" => $transactionCustomFields
            ];

            /** @var  $applicationDataResponse */
            $applicationDataResponse = $this->_soapApiModel->addApplicationTransactionData($applicationDataParams);

            $appDataResponse["status"] = $applicationDataResponse["status"];
            $appDataResponse["error"] = $applicationDataResponse["error"];
            $appDataResponse["message"] = $applicationDataResponse["message"];
            $appDataResponse["response"] = $applicationDataResponse["response"];

            if ($appDataResponse["error"] === false) {
                $this->_ebizchargeLogger->addInfo(__(
                    "Success, the Application Transaction Data has been pushed to EBizCharge Payment Hub. "
                ));
            }

        } catch (\Exception $soapFault) {
            $this->_ebizchargeLogger->addCritical(__("Payment Transaction Error Error:" .
                $soapFault->getMessage()));
            $appDataResponse["message"] = __("Exception : " . $soapFault->getMessage());
        }

        return $appDataResponse;
    }

    /**
     * Get Ec Order Internal Id
     *
     * @return float|mixed|null
     */
    public function getEcOrderInternalId()
    {
        return $this->getData(OrderInterface::EC_ORDER_INTERNALID);
    }

    /**
     * Prepare Custom Fields
     *
     * @param array $applicationCustomFields
     * @return array
     */
    public function prepareCustomFields(array $applicationCustomFields = [])
    {
        $orderApplicationCustomFields = [];

        if (count($applicationCustomFields) > 0) {
            foreach ($applicationCustomFields as $applicationCustomField) {
                $orderApplicationCustomFields[] = [
                    "FieldId" => $applicationCustomField["FieldId"] ?? "",
                    "FieldCaption" => $applicationCustomField["FieldCaption"] ?? "",
                    "FieldName" => $applicationCustomField["FieldName"] ?? "",
                    "FieldValue" => $applicationCustomField["FieldValue"] ?? "",
                    "FieldType" => $applicationCustomField["FieldType"] ?? "",
                    "FieldDataType" => $applicationCustomField["FieldDataType"] ?? "",
                    "FieldDescription" => $applicationCustomField["FieldDescription"] ?? ""
                ];
            }
        }
        return $orderApplicationCustomFields;
    }

    /**
     * Prepare Order incrementId
     *
     * @param null|mixed $incrementId
     * @return string
     */
    public function prepareOrderIncrementId($incrementId = null)
    {
        $length = strlen((string)$incrementId);
        $newCounterIncrementId = substr((string)$incrementId, $length - 4);
        $incrementIds = explode("-", $newCounterIncrementId);
        $incrementCounter = (float)$incrementIds[0];
        return $incrementId . "-" . ($incrementCounter + 1);
    }

    /**
     * Set Quote Currency
     *
     * @param Quote $quote
     * @param string $currency
     * @return mixed
     */
    public function setQuoteCurrency($quote, $currency = '')
    {
        $quoteCurrency = $this->_quoteCurrency
            ->setGlobalCurrencyCode($currency)
            ->setBaseCurrencyCode($currency)
            ->setStoreCurrencyCode($currency)
            ->setQuoteCurrencyCode($currency)
            ->setStoreToBaseRate(self::DEFAULT_CURRENCY_RATE)
            ->setStoreToQuoteRate(self::DEFAULT_CURRENCY_RATE)
            ->setBaseToGlobalRate(self::DEFAULT_CURRENCY_RATE)
            ->setBaseToQuoteRate(self::DEFAULT_CURRENCY_RATE);
        /** @var $directoryCurrency */
        $directoryCurrency = $this->_currencyFactory->create()
            ->setGlobalCurrencyCode($currency)
            ->setBaseCurrencyCode($currency)
            ->setStoreCurrencyCode($currency)
            ->setQuoteCurrencyCode($currency)
            ->setStoreToBaseRate(self::DEFAULT_CURRENCY_RATE)
            ->setStoreToQuoteRate(self::DEFAULT_CURRENCY_RATE)
            ->setBaseToGlobalRate(self::DEFAULT_CURRENCY_RATE)
            ->setBaseToQuoteRate(self::DEFAULT_CURRENCY_RATE);

        /** @var Store $store */
        $this->getStore()->setBaseCurrency($directoryCurrency);

        $quote->setForcedCurrency($directoryCurrency);
        $quote->setBaseCurrencyCode($currency);
        $quote->setGlobalCurrencyCode($currency);
        $quote->setStoreCurrencyCode($currency);
        $quote->setQuoteCurrencyCode($currency);
        $quote->setCurrency($quoteCurrency);

        return $quote;
    }

    /**
     * Get Ec Order Sync status
     *
     * @return float|mixed|null
     */
    public function getEcOrderSyncStatus()
    {
        return $this->getData(OrderInterface::EC_ORDER_SYNC_STATUS);
    }

    /**
     * Get Ec Order PO Number
     *
     * @return float|mixed|null
     */
    public function getEcOrderPoNumber()
    {
        return $this->getData(OrderInterface::EC_ORDER_PO_NUMBER);
    }

    /**
     * Get Ec Date Uploaded
     *
     * @return float|mixed|null
     */
    public function getEcDateUploaded()
    {
        return $this->getData(OrderInterface::EC_ORDER_DATE_UPLOADED);
    }

    /**
     * Get Ec Due Date
     *
     * @return float|mixed|null
     */
    public function getEcDueDate()
    {
        return $this->getData(OrderInterface::EC_ORDER_DUE_DATE);
    }

    /**
     * Get Ec Order Last Sync Date
     *
     * @return float|mixed|null
     */
    public function getEcOrderLastSyncDate()
    {
        return $this->getData(OrderInterface::EC_ORDER_LASTSYNCDATE);
    }

    /**
     * Get Ec Order Created In
     *
     * @return mixed|void
     */
    public function getEcOrderCreatedIn()
    {
        return $this->getData(OrderInterface::EC_ORDER_CREATED_IN);
    }
    // phpcs:enable

    /**
     * Get Orders From Ebizcharge By Customer
     *
     * @param array $soapParams
     * @return array
     */
    public function getOrdersFromEbizcharge($soapParams = []): array
    {
        /*
               foreach ($this->getCollection() as $order) {
                   $order->delete();
                   var_dump("delete order ." . $order->getIncrementId());
               }
       */

        try {
            /** @var $ebizOrdersCollection */
            $ebizOrdersCollection = [];

            /** @var $securityToken */
            $securityToken = $this->_soapApiModel->getUeSecurityToken();
            $storeId = $this->_configResource->getStoreId();
            $envPrefix = $this->_configResource->getEnvoirnmentPrefix($storeId);
            $divisionId = $this->_configResource->getDivisionID($storeId) ?? "";

            /** If no customer Id or Security Token empty array will be resulted */
            if (!$securityToken) {
                return $ebizOrdersCollection;
            }

            /** @var $maxSize */
            $customerId = $soapParams['customerId'] ?? '';
            $maxSize = $soapParams['maxSize'] ?? SoapApiModelInterface::EBIZCHARGE_DEFAULT_MAX_SIZE;
            $start = $soapParams['position'] ?? SoapApiModelInterface::EBIZCHARGE_DEFAULT_POSITION;
            $start = 0;
            $limit = $soapParams['limit'] ?? SoapApiModelInterface::EBIZCHARGE_DEFAULT_REQUEST_MAX_LIMIT;
            $limit = $limit > 500 ? 500 : $limit;

            /** temp */
            // $soapParams['salesOrderInternalId'] = "5d51d601-800f-4182-8bce-081b8e53c335";

            /** Do Loop for MAX requests
             *  to fetch All orders of
             *  a customer from EBizCharge Gateway
             */
            do {

                /**
                 * SOAP
                 * Search Criteria request to Gateway
                 */
                $searchOrdersParams = [
                    'securityToken' => $securityToken,
                    'customerId' => $customerId,
                    'salesOrderNumber' => $soapParams['salesOrderNumber'] ?? '',
                    'salesOrderInternalId' => $soapParams['salesOrderInternalId'] ?? '',
                    'start' => $start,
                    'limit' => $limit,
                    'sort' => "",
                    'includeItems' => SoapApiModelInterface::EBIZCHARGE_DEFAULT_INCLUDE_ITEMS,
                    'filters' => [
                        'SearchFilter' =>
                            [
                                'FieldName' => 'DivisionId',
                                'ComparisonOperator' => 'equal',
                                'FieldValue' => $envPrefix
                            ]
                    ]
                ];

                // var_dump($searchOrdersParams);
                //  exit;

                if (!$this->_soapApiModel->getClient($storeId)) {
                    $this->_ebizchargeLogger->addCritical(__(
                        "No Soap client found, kindly check your SOAP URL."
                    ));
                    continue;
                }
                // echo "\n" . "start=" . $start, "maxsize=" . $maxSize, "limit=" . $limit;
                $this->_ebizchargeLogger->addInfo(__("Fetching orders from (" . $start . "-" .
                    ((int)$start + (int)$limit) . ")"));

                /** @var  $searchOrdersResponse */
                $searchOrdersResponse = $this->_soapApiModel->getClient($storeId)
                    ->SearchSalesOrders($searchOrdersParams)
                ;

                /** if search Orders at Ebizcharge API Gateway */
                if (!isset($searchOrdersResponse->SearchSalesOrdersResult)) {
                    $ebizOrdersCollection = [];
                    $resultCount = 0;

                } elseif ((!is_object($searchOrdersResponse->SearchSalesOrdersResult->SalesOrder)) &&
                    (count((array)$searchOrdersResponse->SearchSalesOrdersResult->SalesOrder)) > 1) {

                    $ebizOrder = $searchOrdersResponse->SearchSalesOrdersResult->SalesOrder;
                    $resultCount = count($searchOrdersResponse->SearchSalesOrdersResult->SalesOrder);
                    $ebizOrdersCollection = array_merge($ebizOrdersCollection, $ebizOrder);

                } else {
                    /** @var $ordersObj */
                    $ebizOrder[] = $searchOrdersResponse->SearchSalesOrdersResult->SalesOrder;
                    $ebizOrdersCollection = array_merge($ebizOrdersCollection, $ebizOrder);
                    $resultCount = 1;
                }

                /** result count */
                if ($resultCount < $limit) {
                    $maxSize = 1;
                }
                $start = $start + $limit;
                //  echo "\n"."start=" . $start, "maxsize=" . $maxSize, "limit=" . $limit;

            } while ($maxSize === 0);

            /** returning the Array of Orders */
            $this->_ebizchargeLogger->addInfo(__(
                "Total Orders found from EBizCharge Api Gateway Total Remote Orders: " .
                count((array)$ebizOrdersCollection)
            ));

        } catch (SoapFault $soapFault) {
            $this->_ebizchargeLogger->addCritical(__('Exception occurred during fetching Orders Exception: ' .
                $soapFault->getMessage()));
        }

        return $ebizOrdersCollection;
    }

    /**
     * Get Division Id
     *
     * @return string
     */
    public function getDivisionId(): string
    {
        return $this->getData(self::EBIZCHARGE_DIVISION_ID) ?? "";
    }

    /**
     * Delete Order
     *
     * @param Order $order
     * @return mixed
     * @throws \Exception
     */
    public function deleteOrder($order)
    {
        return $order->delete();
    }

    /**
     * Delete Orders
     *
     * @param int $orderId
     * @throws \Exception
     */
    public function deleteOrders($orderId = 0)
    {
        if ($orderId) {
            $this->_ebizchargeLogger->addInfo(__("Order " . $orderId . " has been deleted"));
            $this->load($orderId)->delete();
        } else {
            /** @var $ordersCollection */
            $ordersCollection = $this->getCollection()->addFieldToSelect('*');

            if (count($ordersCollection) > 0) {
                foreach ($ordersCollection as $order) {
                    $order->delete();
                    $this->_ebizchargeLogger->addInfo(__("Order " . $order->getId() . " has been deleted"));
                }
            }
        }
    }

    /**
     * Get Email Template Id
     *
     * @return int
     */
    public function getEmailTemplateId()
    {
        return 1;
    }

    /**
     * Save Order To Magento
     *
     * @param Order $order
     * @return array
     * @throws \Exception
     */
    public function saveOrderToMagento($order = null)
    {

        /** @var  $orderResponse */
        $orderResponse = [
            'error' => true,
            'status' => false,
            'message' => __("Error occurred during order creation Locally."),
            'response' => [
                'increment_id' => "",
                'order_id' => 0
            ]
        ];
        /** @var $orderData */
        $orderData = $this->prepareOrderData($order);

        $ebizSalesOrderNumber = isset($orderData['salesordernumber']) ? $orderData['salesordernumber'] : "";
        $orderNumber = $this->prepareAttributesValue($ebizSalesOrderNumber);
        $orderResponse['response']['increment_id'] = $orderNumber;

        /** @var  $orderedItems */
        $orderedItems = isset($orderData['items']) ? $orderData['items'] : [];

        if (count($orderedItems) === 0) {
            $this->_ebizchargeLogger->addInfo(__('This order ' . $orderNumber .
                ' do not have any items.'));
            $orderResponse['message'] = __('This order ' . $orderNumber .
                ' do not have any items.');
            return $orderResponse;
        }
        /** @var  $orderResponse */
        return $this->createOrder($orderData);
    }

    /**
     * Prepare Order data
     *
     * @param mixed $orderParams
     * @return array
     * @phpcs:disable
     */
    public function prepareOrderData($orderParams = null): array
    {
        /** @var $ebizchargeOrderData */
        $ebizchargeOrderData = [];
        $orderCustomFields = [];
        $ebizchargeOrderData['cc_trans_id'] = "";
        $ebizchargeOrderData['additional_info'] = "";
        $ebizchargeOrderData['payment_application_id'] = "";
        $ebizCustomFields = [];

        /** if is object order params */
        if (is_object($orderParams)) {
            foreach ($orderParams as $orderParamKey => $orderParamVal) {
                $paramKey = $this->cleanParam($orderParamKey);
                $orderParamKey = strtolower($orderParamKey);

                /** $order params  */
                if (is_string($orderParamVal)) {
                    $ebizchargeOrderData[$orderParamKey] = $this->cleanParam($orderParamVal);
                    if ($orderParamKey === 'date' || $orderParamKey === 'duedate') {
                        $ebizchargeOrderData['dateuploaded'] = $this->cleanParam($orderParamVal);
                    }
                } else {
                    if (is_object($orderParamVal)) {
                        /** assigning Order items */
                        if (property_exists($orderParamVal, 'Item')) {
                            $orderItems = (array)$orderParamVal->Item;

                            /** order items are more than single item */
                            if (isset($orderItems['ItemId'])) {
                                $ebizchargeOrderData['items'][] = $this->assignOrderData($orderItems);
                            } else {
                                $orderedItems = $this->assignOrderData($orderItems);
                                foreach ($orderedItems as $orderedItem) {
                                    $orderedItem = (array)$orderedItem;
                                    $ebizchargeOrderData['items'][] = $this->assignOrderData($orderedItem);
                                }
                            }
                        }
                        /** assigning Order Billing Address Fields */
                        if ($paramKey === 'BillingAddress') {
                            $billingAddressFields = (array)$orderParamVal;
                            $ebizchargeOrderData['BillingAddress'] = $this->assignOrderData($billingAddressFields);
                        }
                        /** assigning Order Shippig Fields */
                        if ($paramKey === 'ShippingAddress') {
                            $shippingAddressFields = (array)$orderParamVal;
                            $ebizchargeOrderData['ShippingAddress'] = $this->assignOrderData($shippingAddressFields);
                        }

                        /** assigning Order Custom Fields */
                        /*
                        if (property_exists($orderParamVal, 'SalesOrderCustomFields')) {
                            $customFields = (array)$orderParamVal->SalesOrderCustomFields;
                            $ebizchargeOrderData['custom_fields'] = $this->assignOrderData($customFields);
                        }
                        */
                        /** assigning location Id */
                        if (property_exists($orderParamVal, 'LocationId')) {
                            $locationIds = (array)$orderParamVal->SalesOrderCustomFields;
                            $ebizchargeOrderData['locations'] = $this->assignOrderData($locationIds);
                        }

                    } else {
                        /** assigning Ebizcharge Extra Order Params */
                        $ebizchargeOrderData[$orderParamKey] = $this->cleanParam($orderParamVal);
                    }
                }
                /** @var $customerId */
                $ebizCustomerId = $orderParams->CustomerId;
                /** @var  $customer */
                $localCustomer = $this->_customerFactory->create()->loadByEbizCustomerId($ebizCustomerId);

                /** if Customer */
                if ($localCustomer->getEmail()) {
                    $ebizchargeOrderData['customer'] = $localCustomer->getData();
                }
                if ($orderParamKey === "salesordercustomfields") {
                    $orderParamVal = (array)$orderParamVal;
                    $paymentCustomFields = isset($orderParamVal["EbizCustomField"]) ? $orderParamVal["EbizCustomField"] : [];

                    if (is_object($paymentCustomFields)) {
                        $ebizCustomFields[] = (array)$paymentCustomFields;
                    } else {
                        $ebizCustomFields = $paymentCustomFields;
                    }

                    if (count($ebizCustomFields) > 0) {
                        foreach ($ebizCustomFields as $customFields) {
                            $customFields = (array)$customFields;
                            $orderCustomFields[] = $customFields;
                            if (count($orderCustomFields) > 0) {
                                foreach ($orderCustomFields as $key => $field) {
                                    $key = isset($field["FieldId"]) ? $field["FieldId"] : "";
                                    $fieldValue = isset($field["FieldValue"]) ? $field["FieldValue"] : "";
                                    if ($key === "TransactionID") {
                                        $ebizchargeOrderData['cc_trans_id'] = $fieldValue;
                                    }
                                    if ($key === "PaymentAdditionalInfo") {
                                        $ebizchargeOrderData['additional_info'] = $fieldValue;
                                    }
                                    if ($key === "PaymentApplicationID") {
                                        $ebizchargeOrderData['payment_application_id'] = $fieldValue;
                                    }
                                }

                            }
                        }
                    }
                }
            }
            $ebizchargeOrderData["custom_fields"] = $orderCustomFields;
        }

        return $ebizchargeOrderData;
    }

    /**
     * Clean Param
     *
     * @param mixed $param
     * @return array|mixed|string|string[]|null
     */
    public function cleanParam($param = null)
    {
        if (!$param) {
            return $param;
        }
        $param = trim((string)$param);
        $param = str_replace(" ", "", $param);
        $param = str_replace("N/A", "", $param);
        return $param;
    }

    /**
     * Assign Order Data
     *
     * @param mixed $items
     * @return array
     */
    public function assignOrderData($items = null): array
    {
        $itemData = [];
        if (is_array($items)) {
            foreach ($items as $key => $val) {
                $key = $this->cleanParam(strtolower((string)$key));
                if ($key == 'itemid') {
                    $itemData[$key] = $this->cleanParam(preg_replace('/\s+/', '', $val));
                }
                if (is_object($val) || is_array($val)) {
                    $itemData[$key] = (array)$val;
                } else {
                    $itemData[$key] = $this->cleanParam($val);
                }

            }
        }
        return $itemData;
    }

    /**
     * Prepare Attributes Values
     *
     * @param mixed $attributeParam
     * @param string $defaultValue
     * @return string
     */
    public function prepareAttributesValue($attributeParam, $defaultValue = '')
    {
        /** @var $defaultValue */
        $defaultValue = $defaultValue != '' ? $defaultValue : '';
        $attributeParam = trim($attributeParam);
        // $attributeParam = str_replace(' ', '', $attributeParam);
        return !empty($attributeParam) ? $attributeParam : $defaultValue;
    }

    /**
     * Is Order Already Exists
     *
     * @param array $orderParams
     * @return bool
     */
    public function isOrderAlreadyExists($orderParams = []): bool
    {
        /** @var $ebizOrderInternalId */
        $ebizOrderInternalId = $this->prepareAttributesValue($orderParams['salesorderinternalid']);
        $order = $this->loadOrderByEbizInternalId($ebizOrderInternalId);
        if ($order->getEntityId()) {
            return true;
        }
        return false;
    }

    /**
     * Load Order By Ebiz Internal Id
     *
     * @param string $ebizchargeInternalId
     * @return Order
     */
    public function loadOrderByEbizInternalId($ebizchargeInternalId = '')
    {
        /** @var $orderId */
        $orderId = $this->getResource()->getOrderIdByEbizInternalId($ebizchargeInternalId);
        /** @var $order */
        $order = $this->loadByAttribute('entity_id', $orderId);
        return $order;
    }

    /**
     * Load Order By Ebiz Id
     *
     * @param string $ebizSalesOrder
     * @return Order
     */
    public function loadOrderByEbizId($ebizSalesOrder = '')
    {
        /** @var $orderId */
        $orderId = $this->getResource()->getOrderIdByEbizOrderId($ebizSalesOrder);
        /** @var $order */
        $order = $this->loadByAttribute('entity_id', $orderId);
        return $order;
    }

    /**
     * Save Ec Extra Attributes
     *
     * @param mixed $orderId
     * @param array $ecOrderParams
     * @return bool
     * @throws \Exception
     */
    public function saveEcExtraAttributes($orderId, array $ecOrderParams = []): bool
    {
        if (!$orderId) {
            return false;
        }

        try {
            /** @var $order */
            $order = $this->loadByAttribute('entity_id', $orderId);
            /** if order is given */
            if ($order->getId()) {
                /**
                 *
                 * Prepare Attributes
                 */
                $storeId = $this->_configResource->getStoreId();
                $softwareId = $this->_soapApiModel->getSoftwareId();
                $divisionId = $this->_configResource->getDivisionID($storeId) ?? "";

                $ecOrderInternalId = $this->prepareAttributesValue(
                    $ecOrderParams['salesorderinternalid'] ?? ''
                );
                $ecOrderId = $this->prepareAttributesValue($ecOrderParams['salesordernumber'] ?? '');
                $ecCustomerId = $this->prepareAttributesValue($ecOrderParams['customerid'] ?? '');
                $ecOrderPoNumber = $this->prepareAttributesValue($ecOrderParams['ponum'] ?? '');
                $ecSoftwareId = $this->prepareAttributesValue($ecOrderParams['software'] ?? $softwareId);
                $ecOrderDivisionId = $this->prepareAttributesValue(
                    $ecOrderParams['divisionid'] ?? $divisionId
                );
                $ecOrderDateUploaded = $this->prepareAttributesValue(
                    $ecOrderParams['dateuploaded'] ?? ''
                );
                $ecOrderDueDate = $this->prepareAttributesValue($ecOrderParams['duedate'] ?? '');
                $ecOrderCreatedIn = $this->prepareAttributesValue($ecOrderParams['software'] ?? '');

                /**
                 * Saving order params
                 */
                $order->setEcOrderInternalId($ecOrderInternalId);
                $order->setEcCustId($ecCustomerId);
                $order->setEcOrderId($ecOrderId);
                $order->setEcOrderPoNumber($ecOrderPoNumber);
                $order->setEcDivisionId($ecOrderDivisionId);
                $order->setEcDueDate($ecOrderDueDate);
                $order->setEcDateUploaded($ecOrderDateUploaded);
                $order->setEcOrderCreatedIn($ecOrderCreatedIn);
                $order->setSoftwareId($ecSoftwareId);
                $order->setDivisionId($ecOrderDivisionId);
                $order->setEcOrderLastSyncDate($ecOrderDateUploaded);
                $order->setEcOrderSyncStatus(1);

                $this->_ebizchargeLogger->addInfo(__("Saving Ebizcharge extra Attributes"));
                /** saving order extra attributes */
                $order->save();
                $this->_ebizchargeLogger->addInfo(__("Saved Ebizcharge extra Attributes"));

                return true;
            }
            return false;
        } catch (Exception $exception) {
            $this->_ebizchargeLogger->addCritical(__("Exception occurred during saving order Error:" .
                $exception->getMessage()));
            return false;
        }
    }

    /**
     * Get Software Id
     *
     * @return string
     */
    public function getSoftwareId(): string
    {
        return (string)$this->getData(self::EBIZCHARGE_SOFTWARE_ID);
    }

    /**
     * Set Ec Customer Id
     *
     * @param mixed $ecCustId
     * @return Order
     */
    public function setEcCustId($ecCustId)
    {
        return $this->setData(OrderInterface::EC_CUST_ID, $ecCustId);
    }

    /**
     * Set Software Id
     *
     * @param mixed $ecSoftwareId
     * @return OrderInterface
     */
    public function setSoftwareId($ecSoftwareId): OrderInterface
    {
        return $this->setData(self::EBIZCHARGE_SOFTWARE_ID, $ecSoftwareId);
    }

    /**
     * Set Ec Order Last Sync Date
     *
     * @param mixed $ecOrderLastSyncDate
     * @return Order
     */
    public function setEcOrderLastSyncDate($ecOrderLastSyncDate)
    {
        return $this->setData(OrderInterface::EC_ORDER_LASTSYNCDATE, $ecOrderLastSyncDate);
    }

    /**
     * Set Ec order Sync Status
     *
     * @param mixed $ecOrderSyncStatus
     * @return Order
     */
    public function setEcOrderSyncStatus($ecOrderSyncStatus)
    {
        return $this->setData(OrderInterface::EC_ORDER_SYNC_STATUS, $ecOrderSyncStatus);
    }

    /**
     * Get Latest Local Orders
     * @return Collection
     * @throws NoSuchEntityException
     */
    public function getLatestLocalOrders()
    {

        $storeId = $this->_configResource->getStoreId();
        //$envPrefix = $this->_configResource->getEnvoirnmentPrefix($storeId);
        $ordersCollection = $this->_orderCollection
            ->addFieldToSelect('*')
            ->addFieldToFilter(OrderInterfaceAlias::CUSTOMER_ID, ['neq' => '']);
        //$ordersCollection->getSelect()->where(OrderInterface::EBIZCHARGE_DIVISION_ID . ' like(\'' .
        //    $envPrefix . '%\') ');
        $ordersCollection->getSelect()
            ->Where(OrderInterfaceAlias::CUSTOMER_ID . ' IS NOT NULL');
        //   $ordersCollection->getSelect()->Where(OrderInterface::EC_ORDER_SYNC_STATUS . ' != "1"');
        $conditions = OrderInterface::EC_ORDER_INTERNALID . ' IS NULL  OR ';
        $conditions .= OrderInterface::EC_ORDER_INTERNALID . '=""   OR ';
        $conditions .= OrderInterface::EC_ORDER_ID . ' IS NULL   OR ';
        $conditions .= OrderInterface::EC_ORDER_ID . '=""   OR ';
        $conditions .= OrderInterface::EBIZCHARGE_DIVISION_ID . ' IS NULL   OR ';
        $conditions .= OrderInterface::EBIZCHARGE_DIVISION_ID . '=""  ';

        $ordersCollection->getSelect()->where($conditions);
        //  dump($ordersCollection->getSelect()->__toString(), count($ordersCollection)); exit;
        return $ordersCollection;
    }

    /**
     * Is order Exists At EBizCharge
     *
     * @param mixed $orderEbizchargeInternalId
     * @param mixed $ebizchargeOrders
     * @return bool
     */
    public function isOrderExistsAtEbizcharge(mixed $orderEbizchargeInternalId, mixed $ebizchargeOrders): bool
    {
        $isEbizchargeOrderExists = false;

        /** EBizCharge Orders */
        if ($ebizchargeOrders && count($ebizchargeOrders)) {
            foreach ($ebizchargeOrders as $ebizchargeOrder) {
                if ($orderEbizchargeInternalId === $ebizchargeOrder->SalesOrderInternalId) {
                    $isEbizchargeOrderExists = true;
                }
            }
        }
        return $isEbizchargeOrderExists;
    }

    /**
     * @param $localOrder
     * @return array
     * @throws NoSuchEntityException
     */
    public function exportOrderToEbizcharge($localOrder)
    {
        return $this->saveOrderToEbizcharge($localOrder);
    }

    /**
     * Save Order to EBizCharge
     *
     * @param Order|null $order
     * @return array
     * @throws NoSuchEntityException
     */
    public function saveOrderToEbizcharge($order = null): array
    {

        /** @var  $salesOrderParams */
        $salesOrderParamsResp = [
            'status' => 'error',
            'message' => __("Error occurred could not sync order to EBizCharge Hub."),
            'error' => true,
            OrderInterface::EC_ORDER_INTERNALID => '',
            OrderInterface::EC_ORDER_ID => $order->getIncrementId() ?? "",
            OrderInterface::EC_ORDER_PO_NUMBER => $order->getIncrementId() ?? "",
            OrderInterface::EBIZCHARGE_DIVISION_ID => $this->_soapApiModel->getDivisionId(),
            OrderInterface::EBIZCHARGE_SOFTWARE_ID => $this->_soapApiModel->getSoftwareId()
        ];

        try {

            /** @var  $order * */
            // $order = $this->load($order->getId());

            if (!$order || !$order->getIncrementId()) {
                $salesOrderParamsResp['message'] = __("Error occurred as this Order does not exists.");
                $this->_ebizchargeLogger->addInfo(__('Error: This Order do not exists'));
                return $salesOrderParamsResp;
            }

            $orderNumber = $order->getIncrementId() ?? "";
            $orderPayment = $order->getPayment() ?? null;

            if ($orderPayment) {
                $ebizOption = $orderPayment->getEbzcOption();
            }

            if ($ebizOption === PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_DOWNLOAD_ORDER) {
                $orderNumber = $orderPayment->getEbzcParentOrderId();
            }

            /** if order already exists **/
            /***
             * if ($order->getEcOrderInternalId() && $order->getEcOrderId()) {
             * $this->_ebizchargeLogger->addInfo(__('Order ' . $order->getIncrementId() .
             * ' is already existed at Ebizcharge'));
             * return $salesOrderParamsResp;
             * }
             **/

            /** @var $store * */
            $store = $this->_storeManager->getStore();
            $syncDate = $this->getCurrentDateTime();
            $storeId = $order->getStoreId() ? $order->getStoreId() : $store->getId();
            $divisionId = $this->_soapApiModel->getDivisionId();
            $softwareId = $this->_soapApiModel->getSoftwareId();

            /** @var  $orderItems * */
            $orderItems = $order->getAllItems();
            $taxAmount = $order->getTaxAmount();

            /** @var $shippingDescription * */
            $shippingDescription = $order->getShippingDescription();
            $storeName = $this->_storeManager->getStore()->getName();
            $shippingMethod = $order->getShippingMethod();
            $customerId = $order->getCustomerId();
            $ebizCustomerId = $customerId ?? $order->getQuoteId();

            if ($customerId) {
                /** @var $ebizCustomerInternalId * */
                $customerEmail = $order->getCustomerEmail() ?? $order->getQuote()->getCustomerEmail();

                /** Get Customer Email and get EBizCharge Customer Id **/
                if (!$customerEmail) {
                    // throw new LocalizedException(__('Customer Email doest not exist in this order'));
                    $this->_ebizchargeLogger->addCritical(__('Customer Email doest not exist in this order'));
                }
                if ($customerEmail) {
                    /** @var  $customer * */
                    $customer = $this->_customerFactory->create()->load($customerId);
                    /** @var  $ebizCustomerId * */
                    $ebizCustomerInternalId = $customer->getEcCustInternalId() ?? "";
                    if(!$customer->getId()){
                        $ebizCutomerId = $order->getQuoteId();
                        $ebizCustomer = $this->_customerFactory->create()->getEbizCustomerById($ebizCutomerId);
                        $ebizCustomerInternalId = $customer->CustomerInternalId ?? "";
                    }
                    if (!$ebizCustomerInternalId) {
                        /** @var $ebizCustomer * */
                        $ebizCustomer = $this->_customerFactory->create()->saveLocalCustomerToEbizcharge($customer);
                        /** EBizCharge Customer  **/
                        if (isset($ebizCustomer['status']) && $ebizCustomer['status'] === 'Success') {
                            $ebizCustomerInternalId = $ebizCustomer[CustomerInterface::EBIZCHARGE_CUSTOMER_INTERNAL_ID]
                                ?? '';
                        }
                    }
                    $ebizCustomerId = $customer->getEcCustId() ?? $ebizCutomerId;
                }
            }

            /** @var  $transactionData * */
            $orderId = $order->getId();
            $soapClient = $this->_soapApiModel->getClient($storeId);
            $orderItemParams = $this->addOrderItemToEbizcharge($orderItems);

            $billingAddressParams = $this->prepareBillingAddress($order);
            $shippingAddressParams = $this->prepareShippingAddress($order);

            $customFields = $this->prepareOrderCustomFields($orderId);
            $createdAt = $order->getCreatedAt() ?? $this->getCurrentDateTime();
            $transactionID = $orderPayment->getLastTransId() ?? "";
            $ebizPaymentCommand = $orderPayment->getData("ebzc_payment_command");
            // phpcs:ignore
            $paymentAdditionalInfo = serialize($orderPayment->getAdditionalInformation() ?? []);
            $templateId = $this->_configResource->getCustreceiptTemplate($storeId) ?? "";
            $paymentAapplicationID = $orderPayment->getData("ebzc_application_payment_ref_id") ?? "";

            $transactionCustomParams = [
                "TransactionID" => [
                    "FieldId" => "TransactionID",
                    "FieldCaption" => "Payment Transaction ID",
                    "FieldName" => "TransactionID",
                    "FieldValue" => $transactionID,
                    "FieldType" => "text",
                    "FieldDataType" => "varchar",
                    "FieldDescription" => "Payment Transaction ID"
                ],
                "EBizCustomerID" => [
                    "FieldId" => "EBizCustomerID",
                    "FieldCaption" => "EBiz Customer ID",
                    "FieldName" => "EBizCustomerID",
                    "FieldValue" => $ebizCustomerId,
                    "FieldType" => "text",
                    "FieldDataType" => "varchar",
                    "FieldDescription" => "EBiz Customer ID"
                ],
                "EBizPaymentCommand" => [
                    "FieldId" => "EBizPaymentCommand",
                    "FieldCaption" => "EBiz Payment Command",
                    "FieldName" => "EBizPaymentCommand",
                    "FieldValue" => $ebizPaymentCommand,
                    "FieldType" => "text",
                    "FieldDataType" => "varchar",
                    "FieldDescription" => "EBiz Payment Command"
                ],
                "PaymentAdditionalInfo" => [
                    "FieldId" => "PaymentAdditionalInfo",
                    "FieldCaption" => "Payment Additional Info",
                    "FieldName" => "PaymentAdditionalInfo",
                    "FieldValue" => $paymentAdditionalInfo,
                    "FieldType" => "text",
                    "FieldDataType" => "varchar",
                    "FieldDescription" => "Payment Additional Info"
                ],
                "PaymentApplicationID" => [
                    "FieldId" => "PaymentApplicationID",
                    "FieldCaption" => "Payment Application ID",
                    "FieldName" => "PaymentApplicationID",
                    "FieldValue" => $paymentAapplicationID,
                    "FieldType" => "text",
                    "FieldDataType" => "varchar",
                    "FieldDescription" => "Payment Application ID"
                ]
            ];
            /** @var  $transactionCustomFields * */
            $transactionCustomFields = $this->prepareCustomFields($transactionCustomParams);

            /** @var  $ebizOrderParams * */
            $ebizOrderParams = [
                'CustomerId' => $ebizCustomerId,
                'SubCustomerId' => "",
                'SalesOrderNumber' => $orderNumber,
                'Currency' => $order->getBaseCurrencyCode(),
                'Date' => $createdAt,
                'Amount' => (float)$order->getGrandTotal(),
                'DueDate' => $order->getUpdatedAt(),
                'AmountDue' => (float)$order->getTotalDue(),
                'PoNum' => $orderNumber,
                'Items' => $orderItemParams,
                'Software' => $softwareId,
                'NotifyCustomer' => $order->getEmailSent() ?? "",
                'DivisionId' => $divisionId,
                'EmailTemplateID' => $templateId,
                'URL' => $this->getWebsiteUrl(),
                'TotalTaxAmount' => $taxAmount,
                'UniqueId' => $orderNumber,
                'Description' => $shippingDescription,
                'BillingAddress' => $billingAddressParams,
                'ShippingAddress' => $shippingAddressParams,
                'CustomerMessage' => $storeName ?? "",
                'Memo' => $orderNumber,
                'ShipDate' => $createdAt,
                'ShipVia' => $shippingMethod ?? "",
                'SalesRepId' => "",
                'TermsId' => '',
                'IsToBeEmailed' => $order->getEmailSent(),
                'IsToBePrinted' => 0,
                'SalesOrderCustomFields' => $transactionCustomFields
            ];

            /**
             * EBizCharge Order Payload Params
             **/
            $ebizChargeOrderParams = [
                "securityToken" => $this->_soapApiModel->getUeSecurityToken($storeId),
                "salesOrder" => $ebizOrderParams,
                "salesOrderNumber" => $order->getIncrementId()
            ];

            if ((!$order->getEcOrderId() || !$order->getEcOrderInternalId()) && $soapClient) {
                /** @var  $orderResponse * */
                $orderResponse = $soapClient->addSalesOrder($ebizChargeOrderParams);
                /** @var  $orderResponse * */
                $ebizOrderResponse = (array)$orderResponse->AddSalesOrderResult;

                if (isset($ebizOrderResponse["Status"]) && $ebizOrderResponse["Status"] === "Success") {
                    $ebizSalesInternalId = $ebizOrderResponse["SalesOrderInternalId"] ?? "";
                    $ebizChargeOrderParams["salesOrderInternalId"] = $ebizSalesInternalId;
                }
            }

            /** @var  $orderResponse * */
            $orderResponse = $soapClient->UpdateSalesOrder($ebizChargeOrderParams);
            $ebizOrderResponse = (array)$orderResponse->UpdateSalesOrderResult;

            $ebizchargeOrder = $this->getEbizChargeOrderByOrderId($orderNumber, $ebizCustomerId);
            $ebizDivisionId = !empty(trim($ebizchargeOrder['DivisionId'])) ? trim($ebizchargeOrder['DivisionId']) : $divisionId;
            $ebizSoftwareId = !empty(trim($ebizchargeOrder['Software'])) ? trim($ebizchargeOrder['Software']) : $softwareId;


            if (count($ebizchargeOrder) > 0) {
                /** @var $bind */
                $bind = [
                    OrderInterface::EC_ORDER_SYNC_STATUS => 1,
                    OrderInterface::EC_ORDER_INTERNALID => $ebizchargeOrder['SalesOrderInternalId'] ?? "",
                    OrderInterface::EC_ORDER_ID => $ebizchargeOrder['SalesOrderNumber'] ?? "",
                    OrderInterface::EBIZCHARGE_SOFTWARE_ID => $ebizSoftwareId,
                    OrderInterface::EC_CUST_ID => $ebizchargeOrder['CustomerId'] ?? "",
                    OrderInterface::EBIZCHARGE_DIVISION_ID => $ebizDivisionId,
                    OrderInterface::EC_ORDER_CREATED_IN => $ebizSoftwareId,
                    OrderInterface::EC_ORDER_PO_NUMBER => $ebizchargeOrder['PoNum'] ?? "",
                    OrderInterface::EC_ORDER_DUE_DATE => date('Y-m-d H:i:s'),
                    OrderInterface::EC_ORDER_DATE_UPLOADED => date('Y-m-d H:i:s'),
                    OrderInterface::EC_ORDER_LASTSYNCDATE => date('Y-m-d H:i:s')
                ];
                /** @var $where */
                $connection = $this->getResource()->getConnection();
                $whereClause = $connection->quoteInto('entity_id' . " = ?", $orderId);

                /** updating the extra fields via Customer Resource Model */
                $connection->update($this->getResource()->getMainTable(), $bind, $whereClause);

                $salesOrderParamsResp = [
                    'status' => 'success',
                    'error' => false,
                    'message' => __("Success, the order # " . $orderNumber . " has been synced to EBizCharge Hub. "),
                    OrderInterface::EC_ORDER_SYNC_STATUS => 1,
                    OrderInterface::EC_ORDER_INTERNALID => $ebizchargeOrder['SalesOrderInternalId'] ?? "",
                    OrderInterface::EC_CUST_ID => $ebizchargeOrder['CustomerId'] ?? "",
                    OrderInterface::EC_ORDER_ID => $ebizchargeOrder['SalesOrderNumber'] ?? "",
                    OrderInterface::EC_ORDER_PO_NUMBER => $ebizOrderParams['PoNum'] ?? "",
                    OrderInterface::EBIZCHARGE_DIVISION_ID => $ebizDivisionId,
                    OrderInterface::EBIZCHARGE_SOFTWARE_ID => $ebizSoftwareId,
                    OrderInterface::EC_ORDER_DUE_DATE => date('Y-m-d H:i:s'),
                    OrderInterface::EC_ORDER_DATE_UPLOADED => date('Y-m-d H:i:s'),
                    OrderInterface::EC_ORDER_LASTSYNCDATE => date('Y-m-d H:i:s')
                ];
            }

        } catch (\Exception $exception) {
            $this->_ebizchargeLogger->addCritical(__(
                "Exception occurred during syncing order to EBizCharge Hub. :" . $exception->getMessage()
            ));
            $salesOrderParamsResp["message"] = __("Exception occurred during syncing order error:" . $exception->getMessage());
        }
        return $salesOrderParamsResp;
    }

    /**
     * Add Order Item to EBizCharge
     *
     * @param array $orderItems
     * @return array
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function addOrderItemToEbizcharge($orderItems): array
    {
        /** @var $orderedItemsCollection */
        $orderedItemsCollection = [];
        $storeId = $this->getStore()->getId();

        try {

            /** Ordered Item */
            if ($orderItems && count($orderItems) > 0) {

                /** @var $orderItem */
                foreach ($orderItems as $orderItem) {

                    $itemId = $orderItem->getItemId() ?? $orderItem->getOrderItemId();

                    if (empty($itemId)) {
                        continue;
                    }
                    /** @var  $orderItem */
                    $orderItem = $this->_orderItemRepository->get($itemId);


                    /** @var $product */
                    $sku = $orderItem->getSku();
                    $productId = $orderItem->getProductId();
                    $product = $this->_productFactory->create()->load($productId);

                    /** adding item to EBizCharge */
                    $ebizInternalId = $product->getEcItemInternalid();

                    /** if product does not exist at EBizCharge Hub */
                    if (!$ebizInternalId) {
                        /** adding items to EBizCharge Gateway */
                        //  $ebizProduct = $this->_productFactory->create()->uploadItemToEbizcharge($product);
                    }
                    /** @var $isTaxable */
                    $isTaxable = $orderItem->getTaxAmount() > 0 ? true : false;
                    /** @var $unitOfMeasure */
                    $unitOfMeasure = $this->_configResource->getUnitOfMeasure($storeId);

                    $customFields = [
                        'FieldId' => 'sku',
                        'FieldCaption' => 'sku',
                        'FieldName' => 'sku',
                        'FieldValue' => $orderItem->getSku(),
                        'FieldType' => 'text',
                        'FieldDataType' => 'text',
                        'FieldDescription' => 'Sku of the Item'
                    ];

                    $productShortDescription = $product->getData("short_description") ?? "";
                    $productDescription = strip_tags((string)$productShortDescription);
                    $productDescriptions = substr((string)$productDescription, 0, 500) ?? "";

                    /** Ordered Item */
                    $item = [
                        'ItemId' => trim((string)$itemId) ?? "",
                        'Name' => trim((string)$orderItem->getName()) ?? "",
                        'Description' => trim((string)$productDescriptions) ?? "",
                        'UnitPrice' => (double)$orderItem->getPrice(),
                        'Qty' => (double)$orderItem->getQtyOrdered() * 1,
                        'Taxable' => $isTaxable,
                        'TaxRate' => (double)$orderItem->getTaxAmount(),
                        'UnitOfMeasure' => $unitOfMeasure,
                        'TotalLineAmount' => (double)$orderItem->getRowTotalInclTax(),
                        'TotalLineTax' => (double)$orderItem->getTaxAmount(),
                        'ItemLineNumber' => $itemId,
                        'GrossPrice' => (double)$orderItem->getPriceInclTax(),
                        'SalesDiscount' => (double)$orderItem->getDiscountAmount(),
                        'WarrantyDiscount' => (double)$orderItem->getBaseDiscountAmount(),
                        'DivisionId' => $this->_soapApiModel->getDivisionId($storeId) ?? "",
                        'ItemCustomFields' => []
                    ];

                    if ($ebizInternalId) {
                        $item['ItemInternalId'] = trim($product->getEcItemInternalId() ?? "");
                    }

                    $orderedItemsCollection[] = $item;

                }
            }
        } catch (Exception $exception) {
            $this->_ebizchargeLogger->addCritical(__("Error during item uploading.  Exception: " . $exception->getMessage()));
        }
        return $orderedItemsCollection;
    }

    /**
     * Get Unit of Measure
     *
     * @param int $storeId
     * @return bool
     */
    public function getUnitOfMeasure($storeId = 0)
    {
        return $this->_configResource->getUnitOfMeasure($storeId);
    }

    /**
     * Prepare Billing Address
     *
     * @param Order $order
     * @return string[]
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function prepareBillingAddress($order)
    {
        $billingAddressId = $order->getBillingAddressId();

        /** @var $billingAddress */
        $billingAddress = $this->_addressRepository->get($billingAddressId);
        $billingStreet = $billingAddress->getStreet();

        $billings = [
            'FirstName' => $billingAddress->getFirstname() ?? OrderInterface::NOT_AVAILABLE,
            'LastName' => $billingAddress->getLastname() ?? OrderInterface::NOT_AVAILABLE,
            'CompanyName' => $billingAddress->getCompany() ?? OrderInterface::NOT_AVAILABLE,
            'Address1' => isset($billingStreet[0]) ? $billingStreet[0] : OrderInterface::NOT_AVAILABLE,
            'Address2' => isset($billingStreet[1]) ? $billingStreet[1] : OrderInterface::NOT_AVAILABLE,
            'Address3' => isset($billingStreet[2]) ? $billingStreet[2] : OrderInterface::NOT_AVAILABLE,
            'City' => $billingAddress->getCity() ?? OrderInterface::NOT_AVAILABLE,
            'State' => $billingAddress->getRegion() ?? OrderInterface::NOT_AVAILABLE,
            'ZipCode' => $billingAddress->getPostcode() ?? OrderInterface::NOT_AVAILABLE,
            'Country' => $billingAddress->getCountryId() ?? OrderInterface::NOT_AVAILABLE,
            'IsDefault' => 1,
            'AddressId' => $billingAddressId
        ];

        return $billings;
    }

    /**
     * Prepare Shipping Address
     *
     * @param Order $order
     * @return array
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function prepareShippingAddress($order)
    {
        /** @var $shippingAddressId */
        $shippingAddressId = $order->getShippingAddressId() ?? $order->getBillingAddressId();

        /** @var $shippingAddress */
        $shippingAddress = $this->_addressRepository->get($shippingAddressId);
        $shippingStreet = $shippingAddress->getStreet();

        /** @var shipping Address Params $shippingAddressParams */
        $shippingAddressParams = [
            'FirstName' => $shippingAddress->getFirstname() ?? OrderInterface::NOT_AVAILABLE,
            'LastName' => $shippingAddress->getLastname() ?? OrderInterface::NOT_AVAILABLE,
            'CompanyName' => $shippingAddress->getCompany() ?? OrderInterface::NOT_AVAILABLE,
            'Address1' => isset($shippingStreet[0]) ? $shippingStreet[0] : OrderInterface::NOT_AVAILABLE,
            'Address2' => isset($shippingStreet[1]) ? $shippingStreet[1] : OrderInterface::NOT_AVAILABLE,
            'Address3' => isset($shippingStreet[2]) ? $shippingStreet[2] : OrderInterface::NOT_AVAILABLE,
            'City' => $shippingAddress->getCity() ?? OrderInterface::NOT_AVAILABLE,
            'State' => $shippingAddress->getRegion() ?? OrderInterface::NOT_AVAILABLE,
            'ZipCode' => $shippingAddress->getPostcode(),
            'Country' => $shippingAddress->getCountryId(),
            'IsDefault' => 1,
            'AddressId' => $shippingAddressId,
        ];

        return $shippingAddressParams;
    }

    /**
     * Prepare Order custom Fields
     *
     * @param string|null $orderId
     * @return array
     */
    public function prepareOrderCustomFields(string $orderId = null): array
    {
        /** @var  $paymentCustomFields */
        $paymentCustomFields = [];

        /** @var  $order */
        $order = $this->load($orderId);
        if ($order->getId()) {
            $payment = $order->getPayment();
            $paymentCustomFields[] = [
                'FieldId' => 'transaction_id',
                'FieldCaption' => 'Transaction Id',
                'FieldName' => 'transaction_id',
                'FieldValue' => '',
                'FieldType' => 'text',
                'FieldDataType' => 'text',
                'FieldDescription' => 'Transaction Id'
            ];
        }

        return $paymentCustomFields;
    }

    /**
     * Get Website URL
     *
     * @return string
     */
    public function getWebsiteUrl()
    {
        return $this->getStore()->getBaseUrl();
    }

    /**
     * Get Ec Order Id
     *
     * @return float|mixed|null
     */
    public function getEcOrderId()
    {
        return $this->getData(OrderInterface::EC_ORDER_ID);
    }

    /**
     * @param $ebizOrderId
     * @param $ebizCustomerId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getEbizChargeOrderByOrderId($ebizOrderId = "", $ebizCustomerId = ""): array
    {
        $ebizOrder = [];

        try {
            $storeId = $this->_configResource->getStoreId() ?? "0";
            /** @var  $orderParams */
            $orderParams = [
                'securityToken' => $this->_soapApiModel->getUeSecurityToken(),
                'customerId' => $ebizCustomerId,
                'includeItems' => 1,
                'subCustomerId' => "",
                'salesOrderNumber' => $ebizOrderId,
                'salesOrderInternalId' => ""
            ];

            /** @var  $ebizOrderResp */
            $ebizOrderResp = $this->_soapApiModel->getClient($storeId)->GetSalesOrder($orderParams);

            /** if response */
            if (is_object($ebizOrderResp) && $ebizOrderResp->GetSalesOrderResult) {
                $ebizOrder = (array)$ebizOrderResp->GetSalesOrderResult;
                $this->_ebizchargeLogger->addInfo(__('Success: found Order from EBizCharge : ' . $ebizOrderId));
            }

        } catch (SoapFault $soapFault) {
            $this->_ebizchargeLogger->addCritical(__("Exception occurred during getting Order Exception: " .
                $soapFault->getMessage()));
        }
        return $ebizOrder;
    }

    /**
     * @param $ebizOrderParams
     * @return false
     * @throws NoSuchEntityException
     */
    public function getEbizChargeOrder($ebizOrderParams = [])
    {
        $ebizOrder = false;

        try {
            $storeId = $this->_configResource->getStoreId() ?? "0";
            /** @var  $orderParams */
            $orderParams = [
                'securityToken' => $this->_soapApiModel->getUeSecurityToken(),
                'customerId' => isset($ebizOrderParams['customerId']) ? $ebizOrderParams['customerId'] : '',
                'includeItems' => isset($ebizOrderParams['includeItems']) ? $ebizOrderParams['includeItems'] : 1,
                'subCustomerId' => isset($ebizOrderParams['subCustomerId']) ? $ebizOrderParams['subCustomerId'] : '',
                'salesOrderNumber' => $ebizOrderParams['salesOrderNumber'] ?? '',
                'salesOrderInternalId' => $ebizOrderParams['salesOrderInternalId'] ?? '',
            ];
            /** @var  $ebizOrderResp */
            $ebizOrderResp = $this->_soapApiModel->getClient($storeId)->GetSalesOrder($orderParams);

            /** if response */
            if (is_object($ebizOrderResp) && $ebizOrderResp->GetSalesOrderResult) {
                $ebizOrder = $ebizOrderResp->GetSalesOrderResult;
                $orderNumber = $ebizOrderResp->GetSalesOrderResult->SalesOrderNumber;

                $this->_ebizchargeLogger->addInfo(__('Success: found Order from EBizCharge : ' . $orderNumber));
            }
            return $ebizOrder;
        } catch (SoapFault $soapFault) {
            $this->_ebizchargeLogger->addCritical(__("Exception occurred during getting Order Exception: " .
                $soapFault->getMessage()));
            // phpcs:ignore
            print_r("Exception occurred during getting Order Exception: " . $soapFault->getMessage());
            return $ebizOrder;
        }
    }

    /**
     * Get Ec Division Id
     *
     * @return float|mixed|null
     */
    public function getEcDivisionId()
    {
        return $this->getData(OrderInterface::EC_ORDER_DIVISION_ID);
    }

    /**
     * Get Current Date
     *
     * @param string $format
     * @return string
     * @throws \Exception
     */
    public function getCurrentDate(string $format = "Y-m-d")
    {
        return $this->_soapApiModel->getCurrentDateTime($format);
    }

    /**
     * @return string
     */
    public function getProposedInvoiceIncrementId()
    {
        $invoiceCollectionFactory = $this->_invoiceCollectionFactory->create();
        $lastInvoiceItem = $invoiceCollectionFactory->getLastItem();
        $lastInvoiceIncrementId = $lastInvoiceItem->getIncrementId() ?? "";
        $isNumericInvoice = is_numeric($lastInvoiceIncrementId);

        if (preg_match('/(\d+)$/', $lastInvoiceIncrementId, $matches)) {
            // Increment the last digits by 1
            $incrementedValue = (int)$matches[0] + 1;
            $incrementedValue = (string)$incrementedValue;
            $invRepLen = strlen($incrementedValue);
            $invIncrementPartA = substr($lastInvoiceIncrementId, 0, strlen($lastInvoiceIncrementId) - $invRepLen);
            $lastInvoiceIncrementId = $invIncrementPartA . $incrementedValue;
        }
        return $lastInvoiceIncrementId;
    }

    /**
     * Get Order Customer Id
     *
     * @param Customer $customer
     * @param mixed $customerInternalId
     * @return float|int|string|null
     */
    public function getOrderCustomerId($customer, $customerInternalId = null)
    {
        /** @var  $ebizCustomerId */
        $ebizCustomerId = $this->getCustomerIsGuest();
        /** @var  $ebizCustomer */
        $ebizCustomer = $customer->getEbizCustomerByInternalId($customerInternalId);
        if (is_object($ebizCustomer)) {
            return $ebizCustomerId;
        }

        return '';
    }

    /**
     * Get Sub Customer ID
     *
     * @param Order|null $order
     * @return string
     */
    public function getSubCustomerId(Order $order = null)
    {
        $customer = $order->getCustomerId();
        return '';
    }

    /**
     * Get Order By Internal From Ebizcharge
     *
     * @param string $ebizInternalOrderId
     * @return bool
     */
    public function getOrderByInternalIdFromEbizcharge($ebizInternalOrderId = '')
    {
        try {
            $ebizCustomerId = '';
            $ebizSalesOrderNumber = '';
            $ebizSalesOrderInternalId = trim($ebizInternalOrderId);
            $storeId = $this->_configResource->getStoreId() ?? "0";

            if (!$ebizSalesOrderInternalId) {
                $this->_ebizchargeLogger->addCritical(__("Please provide valid Customer Id and Sales Order Id"));
                return false;
            }

            $params = [
                'securityToken' => $this->_soapApiModel->getUeSecurityToken(),
                'customerId' => '',
                'salesOrderNumber' => '',
                'salesOrderInternalId' => $ebizSalesOrderInternalId
            ];
            $getSalesOrder = $this->_soapApiModel->getClient($storeId)->GetSalesOrder($params);

            if ($getSalesOrder->GetSalesOrderResult) {
                $this->_ebizchargeLogger->addInfo(__("Success, order found at EEizCharge Gateway"));
                return $getSalesOrder->GetSalesOrderResult;
            }
            return false;
        } catch (Exception $exception) {
            $this->_ebizchargeLogger->addCritical(__(
                "Exception occurred during fetching Order from EEizCharge Gateway Error: " .
                $exception->getMessage()
            ));
            return false;
        }
    }

    /**
     * Get Total Active Orders
     *
     * @param int $active
     * @return mixed
     */
    public function getTotalActiveOrders($active = 1)
    {
        /** @var  $active */
        $active = $active == 1 ?? 0;

        /** @var $totalOrders */
        $totalOrders = $this->_orderCollection->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter(
                'status',
                [
                    'eq' => $active
                ]
            )->count();
        return $totalOrders;
    }

    /**
     * Sync Order to EBizCharge
     *
     * @param null|mixed $localOrderId
     * @return array
     * @throws NoSuchEntityException
     */
    public function syncOrderToEbizcharge($localOrderId = null): array
    {
        /** @var $localOrder */
        $localOrder = $this->load($localOrderId);

        $salesOrderParamsResp = [
            'status' => 'error',
            'message' => __('Error occurred during syncing order to EBizCharge Hub.'),
            'error' => true,
            OrderInterface::EC_ORDER_INTERNALID => '',
            OrderInterface::EC_ORDER_ID => '',
            OrderInterface::EC_ORDER_PO_NUMBER => '',
            OrderInterface::EBIZCHARGE_DIVISION_ID => $this->_soapApiModel->getDivisionId(),
            OrderInterface::EBIZCHARGE_SOFTWARE_ID => $this->_soapApiModel->getSoftwareId()
        ];
        $orderNumber = $localOrder->getIncrementId() ?? "";

        if ($localOrder->getIncrementId() && $localOrder->getId()) {
            $orderNumber = $localOrder->getIncrementId() ?? "";
            $ebizOrderId = $localOrder->getEcOrderId() ?? "";
            $ebizOrderInternalId = $localOrder->getEcOrderInternalId() ?? "";
            $ebizPoNumber = $localOrder->getEcPoNumber() ?? "";

            /** saving Order to EBizCharge */
            /** @var  $ebizchargeOrder */
            $ebizchargeOrderParams = $this->saveOrderToEbizcharge($localOrder);

            if (isset($ebizchargeOrderParams['error']) && $ebizchargeOrderParams['error'] === false) {
                $salesOrderParamsResp = [
                    'status' => 'success',
                    'error' => false,
                    'message' => __("Success, the order # " . $orderNumber .
                        " has been synced with EBizCharge Payment Gateway"),
                    OrderInterface::EC_ORDER_INTERNALID => $ebizchargeOrderParams[OrderInterface::EC_ORDER_INTERNALID],
                    OrderInterface::EC_ORDER_ID => $ebizchargeOrderParams[OrderInterface::EC_ORDER_ID],
                    OrderInterface::EC_ORDER_PO_NUMBER => $ebizchargeOrderParams[OrderInterface::EC_ORDER_PO_NUMBER],
                    OrderInterface::EBIZCHARGE_DIVISION_ID => $this->_soapApiModel->getDivisionId(),
                    OrderInterface::EBIZCHARGE_SOFTWARE_ID => $this->_soapApiModel->getSoftwareId()
                ];
            }

        } else {
            $salesOrderParamsResp["message"] = __("Error, this order # " . $orderNumber .
                " does not qualify to sync to EBizCharge Hub.");
            $this->_ebizchargeLogger->addCritical(__("This Order # " . $orderNumber .
                " does not qualify to sync to EBizCharge Hub."));
        }

        return $salesOrderParamsResp;
    }

    /**
     * Get EC PO Number
     *
     * @return float|mixed|null
     */
    public function getEcPoNumber()
    {
        return $this->getData(self::EC_ORDER_PO_NUMBER);
    }

    /**
     * Re Order
     *
     * @param null|mixed $parentOrder
     * @return array|int
     * @throws \Exception
     */
    public function reOrder($parentOrder = null)
    {
        $this->_parentOrder = $parentOrder;

        /** set Area Code to Admin HTML for place orders  */
        return $this->_appState->emulateAreaCode(
            Area::AREA_FRONTEND,
            /** running call back  */
            function () {

                /** @var setting back the parent order value $parentOrder */
                $parentOrder = $this->_parentOrder;

                /** @var $availableShippingMethods */
                $store = $this->_storeManager->getStore();

                /** @var  $parentOrder */
                $parentOrderNumber = $parentOrder->getIncrementId();

                $parentOrder = $this->loadByAttribute('increment_id', $parentOrder->getIncrementId());
                $store = $parentOrder->getStore() ? $parentOrder->getStore() : $this->_storeManager->getStore();
                $storeId = $parentOrder->getStoreId() ? $parentOrder->getStoreId() :
                    $this->_storeManager->getStore()->getId();
                $websiteId = $parentOrder->getStore()->getWebsiteId() ? $parentOrder->getStore()->getWebsiteId() :
                    $this->_storeManager->getStore()->getWebsiteId();
                $currency = $parentOrder->getOrderCurrency();
                $customerId = $parentOrder->getCustomerId();

                /** @var $customerEmail */
                $customerEmail = $parentOrder->getCustomerEmail();
                $baseCurrency = $parentOrder->getGlobalCurrencyCode();
                $guestCustomer = $parentOrder->getCustomerIsGuest() ? true : false;

                /** @var  $billingAddress */
                $billingAddress = $parentOrder->getBillingAddress();

                /** @var  $billingStreetAddress */
                $billingStreetAddress = $billingAddress->getStreet();
                $billingStreetAddr = '';

                if (count($billingStreetAddress) > 0) {
                    foreach ($billingStreetAddress as $billingStreetAddr) {
                        $billingStreetAddr .= !empty($billingStreetAddr) ? $billingStreetAddr : ' ';
                    }
                }
                /** @var  $shippingAddress */
                $shippingAddress = $parentOrder->getShippingAddress();

                /** @var $shippingStreetAddress */
                $shippingStreetAddress = $shippingAddress->getStreet();
                $shippingStreetAddr = '';

                if (count($shippingStreetAddress) > 0) {
                    foreach ($shippingStreetAddress as $shippingStreetAddr) {
                        $shippingStreetAddr .= !empty($shippingStreetAddr) ? $shippingStreetAddr : ' ';
                    }
                }

                /** @var  $shippingMethod */
                $shippingMethod = $parentOrder->getShippingMethod();
                /** @var  $paymentMethod */
                $paymentMethod = $parentOrder->getPayment()->getMethod();

                /** @var  $paymentInfo */
                $paymentInfo = $parentOrder->getPayment();

                /** @var $parentOrderedItems */
                $parentOrderedItems = $parentOrder->getAllItems();

                try {
                    $orderedItems = [];
                    /** adding order items to the quote for placing order */
                    if (count($parentOrderedItems) > 0) {
                        foreach ($parentOrderedItems as $parentOrderedItem) {
                            $productId = $parentOrderedItem->getProductId();
                            $product = $this->_productFactory->create()->load($productId);

                            /** throwing the exception in chase of no product */
                            if (!$product->getEntityId()) {
                                $this->_ebizchargeLogger->addCritical(__(
                                    'Exception occurred during Creating Order as the ordered product ' .
                                    $productId . ' doest not exists'
                                ));
                                /** localized exception occurred */
                                throw new LocalizedException(__('Product: ' . $productId .
                                    ' Doest not exist during re ordering '));
                            }
                            /** ordered items */
                            $orderedItems [] = [
                                'product_id' => $productId,
                                'qty' => $parentOrderedItem->getQtyOrdered()
                            ];
                        }
                    }

                    /** @var  $orderData */
                    $orderData = [
                        'currency_id' => $baseCurrency,
                        'email' => $customerEmail,
                        'guest_order' => $guestCustomer,
                        'billing_address' => [
                            'firstname' => $billingAddress->getFirstname(),
                            'lastname' => $billingAddress->getLastname(),
                            'street' => $billingStreetAddr,
                            'city' => $billingAddress->getCity(),
                            'country_id' => $billingAddress->getCountryId(),
                            'region' => $billingAddress->getRegion(),
                            'region_id' => $shippingAddress->getRegionId(),
                            'postcode' => $billingAddress->getPostcode(),
                            'telephone' => $billingAddress->getTelephone(),
                            'save_in_address_book' => 1
                        ],
                        'shipping_address' => [
                            'firstname' => $shippingAddress->getFirstname(),
                            'lastname' => $shippingAddress->getLastname(),
                            'street' => $shippingStreetAddr,
                            'city' => $shippingAddress->getCity(),
                            'country_id' => $shippingAddress->getCountryId(),
                            'region' => $shippingAddress->getRegion(),
                            'region_id' => $shippingAddress->getRegionId(),
                            'postcode' => $shippingAddress->getPostcode(),
                            'telephone' => $shippingAddress->getTelephone(),
                            'save_in_address_book' => 1
                        ],
                        'items' => $orderedItems
                    ];

                    $customer = $this->_customerFactory->create();
                    $customer->setWebsiteId($websiteId);
                    /** load customet by email address */
                    $customer->loadByEmail($orderData['email']);

                    if (!$customer->getEntityId()) {
                        //If not available then create this customer
                        $customer->setWebsiteId($websiteId)
                            ->setStore($store)
                            ->setFirstname($orderData['shipping_address']['firstname'])
                            ->setLastname($orderData['shipping_address']['lastname'])
                            ->setEmail($orderData['email'])
                            ->setPassword($orderData['email']);
                        $customer->save();
                    }

                    //Create object of quote
                    $quote = $this->_quoteFactory->create();
                    //set store for which you create quote
                    $quote->setStore($store);

                    // if you have already buyer id then you can load customer directly
                    $customer = $this->_customerRepository->getById($customer->getEntityId());
                    $quote->setCurrency();
                    $quote->assignCustomer($customer); //Assign quote to customer

                    //add items in quote
                    foreach ($orderData['items'] as $item) {
                        $product = $this->_productFactory->create()->load($item['product_id']);

                        /** adding product to quote */
                        $quote->addProduct(
                            $product,
                            // phpcs:ignore
                            intval($item['qty'])
                        );
                    }

                    //Set Address to quote
                    $quote->getBillingAddress()->addData($orderData['shipping_address']);
                    $quote->getShippingAddress()->addData($orderData['shipping_address']);

                    /** @var $availableShippingMethods */
                    $availableShippingMethods = $this->_ebizchargeConfigModel->getActiveCarriers($store);

                    /** @var  $originalShippingMethod */
                    $originalShippingMethod = $shippingMethod;

                    /** @var setting back the shipping method $newShippingMethod */
                    $newShippingMethod = '';

                    /** get available shipping methods */
                    if (count($availableShippingMethods) > 0) {
                        foreach ($availableShippingMethods as $availableShippingMethod) {
                            $shippingMethodId = $availableShippingMethod['id'];
                            $shippingMethodTitle = $shippingMethodId . '_' . $shippingMethodId;

                            if ($shippingMethodTitle == $originalShippingMethod) {
                                $newShippingMethod = $shippingMethodTitle;
                            }
                        }
                    }

                    /** if shipping method is not available then select the back up shipping method */
                    if ($newShippingMethod == '') {
                        $newShippingMethod = $this->_ebizchargeConfigModel->getEbizConfigShippingMethod($storeId);
                    }
                    $availablePaymentMethods = $this->_ebizchargeConfigModel->getActivePaymentMethods();
                    $newPaymentMethod = '';
                    if (count($availablePaymentMethods) > 0) {
                        foreach ($availablePaymentMethods as $availablePaymentMethod) {
                            $paymentMethodTitle = $availablePaymentMethod->getTitle();
                            $paymentMethodCode = $availablePaymentMethod->getCode();
                            if ($paymentMethod == $paymentMethodCode) {
                                $newPaymentMethod = $paymentMethodCode;
                            }
                        }
                    }
                    if ($newPaymentMethod === '') {
                        $newPaymentMethod = PaymentInterface::CODE;
                    }
                    // Collect Rates and Set Shipping & Payment Method
                    $shippingAddress = $quote->getShippingAddress();
                    $shippingAddress->setCollectShippingRates(true)
                        ->collectShippingRates()
                        ->setShippingMethod($newShippingMethod); //shipping method
                    $quote->setPaymentMethod($newPaymentMethod); //payment method
                    $quote->setInventoryProcessed(false); //not effetc inventory

                    //  $quote->getShippingAddress()->setShipping
                    $quote->collectTotals();

                    $quote->save(); //Now Save quote and your quote is ready

                    // Set Sales Order Payment
                    $quote->getPayment()->importData(
                        [
                            'method' => $newPaymentMethod
                        ]
                    );
                    // Collect Totals & Save Quote
                    $quote->collectTotals()->save();

                    // Create Order From Quote
                    $order = $this->_quoteManagement->submit($quote);

                    /** stopping sending email to Customer */
                    $order->setEmailSent(0);

                    /** @var $orderNumber */
                    $orderNumber = $order->getRealOrderId();

                    if ($order && $order->getEntityId()) {
                        /** @var  $orderNumber */
                        $orderNumber = $order->getRealOrderId();
                        $this->_ebizchargeLogger->addInfo(__('Success New order ' . $orderNumber .
                            ' has been created from the previouse Order ' . $parentOrderNumber . ' '));
                        $result = [
                            'error' => false,
                            'order_number' => $orderNumber,
                            'order_id' => $order->getEntityId(),
                            'msg' => 'Success: New clone of the order has been placed ' . $orderNumber
                        ];

                        /** create invoice for the order */
                        $orderId = $order->getEntityId();
                        $amountDue = $order->getBaseTotalDue();
                        $grandTotal = (double)$order->getBaseGrandTotal();

                        /** check if due amount is Zero or it is with Partial Inovice */
                        /** @var  $ebizchargePaymentMethod */
                        $ebizchargePaymentMethod = PaymentInterface::CODE;
                        $order->setPaymentMethod($ebizchargePaymentMethod);
                        $order->getPayment()->setMethod($ebizchargePaymentMethod);
                        $order->getPayment()->setAdditionalData($ebizchargePaymentMethod);
                        $order->getPayment()->setAdditionalInformation(
                            ['method_title' => $ebizchargePaymentMethod]
                        );
                        /** saving order Params for Ebizcharge */
                        $order->save();

                        /** create Invoice */
                        if ($amountDue == 0 && $grandTotal > $amountDue) {
                            // $this->createInvoice($orderId, $paymentMethod);
                        }
                    } else {
                        /** @var  $result */
                        $result = [
                            'error' => true,
                            'order_number' => '',
                            'order_id' => '',
                            'msg' => 'Error occured during placing order of the Clone ' . $parentOrderNumber
                        ];
                        $this->_ebizchargeLogger->addError(__('Error occurred during cloning the Order ' .
                            $parentOrderNumber));
                    }

                    return $result;
                } catch (Exception $exception) {
                    $this->_ebizchargeLogger->addCritical(__("Exception occurred during creating Order Error: " .
                        $exception->getMessage()));
                    $result = [
                        'error' => true,
                        'order_number' => '',
                        'order_id' => '',
                        'msg' => 'Error occurred during placing order of the Clone Error: ' . $exception->getMessage()
                    ];

                    return $result;
                }
            }
        );
    }

    /**
     * Get Recurring Parent Order
     *
     * @return string
     */
    public function getRecurringParentOrder(): string
    {
        return $this->getData(OrderInterface::EBIZCHARGE_RECURRING_PARENT_ORDER);
    }

    /**
     * Get Recurring addtional Info
     *
     * @return string
     */
    public function getRecurringAdditionalInfo(): string
    {
        return $this->getData(OrderInterface::EBIZCHARGE_RECURRING_ADDITIONAL_INFO);
    }

    /**
     * Set Recurring Parent Order
     *
     * @param mixed $recurringParentOrder
     * @return Order
     */
    public function setRecurringParentOrder($recurringParentOrder)
    {
        return $this->setData(OrderInterface::EBIZCHARGE_RECURRING_PARENT_ORDER, $recurringParentOrder);
    }

    /**
     * Set Recurring Additoional Info
     *
     * @param mixed $recurringAdditionalInfo
     * @return Order
     */
    public function setRecurringAdditionalInfo($recurringAdditionalInfo)
    {
        return $this->setData(OrderInterface::EBIZCHARGE_RECURRING_ADDITIONAL_INFO, $recurringAdditionalInfo);
    }

    /**
     * Get Ec Surcharge Percentage
     *
     * @return float|null
     */
    public function getEcSurchargePercentage()
    {
        return $this->getData(SurchargeInterface::EC_SURCHARGE_PERCENTAGE);
    }

    /**
     * Get Ec Surcharge Ineligible
     *
     * @return float|null
     */
    public function getEcSurchargeIneligible()
    {
        return $this->getData(SurchargeInterface::EC_SURCHARGE_INELIGIBLE);
    }

    /**
     * Set Ec Surcharge Ineligible
     *
     * @param float|null $surchargeIneligible
     * @return OrderInterface
     */
    public function setEcSurchargeIneligible($surchargeIneligible)
    {
        return $this->setData(SurchargeInterface::EC_SURCHARGE_INELIGIBLE, $surchargeIneligible);
    }

    /**
     * Prepare Ordered Item Price
     *
     * @param null|mixed $quoteItemId
     * @return array
     */
    public function prepareOrderedItemPrice($quoteItemId = null): array
    {
        /** @var  $itemDetail */
        $itemDetail = [];

        /** @var  $quoteItem */
        $quoteItem = $this->quoteItemFactory->create()->load($quoteItemId);
        $itemDetail = $quoteItem->getData();
        $productId = $quoteItem->getProductId() ?? 0;
        $product = $this->_productFactory->create()->load($productId);
        $itemDetail["description"] = $quoteItem->getName() . ":" . $quoteItem->getSku();
        $itemDetail["short_description"] = $quoteItem->getName() . "(" . $productId . ")";
        $itemDetail["unit_of_measure"] = $this->_configResource->getWeightUnit();
        $itemDetail["product_ref_num"] = $productId;
        $itemDetail["weight"] = 0;
        if ($productId !== 0 && $product) {
            $itemDetail["weight"] = $product->getWeight() ?? 0;
        }

        /**
         * if Parent Item ID
         */
        // phpcs:ignore
        if (@$quoteItem->getParentItemId()) {
            $parentItemId = $quoteItem->getParentItemId();
            $parentQuoteItem = $this->quoteItemFactory->create()->load($parentItemId);
            $productId = $parentQuoteItem->getProductId() ?? 0;
            $itemDetail = $parentQuoteItem->getData();
            $product = $this->_productFactory->create()->load($productId);
            $itemDetail["description"] = $parentQuoteItem->getName() . ":" . $parentQuoteItem->getSku();
            $itemDetail["short_description"] = $parentQuoteItem->getName() . "(" . $productId . ")";
            $itemDetail["product_ref_num"] = $productId;
            $itemDetail["unit_of_measure"] = $this->_configResource->getWeightUnit();
            $itemDetail["weight"] = 0;
            if ($productId !== 0 && $product) {
                $itemDetail["weight"] = $product->getWeight() ?? 0;
            }
        }

        return $itemDetail;
    }

    /**
     * @param $orderId
     * @param $orderPostParams
     * @param $newOrderModel
     * @return bool|void
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function processEditOrderQuickSale($orderId = null, $orderPostParams = null, $newOrderModel = null)
    {

        /** @var  $isOrderEditVoid */
        $isOrderEditVoidSelected = true;
        $store = $this->_configResource->getStore();
        /** @var  $storeId */
        $storeId = $store->getStoreId() ?? "0";
        /** @var  $isOrderVoidSelectedNo */
        $isOrderVoidSelectedNo = $this->_configResource->getVoidOrderEditFlow($storeId);
        /** To check surcharge is enabled on EBizCharge Portal & on Magento as well */
        $customerFactory = $this->_customerFactory->create();
        $isSurchargeEnabled = $customerFactory->isSurchargeEnabled();
        $surchargeSettings = $customerFactory->getSurchargeSettings($storeId);
        $surchargePercentage = isset($surchargeSettings["surchargePercentage"]) ? (float)$surchargeSettings["surchargePercentage"] : 0;


        try {

            if ($newOrderModel->getSession()->getOrder()->getId()) {
                if ($isOrderVoidSelectedNo && $isOrderVoidSelectedNo === "2") {

                    $newOrderModel->saveQuote();
                    $oldOrderId = $newOrderModel->getSession()->getOrder()->getId();
                    $oldOrder = $newOrderModel->getSession()->getOrder();
                    $oldOrderQuoteId = $newOrderModel->getSession()->getData("quote_id");
                    $quote = $this->getQuoteFromAdminSession();
                    $quoteId = $quote->getId();

                    /** @var  $currentOrder */
                    $oldOrder = $this->load($oldOrderId);
                    $oldOrderQuote = $this->_quoteFactory->create()->load($oldOrderQuoteId);

                    $newOrderPostParams = (array)$orderPostParams;
                    $newOrderPayment = isset($newOrderPostParams["payment"]) ? $newOrderPostParams["payment"] : [];
                    $newOrderItems = isset($newOrderPostParams["in_products"]) ? $newOrderPostParams["in_products"] : [];
                    $newOrderQuote = $newOrderModel->getQuote();

                    if (isset($newOrderPostParams['account'])) {
                        // $this->setAccountData($newOrderPostParams['account']);
                    }

                    if (isset($newOrderPostParams['comment'])) {
                        $oldOrderQuote->addData($newOrderPostParams['comment']);
                        if (empty($newOrderPostParams['comment']['customer_note_notify'])) {
                            $oldOrder->setCustomerNoteNotify(false);
                            $oldOrderQuote->setCustomerNoteNotify(false);
                        } else {
                            $oldOrder->setCustomerNoteNotify(true);
                            $oldOrderQuote->setCustomerNoteNotify(true);
                        }
                    }

                    $order = $this->orderFactory->create()->load($orderId);

                    if (!$order) {
                        /**
                         * When there is no order return back error
                         */
                        $isOrderEditVoidSelected = false;
                        throw new NoSuchEntityException(__("No such order exists. please try again."));
                    }

                    $order->setQuoteId($quoteId);
                    $quote->collectTotals();
                    $quoteTotals = $quote->getTotals();
                    $taxAmount = 0;

                    $taxTotals = isset($quoteTotals["tax"]) ? $quoteTotals["tax"] : null;

                    if ($taxTotals) {
                        $taxAmount = $taxTotals->getValue();
                        $quote->setTaxAmount($taxAmount);
                        $quote->setBaseTaxAmount($taxAmount);
                        $quote->setSubtotalInclTax((float)$taxAmount + $quote->getSubtotal());
                        $quote->setBaseSubtotalInclTax((float)$taxAmount + $quote->getBaseSubtotal());
                    }

                    /**
                     * saving new Order Items
                     */
                    $this->setNewOrderItems($order, $quote);


                    /**
                     * save new Order Billing Address
                     */
                    $this->setNewOrderBillingAddress($oldOrder, $newOrderQuote, $oldOrderQuote);

                    /**
                     * Save new Order shipping Method
                     */
                    $this->setNewOrderShippingMethod($oldOrder, $newOrderModel, $oldOrderQuote);

                    /**
                     * Set new Order Payment
                     */
                    $this->setNewOrderPayment($oldOrder, $newOrderQuote, $newOrderPostParams, $oldOrderQuote);

                    /**
                     * Save new Order Totals
                     */
                    $this->collectOrderTotals($order, $quote);

                    $oldOrderQuote->save();
                    $quote->save();

                    /**
                     * saving current Order
                     */
                    $isOrderEditVoidSelected = false;
                    $order->save();

                }
            }

        } catch (Exception $exception) {
            $this->_ebizchargeLogger->addCritical(__("Could not edit your order : " . $exception->getMessage()));
            //  var_dump($exception->getMessage()); exit;
        }

        return $isOrderEditVoidSelected;

    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuoteFromAdminSession()
    {
        return $this->adminQuoteSession->getQuote();
    }

    /**
     * @param $origOrder
     * @param $quote
     * @return void
     * @throws NoSuchEntityException
     */
    public function setNewOrderItems($origOrder = null, $quote = null)
    {
        $newOrderItems = [];
        $origOrderItems = $origOrder->getAllItems();
        $newOrderItems = array_merge($newOrderItems, $origOrderItems);
        $quoteItems = $quote->getAllVisibleItems();
        $newOrderItems = array_merge($newOrderItems, $quoteItems);
        $sameOrderedItems = [];

        if (count($newOrderItems) > 0) {

            /** add new quote order items  */
            foreach ($newOrderItems as $newOrderItem) {

                $productId = $newOrderItem->getProductId();
                $orderedId = $newOrderItem->getOrderId();
                $quotedQty = (float)$newOrderItem->getQty() ?? 0;
                $orderedQty = (float)$newOrderItem->getQtyOrdered() ?? 0;
                $invoicedQty = (float)$newOrderItem->getQtyInvoiced() ?? 0;
                $shippedQty = (float)$newOrderItem->getQtyShipped() ?? 0;

                if (!empty($orderedId) && ($shippedQty > 0 || $invoicedQty > 0)) {
                    $sameOrderedItems[$productId] = $newOrderItem;
                    /**
                     * if ordered quantity and shipped qty is greater than 0
                     * Skip item
                     */
                    continue;
                }
                if (!empty($orderedId) && $orderedQty > 0) {
                    /**
                     * delete item if already ordered and not in quote
                     */
                    $newOrderItem->delete();
                    continue;
                }

                if (empty($orderedId) && array_key_exists($productId, $sameOrderedItems)) {
                    /**
                     * update same invoiced item qty
                     */
                    $invoicedItem = isset($sameOrderedItems[$productId]) ? $sameOrderedItems[$productId] : null;
                    $invoicedQty = (float)$invoicedItem->getQtyInvoiced();

                    if ($invoicedItem && $invoicedQty > 0) {
                        $quoteQty = (float)$newOrderItem->getQty();
                        $invoicedQty = (float)$invoicedItem->getQtyInvoiced();
                        $newInvoicedOrderedQty = $quoteQty + $invoicedQty;

                        $quoteItemPrice = (float)$newOrderItem->getPrice();
                        $quoteItemBasePrice = (float)$newOrderItem->getBasePrice();
                        $quoteRowTotal = $quoteQty * $quoteItemPrice;
                        $quoteBaseRowTotal = $quoteQty * $quoteItemBasePrice;

                        $invoiceItemPrice = (float)$invoicedItem->getPrice();
                        $invoiceItemBasePrice = (float)$invoicedItem->getBasePrice();
                        $invoiceRowTotal = $invoicedQty * $invoiceItemPrice;
                        $invoiceBaseRowTotal = $invoicedQty * $invoiceItemBasePrice;


                        $invoicedItem
                            ->setBasePrice((float)$newOrderItem->getBasePrice())
                            ->setPrice((float)$newOrderItem->getPrice())
                            ->setBasePriceInclTax((float)$newOrderItem->getBasePriceInclTax())
                            ->setPriceInclTax((float)$newOrderItem->getPriceInclTax())
                            ->setDiscountAmount((float)$newOrderItem->getDiscountAmount() + (float)$newOrderItem->getDiscountAmount())
                            ->setQtyOrdered($newInvoicedOrderedQty)
                            ->setTaxPercent((float)$newOrderItem->getTaxPercent())
                            ->setTaxAmount((float)$newOrderItem->getTaxAmount() + (float)$newOrderItem->getTaxAmount())
                            ->setBaseTaxAmount((float)$newOrderItem->getBaseTaxAmount() + (float)$newOrderItem->getBaseTaxAmount())
                            ->setBaseRowTotal((float)$newOrderItem->getBaseRowTotal() + (float)$newOrderItem->getBaseRowTotal())
                            ->setRowTotal((float)$newOrderItem->getRowTotal() + (float)$newOrderItem->getRowTotal())
                            ->setRowTotalInclTax((float)$newOrderItem->getRowTotalInclTax() + (float)$newOrderItem->getRowTotalInclTax())
                            ->setEcShippingAmount((float)$newOrderItem->getEcShippingAmount() + (float)$newOrderItem->getEcShippingAmount())
                            ->setEcSurchargeAmount((float)$newOrderItem->getEcSurchargeAmount() + (float)$newOrderItem->getEcSurchargeAmount())
                            ->setDiscountAmount((float)$newOrderItem->getDiscountAmount() + (float)$newOrderItem->getDiscountAmount())
                            ->setBaseDiscountAmount((float)$newOrderItem->getBaseDiscountAmount() + (float)$newOrderItem->getBaseDiscountAmount())
                            ->setWeight($newOrderItem->getWeight() + $newOrderItem->getWeight())
                            ->setIsVirtual($newOrderItem->getIsVirtual())
                            ->setFreeShipping($newOrderItem->getFreeShipping())
                            ->save();
                    }

                    continue;

                }

                //  dump("productId:".$productId." orderedId=". $orderedId." orderedQty=". $orderedQty." invoicedQty=".$invoicedQty." shippedQty=".$shippedQty." quoteQty=".$quotedQty);

                $product = $this->_productRepository->getById($productId);
                $orderItem = $this->orderItemFactory->create();
                $cQuoteItem = $this->quoteItemFactory->create();
                $cartItem = $this->cartItemInterfaceFactory->create();

                $productInfoBuyRequests = $newOrderItem->getProduct()->getTypeInstance(true)->getOrderOptions($newOrderItem->getProduct()) ?? [];

                $orderItem
                    ->setProductId($newOrderItem->getProductId())
                    ->setName($newOrderItem->getName())
                    ->setSku($newOrderItem->getSku())
                    ->setQuoteItemId($newOrderItem->getQuoteId())
                    ->setBasePrice((float)$newOrderItem->getBasePrice())
                    ->setPrice((float)$newOrderItem->getPrice())
                    ->setBasePriceInclTax((float)$newOrderItem->getBasePriceInclTax())
                    ->setPriceInclTax((float)$newOrderItem->getPriceInclTax())
                    ->setDiscountAmount((float)$newOrderItem->getDiscountAmount())
                    ->setQtyOrdered((float)$newOrderItem->getQty())
                    ->setTaxPercent((float)$newOrderItem->getTaxPercent())
                    ->setTaxAmount((float)$newOrderItem->getTaxAmount())
                    ->setBaseTaxAmount((float)$newOrderItem->getBaseTaxAmount())
                    ->setBaseRowTotal((float)$newOrderItem->getBaseRowTotal())
                    ->setRowTotal((float)$newOrderItem->getRowTotal())
                    ->setRowTotalInclTax((float)$newOrderItem->getRowTotalInclTax())
                    ->setEcShippingAmount($newOrderItem->getEcShippingAmount())
                    ->setEcSurchargeAmount($newOrderItem->getEcSurchargeAmount())
                    ->setDiscountAmount($newOrderItem->getDiscountAmount())
                    ->setBaseDiscountAmount($newOrderItem->getBaseDiscountAmount())
                    ->setWeight($newOrderItem->getWeight())
                    ->setIsVirtual($newOrderItem->getIsVirtual())
                    ->setFreeShipping($newOrderItem->getFreeShipping())
                    ->setPriceInclTax($newOrderItem->getPriceInclTax())
                    ->setBasePriceInclTax($newOrderItem->getBasePriceInclTax());

                $productQtyOptions = $newOrderItem->getQtyOptions() ?? [];

                if (count($productQtyOptions) > 0) {
                    $orderItem->setQtyOptions($productQtyOptions);
                }
                if (is_array($productInfoBuyRequests) && count($productInfoBuyRequests) > 0) {
                    $orderItem->setProductOptions($productInfoBuyRequests);
                }
                $orderItem->setProductType($newOrderItem->getProductType());
                $orderItem->setHasChildren($newOrderItem->getHasChildren());

                $origOrder->addItem($orderItem);

            }

            $quote->collectTotals()->save();
            $origOrder->save();
        }


    }

    /**
     * Set Ec Surcharge Amount
     *
     * @param float|null $surchargeAmount
     * @return OrderInterface
     */
    public function setEcSurchargeAmount($surchargeAmount)
    {
        return $this->setData(SurchargeInterface::EC_SURCHARGE_AMOUNT, $surchargeAmount);
    }

    /**
     * Get Ec Surcharge Amount
     *
     * @return float|null
     */
    public function getEcSurchargeAmount()
    {
        return $this->getData(SurchargeInterface::EC_SURCHARGE_AMOUNT);
    }

    /**
     * @param $oldOrderModel
     * @param $newOrderModel
     * @param $oldOrderQuote
     * @return void
     */
    public function setNewOrderBillingAddress($oldOrderModel = null, $newOrderModel = null, $oldOrderQuote = null)
    {
        if ($newOrderModel->getBillingAddress()) {
            $oldOrderBillingAddress = $oldOrderModel->getBillingAddress();
            $neworderBillingAddress = $newOrderModel->getBillingAddress();
            $oldBillingAddressId = $oldOrderBillingAddress->getId() ?? 0;

            $billingAddress = $this->orderAddressInterfaceFactory->create()->load($oldBillingAddressId);
            $billingAddress
                ->setPrefix($neworderBillingAddress->getPrefix())
                ->setSuffix($neworderBillingAddress->getSuffix())
                ->setFirstname($neworderBillingAddress->getFirstname())
                ->setLastname($neworderBillingAddress->getLastname())
                ->setMiddlename($neworderBillingAddress->getMiddlename())
                ->setCompany($neworderBillingAddress->getCompany())
                ->setStreet($neworderBillingAddress->getStreet())
                ->setCity($neworderBillingAddress->getCity())
                ->setCountryId($neworderBillingAddress->getCountryId())
                ->setRegion($neworderBillingAddress->getRegion())
                ->setRegionId($neworderBillingAddress->getRegionId())
                ->setPostcode($neworderBillingAddress->getPostcode())
                ->setTelephone($neworderBillingAddress->getTelephone())
                ->setFax($neworderBillingAddress->getFax())
                ->setVatId($neworderBillingAddress->getVatId())
                ->save();

            $oldOrderQuote->getBillingAddress()
                ->setPrefix($neworderBillingAddress->getPrefix())
                ->setSuffix($neworderBillingAddress->getSuffix())
                ->setFirstname($neworderBillingAddress->getFirstname())
                ->setLastname($neworderBillingAddress->getLastname())
                ->setMiddlename($neworderBillingAddress->getMiddlename())
                ->setCompany($neworderBillingAddress->getCompany())
                ->setStreet($neworderBillingAddress->getStreet())
                ->setCity($neworderBillingAddress->getCity())
                ->setCountryId($neworderBillingAddress->getCountryId())
                ->setRegion($neworderBillingAddress->getRegion())
                ->setRegionId($neworderBillingAddress->getRegionId())
                ->setPostcode($neworderBillingAddress->getPostcode())
                ->setTelephone($neworderBillingAddress->getTelephone())
                ->setFax($neworderBillingAddress->getFax())
                ->setVatId($neworderBillingAddress->getVatId())
                ->save();

        }

    }

    /**
     * @param $oldOrderModel
     * @param $newOrderModel
     * @param $oldOrderQuote
     * @return void
     * @throws NoSuchEntityException
     */
    public function setNewOrderShippingMethod($oldOrderModel = null, $newOrderModel = null, $oldOrderQuote = null)
    {
        if ($newOrderModel->getShippingMethod()) {
            $oldOrderModel->setShippingMethod($newOrderModel->getShippingMethod());
            $oldOrderModel->setShippingDescription($newOrderModel->getShippingDescription());

            $quote = $this->_cartRepositoryInterface->get($oldOrderQuote->getId());
            $quote->setShippingMethod($newOrderModel->getShippingMethod());
            $quote->setShippingDescription($newOrderModel->getShippingDescription());
            /**
             * Save new Order Shipping Address
             */
            $this->setNewOrderShippingAddress($oldOrderModel, $newOrderModel, $oldOrderQuote);
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->getShippingAddress()->collectShippingRates();
            $quote->collectTotals();
            $quote->save();
        }

    }

    /**
     * @param $oldOrderModel
     * @param $newOrderModel
     * @param $oldOrderQuote
     * @return void
     * @throws \Exception
     */
    public function setNewOrderShippingAddress($oldOrderModel = null, $newOrderModel = null, $oldOrderQuote = null)
    {
        if ($newOrderModel->getShippingAddress()) {
            $oldOrderShippingAddress = $oldOrderModel->getShippingAddress();
            $neworderShippingAddress = $newOrderModel->getShippingAddress();
            $oldShippingAddressId = $oldOrderShippingAddress->getId() ?? 0;

            $shippingAddress = $this->orderAddressInterfaceFactory->create()->load($oldShippingAddressId);
            $shippingAddress
                ->setPrefix($neworderShippingAddress->getPrefix())
                ->setSuffix($neworderShippingAddress->getSuffix())
                ->setFirstname($neworderShippingAddress->getFirstname())
                ->setLastname($neworderShippingAddress->getLastname())
                ->setMiddlename($neworderShippingAddress->getMiddlename())
                ->setCompany($neworderShippingAddress->getCompany())
                ->setStreet($neworderShippingAddress->getStreet())
                ->setCity($neworderShippingAddress->getCity())
                ->setCountryId($neworderShippingAddress->getCountryId())
                ->setRegion($neworderShippingAddress->getRegion())
                ->setRegionId($neworderShippingAddress->getRegionId())
                ->setPostcode($neworderShippingAddress->getPostcode())
                ->setTelephone($neworderShippingAddress->getTelephone())
                ->setFax($neworderShippingAddress->getFax())
                ->setVatId($neworderShippingAddress->getVatId());

            $shippingAddress->save();

            $olderQuoteShippingAddress = $oldOrderQuote->getShippingAddress();
            $olderQuoteShippingAddress
                ->setPrefix($neworderShippingAddress->getPrefix())
                ->setSuffix($neworderShippingAddress->getSuffix())
                ->setFirstname($neworderShippingAddress->getFirstname())
                ->setLastname($neworderShippingAddress->getLastname())
                ->setMiddlename($neworderShippingAddress->getMiddlename())
                ->setCompany($neworderShippingAddress->getCompany())
                ->setStreet($neworderShippingAddress->getStreet())
                ->setCity($neworderShippingAddress->getCity())
                ->setCountryId($neworderShippingAddress->getCountryId())
                ->setRegion($neworderShippingAddress->getRegion())
                ->setRegionId($neworderShippingAddress->getRegionId())
                ->setPostcode($neworderShippingAddress->getPostcode())
                ->setTelephone($neworderShippingAddress->getTelephone())
                ->setFax($neworderShippingAddress->getFax())
                ->setVatId($neworderShippingAddress->getVatId())
                ->save();

        }

    }

    /**
     * @param $oldOrderModel
     * @param $newOrderModel
     * @param $newOrderRequestParams
     * @param $oldOrderQuote
     * @return void
     */
    public function setNewOrderPayment($oldOrderModel = null, $newOrderModel = null, $newOrderRequestParams = [], $oldOrderQuote = null)
    {

        $newOrderPaymentData = isset($newOrderRequestParams['payment']) ? $newOrderRequestParams['payment'] : [];

        if (isset($newOrderPaymentData["method"])) {
            $oldOrderModel->getPayment()->setMethod($newOrderPaymentData["method"]);
            //  $oldOrderModel->getPayment()->importData($newOrderPaymentData);
            // $oldOrderQuote->getPayment()->importData($newOrderPaymentData);

            if (isset($newOrderPaymentData["ebzc_option"]) && $newOrderPaymentData["ebzc_option"] === "paylater") {
                // $oldOrderModel->getPayment()->setEbzcOption($newOrderPaymentData["ebzc_option"]);
                $oldOrderModel->getPayment()->setEbzcQuickSale($newOrderPaymentData["ebzc_quick_sale"]);
                $oldOrderModel->getPayment()->setEbzcQuickCapture(1);
                $oldOrderModel->getPayment()->setAdditionalInformation("ebzc_quick_sale", $newOrderPaymentData["ebzc_quick_sale"]);
                $oldOrderModel->getPayment()->setAdditionalInformation("ebzc_quick_capture", 1);

            }
        }
    }

    /**
     * @param CoreOrder|null $origOrder
     * @param $quote
     * @return void
     */
    protected function collectOrderTotals(CoreOrder $origOrder = null, $quote = null)
    {

        $invoiceCollection = $origOrder->getInvoiceCollection();
        $paidTotals = $this->getPaidInvoicesTotals($origOrder);
        // dump($paidTotals->getData());

        $quoteBaseSubtotal = $quote->getBaseSubtotal() ?? 0;
        $quoteSubtotal = $quote->getSubtotal() ?? 0;
        $quoteBaseSubtotalWithDiscount = $quote->getBaseSubtotalWithDiscount() ?? 0;
        $quoteSubtotalWithDiscount = $quote->getSubtotalWithDiscount() ?? 0;
        $quoteDiscountAmount = $quoteSubtotalWithDiscount - $quoteSubtotal;
        $quoteBaseDiscountAmount = $quoteBaseSubtotalWithDiscount - $quoteBaseSubtotal;


        $orderSubtotalInvoiced = (float)$origOrder->getTotalInvoiced();

        $quoteTaxAmount = (float)$quote->getTaxAmount();
        $quoteBaseTaxAmount = (float)$quote->getBaseTaxAmount();
        $quoteSubtotal = (float)$quote->getSubtotal();
        $quoteBaseSubtotalInclTax = (float)$quote->getBaseSubtotalInclTax();
        $quoteSubtotalInclTax = (float)$quote->getSubtotalInclTax();
        $quoteBaseSubtotal = (float)$quote->getBaseSubtotal();
        $quoteGrandTotal = (float)$quote->getGrandTotal();
        $quoteBaseGrandTotal = (float)$quote->getBaseGrandTotal();
        $quoteBaseShippingAmount = (float)$quote->getBaseShippingAmount();
        $quoteShippingAmount = (float)$quote->getShippingAmount();
        $quoteBaseShippingInclTax = (float)$quote->getBaseShippingInclTax();
        $quoteShippingInclTax = (float)$quote->getShippingInclTax();
        $quoteOrderedTotalQty = (float)$quote->getItemsQty();
        $quoteSurchargeAmount = (float)$quote->getEcSurchargeAmount();

        if ($quoteSurchargeAmount > 0) {
            $quoteGrandTotal += $quoteSurchargeAmount;
            $quoteBaseGrandTotal += $quoteSurchargeAmount;
            $quote->setGrandTotal($quoteGrandTotal);
            $quote->setBaseGrandTotal($quoteBaseGrandTotal);
        }
        /**
         * if invoiced and then need to edit invoiced qty
         * and amount
         */
        if ($orderSubtotalInvoiced > 0 && $invoiceCollection && count($invoiceCollection) > 0) {


            $orderTaxAmount = (float)$paidTotals->getTaxAmount();
            $orderBaseTaxAmount = (float)$paidTotals->getBaseTaxAmount();
            $orderSubtotal = (float)$paidTotals->getSubtotal();
            $orderBaseSubtotalInclTax = (float)$paidTotals->getBaseSubtotalInclTax();
            $orderSubtotalInclTax = (float)$paidTotals->getSubtotalInclTax();
            $orderBaseSubtotal = (float)$paidTotals->getBaseSubtotal();
            $orderGrandTotal = (float)$paidTotals->getGrandTotal();
            $orderBaseGrandTotal = (float)$paidTotals->getBaseGrandTotal();
            $orderBaseShippingAmount = (float)$paidTotals->getBaseShippingAmount();
            $orderShippingAmount = (float)$paidTotals->getShippingAmount();
            $orderBaseShippingInclTax = (float)$paidTotals->getBaseShippingInclTax();
            $orderShippingInclTax = (float)$paidTotals->getShippingInclTax();
            $orderBaseDiscountAmount = (float)$paidTotals->getBaseDiscountAmount();
            $orderDiscountAmount = (float)$paidTotals->getDiscountAmount();
            $orderedTotalQty = (float)$paidTotals->getTotalQty();
            $orderedSurchargeAmount = (float)$paidTotals->getEcSurchargeAmount();


            $orderTaxAmount += $quoteTaxAmount;
            $orderBaseTaxAmount += $quoteBaseTaxAmount;
            $orderSubtotal += $quoteSubtotal;
            $orderBaseSubtotalInclTax += $quoteBaseSubtotalInclTax;
            $orderSubtotalInclTax += $quoteSubtotalInclTax;
            $orderBaseSubtotal += $quoteBaseSubtotal;
            $orderBaseGrandTotal += $quoteBaseGrandTotal;
            $orderGrandTotal += $quoteGrandTotal;
            $orderBaseShippingAmount += $quoteBaseShippingAmount;
            $orderShippingAmount += $quoteShippingAmount;
            $orderBaseShippingInclTax += $quoteBaseShippingInclTax;
            $orderShippingInclTax += $quoteShippingInclTax;
            $orderBaseDiscountAmount += $quoteBaseDiscountAmount;
            $orderDiscountAmount += $quoteDiscountAmount;
            $orderedTotalQty += $quoteOrderedTotalQty;
            $orderedSurchargeAmount += $quoteSurchargeAmount;

        } else {
            $orderTaxAmount = $quoteTaxAmount;
            $orderBaseTaxAmount = $quoteBaseTaxAmount;
            $orderSubtotal = $quoteSubtotal;
            $orderBaseSubtotalInclTax = $quoteBaseSubtotalInclTax;
            $orderSubtotalInclTax = $quoteSubtotalInclTax;
            $orderBaseSubtotal = $quoteBaseSubtotal;
            $orderBaseGrandTotal = $quoteBaseGrandTotal;
            $orderGrandTotal = $quoteGrandTotal;
            $orderBaseShippingAmount = $quoteBaseShippingAmount;
            $orderShippingAmount = $quoteShippingAmount;
            $orderBaseShippingInclTax = $quoteBaseShippingInclTax;
            $orderShippingInclTax = $quoteShippingInclTax;
            $orderBaseDiscountAmount = $quoteBaseDiscountAmount;
            $orderDiscountAmount = $quoteDiscountAmount;
            $orderedTotalQty = $quoteOrderedTotalQty;
            $orderedSurchargeAmount = $quoteSurchargeAmount;

        }

        $origOrder->setTaxAmount($orderTaxAmount)
            ->setBaseTaxAmount($orderBaseTaxAmount)
            ->setSubtotal($orderSubtotal)
            ->setBaseSubtotal($orderBaseSubtotal)
            ->setBaseSubtotalInclTax($orderBaseSubtotalInclTax)
            ->setSubtotalInclTax($orderSubtotalInclTax)
            ->setBaseGrandTotal($orderBaseGrandTotal)
            ->setGrandTotal($orderGrandTotal)
            ->setBaseShippingAmount($orderBaseShippingAmount)
            ->setShippingAmount($orderShippingAmount)
            ->setBaseShippingInclTax($orderBaseShippingInclTax)
            ->setShippingInclTax($orderShippingInclTax)
            ->setBaseDiscountAmount($orderBaseDiscountAmount)
            ->setDiscountAmount($orderDiscountAmount)
            ->setTotalQtyOrdered($orderedTotalQty)
            ->setEcSurchargeAmount($orderedSurchargeAmount);
        $quote->collectTotals();

        //  dump($origOrder->debug(), $quote->debug());exit;


    }

    /**
     * @param CoreOrder|null $origOrder
     * @return DataObject
     */
    public function getPaidInvoicesTotals(CoreOrder $origOrder = null)
    {
        $invoiceCollections = $origOrder->getInvoiceCollection();
        $invoicedTotals = new DataObject();

        $invoicedTotalsData = [
            "base_grand_total" => 0,
            "shipping_tax_amount" => 0,
            "tax_amount" => 0,
            "base_tax_amount" => 0,
            "base_shipping_tax_amount" => 0,
            "base_discount_amount" => 0,
            "grand_total" => 0,
            "shipping_amount" => 0,
            "subtotal_incl_tax" => 0,
            "base_subtotal_incl_tax" => 0,
            "base_shipping_amount" => 0,
            "total_qty" => 0,
            "base_to_global_rate" => 0,
            "subtotal" => 0,
            "base_subtotal" => 0,
            "discount_amount" => 0,
            "discount_tax_compensation_amount" => 0,
            "base_discount_tax_compensation_amount" => 0,
            "shipping_discount_tax_compensation_amount" => 0,
            "base_shipping_discount_tax_compensation_amnt" => 0,
            "shipping_incl_tax" => 0,
            "base_shipping_incl_tax" => 0,
            "ec_surcharge_amount" => 0
        ];

        if ($invoiceCollections && count($invoiceCollections) > 0) {
            foreach ($invoiceCollections as $invoice) {

                $invoicedTotalsData["base_grand_total"] += (float)$invoice->getbase_grand_total();
                $invoicedTotalsData["shipping_tax_amount"] += (float)$invoice->getshipping_tax_amount();
                $invoicedTotalsData["tax_amount"] += (float)$invoice->gettax_amount();
                $invoicedTotalsData["base_tax_amount"] += (float)$invoice->getbase_tax_amount();
                $invoicedTotalsData["base_shipping_tax_amount"] += (float)$invoice->getbase_shipping_tax_amount();
                $invoicedTotalsData["base_discount_amount"] += (float)$invoice->getbase_discount_amount();
                $invoicedTotalsData["grand_total"] += (float)$invoice->getgrand_total();
                $invoicedTotalsData["shipping_amount"] += (float)$invoice->getshipping_amount();
                $invoicedTotalsData["subtotal_incl_tax"] += (float)$invoice->getsubtotal_incl_tax();
                $invoicedTotalsData["base_subtotal_incl_tax"] += (float)$invoice->getbase_subtotal_incl_tax();
                $invoicedTotalsData["base_shipping_amount"] += (float)$invoice->getbase_shipping_amount();
                $invoicedTotalsData["total_qty"] += (float)$invoice->gettotal_qty();
                $invoicedTotalsData["base_to_global_rate"] += (float)$invoice->getbase_to_global_rate();
                $invoicedTotalsData["subtotal"] += (float)$invoice->getsubtotal();
                $invoicedTotalsData["base_subtotal"] += (float)$invoice->getbase_subtotal();
                $invoicedTotalsData["discount_amount"] += (float)$invoice->getdiscount_amount();
                $invoicedTotalsData["discount_tax_compensation_amount"] += (float)$invoice->getdiscount_tax_compensation_amount();
                $invoicedTotalsData["base_discount_tax_compensation_amount"] += (float)$invoice->getbase_discount_tax_compensation_amount();
                $invoicedTotalsData["shipping_discount_tax_compensation_amount"] += (float)$invoice->getshipping_discount_tax_compensation_amount();
                $invoicedTotalsData["base_shipping_discount_tax_compensation_amnt"] += (float)$invoice->getbase_shipping_discount_tax_compensation_amnt();
                $invoicedTotalsData["shipping_incl_tax"] += (float)$invoice->getshipping_incl_tax();
                $invoicedTotalsData["base_shipping_incl_tax"] += (float)$invoice->getbase_shipping_incl_tax();
                $invoicedTotalsData["ec_surcharge_amount"] += (float)$invoice->getec_surcharge_amount();

            }
        }
        $invoicedTotals->setData($invoicedTotalsData);

        return $invoicedTotals;

    }

    /**
     * @param $quoteItem
     * @param $oldOrderModel
     * @return bool
     */
    public function compareOrderItems($quoteItem = null, $oldOrderModel = null)
    {
        $oldOrderItems = $oldOrderModel->getAllVisibleItems();
        $isSameItem = false;
        foreach ($oldOrderItems as $oldOrderItem) {
            $oldOrderItemProductId = $oldOrderItem->getProductId();
            $oldOrderItemSku = $oldOrderItem->getSku();
            $oldOrderItemQtyOrdered = (float)$oldOrderItem->getQtyOrdered();
            $oldOrderItemQty = (float)$oldOrderItem->getQty();

            $newOrderItemProductId = $quoteItem->getProductId();
            $newOrderItemSku = $quoteItem->getSku();
            $newOrderItemQtyOrdered = (float)$quoteItem->getQtyOrdered();
            $newOrderItemQty = (float)$quoteItem->getQty();

            if (
                $oldOrderItemProductId === $newOrderItemProductId &&
                $oldOrderItemQtyOrdered === $newOrderItemQtyOrdered &&
                $oldOrderItemQty === $newOrderItemQty
            ) {
                $isSameItem = true;
                return $isSameItem;
            }

        }
        return $isSameItem;
    }

    /**
     * @param $storeId
     * @param $quoteId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function renderCheckoutWebHostedProFormUrl(mixed $storeId = 0, mixed $quoteId = 0): array
    {
        $storeId = $storeId !== null ? $storeId : $this->_checkoutSession->getQuote()->getStoreId();
        $quoteId = $quoteId ?? $this->_checkoutSession->getQuote()->getId();

        $checkoutPaymentFormType = $this->_configResource->getPaymentFormType($storeId);

        /** @var  $hostedProUrlResp */
        $hostedProUrlResp = [
            'error' => true,
            'message' => __("An unknown error occurred during fetch URL."),
            "hosted_pro_url" => "",
            'payload' => [],
            'response' => [
                "ebiz_hosted_pro_url" => "",
                "error_message" => __("An unknown error occurred during fetch URL."),
                "payment_internal_id" => ""
            ]
        ];

        if ($checkoutPaymentFormType && $checkoutPaymentFormType === "2") {
            if (!$quoteId) {
                $quoteId = $this->_checkoutSession->getQuote()->getId();
            }
            $hostedProUrlResp = $this->prepareCheckoutWebHostedFormUrl($storeId, $quoteId);
        }
        return $hostedProUrlResp;
    }

    /**
     * Prepare Checkout WebHosted Form Url
     *
     * @param $storeId
     * @param $quoteId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function prepareCheckoutWebHostedFormUrl(mixed $storeId = 0, mixed $quoteId = 0): array
    {
        $methodType = OrderInterface::EBIZCHARGE_WEBFORM_CHECKOUT_PAYMENT_METHOD_TYPES;
        $formType = OrderInterface::EBIZCHARGE_WEBFORM_CHECKOUT_REGISTERED_USER_FORM_TYPE;
        $storeId = $storeId !== null ? $storeId : $this->_checkoutSession->getQuote()->getStoreId();
        $quoteId = $quoteId ?? $this->_checkoutSession->getQuote()->getId();
        $checkoutSessionQuote = $this->_checkoutSession->getQuote();
        $cardsTokenizeOnly = $this->_configResource->IsCardsTokenizeOnly($storeId);
        $phpSessionID = $this->_checkoutSession->getSessionId() ?? "";
        $customerId = $this->_customerSession->getCustomerId() ?? "";
        $isRecurringExists = $this->isRecurringExists($storeId);

        if (!$quoteId) {
            if ($checkoutSessionQuote->getId()) {
                $customerId = $checkoutSessionQuote->getCustomerId();
            }
        }
        if (!$this->_customerSession->isLoggedIn()) {
            $customerId = $this->_checkoutSession->getQuote()->getId();
            $quoteId = $this->_checkoutSession->getQuote()->getId();
            $formType = OrderInterface::EBIZCHARGE_WEBFORM_CHECKOUT_GUEST_USER_FORM_TYPE;
        }
        $sessionIdParams = [
            "TransactionLookupKey" => $phpSessionID,
            "PHPSESSID" => $phpSessionID,
            "customer_id" => $customerId,
            "payment_type" => OrderInterface::EBIZCHARGE_WEBFORM_PAYMENT_TYPE_CHECKOUT,
            "quote_id" => $quoteId
        ];

        if ($cardsTokenizeOnly || $isRecurringExists) {
            $formType = OrderInterface::EBIZCHARGE_WEBFORM_CHECKOUT_REGISTERED_USER_TOKENIZED_ONLY_FORM_TYPE;
            $sessionIdParams["payment_type"] = OrderInterface::EBIZCHARGE_WEBFORM_PAYMENT_TYPE_ADD_NEW_PAYMENT_METHOD;
        }
        /**
         * Callback Response URL
         */
        $callBackResponseUrl = $this->_configResource->getCheckoutWebHostedApprovedUrl($sessionIdParams);

        return $this->_soapApiModel
            ->prepareWebHostedCheckoutFormUrl(
                $customerId,
                $formType,
                $methodType,
                true,
                $callBackResponseUrl,
                $storeId,
                $quoteId
            );

    }

    /**
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isRecurringExists(mixed $storeId = "0"): bool
    {
        $isRecurringExists = false;
        $quote = $this->_checkoutSession->getQuote();
        $unRecurredItems = [];
        if ($quote && $quote->getId()) {
            $quoteItems = $quote->getAllVisibleItems();
            if ($quoteItems && count($quoteItems) > 0) {
                foreach ($quoteItems as $quoteItem) {
                    $buyRequest = $quoteItem->getBuyRequest();
                    $recData = $buyRequest->getRecurring() ?? [];
                    $recurringData = (array)$recData;
                    if (isset($recurringData['rec_activate']) && !empty($recurringData['rec_activate']) &&
                        isset($recurringData['rec_frequency']) && !empty($recurringData['rec_frequency']))
                    {
                        $isRecurringExists = true;
                    } else {
                        $unRecurredItems[] = $quoteItem;
                    }
                }
            }
        }
        /**
         * is recurring items and normal items then the recurring will be false
         */
        if (count($unRecurredItems) > 0) {
            $isRecurringExists = false;
        }

        return $isRecurringExists;
    }

    /**
     * Prepare Recurring Quote
     *
     * @param Http $requestParams
     * @return array
     */
    public function prepareRecurringQuote(Http $requestParams): array
    {

        $quoteData = [];
        $quoteData["items"] = [];
        $totalAmount = 0;
        $surchargePercentage = 0;
        $paymentMethodId = 0;
        $surchargeAmount = 0;
        try {
            $store = $this->_configResource->getStore();
            $storeId = $store->getId();
            $adminSession = $this->adminQuoteSession;
            $quoteId = $adminSession->getQuoteId();
            $customerId = $requestParams->getParam("customer_id");
            $productId = $requestParams->getParam("product_id");
            $productQty = $requestParams->getParam("qty");
            $billingAddressId = $requestParams->getParam("addressBill");
            $shippingAddressId = $requestParams->getParam("addressShip");
            $shippingMethod = $requestParams->getParam("shipping_method");
            $customer = $this->_customerRepository->getById($customerId);
            $surchargeSettings = $this->_soapApiModel->getSurchargeSettings($storeId);
            $paymentMethod = $requestParams->getParam("payment");
            $product = $this->_productFactory->create()->load($productId);

            /** @var CartRepositoryInterface $quoteRepository */
            $quoteRepository = $this->_cartRepositoryInterface; //$objectManager->get(CartRepositoryInterface::class);
            $productRepository = $this->_productRepository;

            $store = $this->getStore();
            $quote = $this->_quoteFactory->create(); //$quoteFactory->create();
            $quote->setStore($store);
            $quote->setCurrency();
            if ($customer) {
                $quote->assignCustomer($customer);
            }

            if ($productId) {
                $product = $productRepository->get($product->getSku());
                $quote->addProduct($product, $productQty);

                if ($billingAddressId) {
                    $quoteBillingAddressParams = $this->prepareCustomerBillingAddressById($billingAddressId);
                    $quote->getBillingAddress()->addData($quoteBillingAddressParams);
                }
                if ($shippingAddressId) {
                    $quoteShippingAddressParams = $this->prepareCustomerShippingAddressById($shippingAddressId, $shippingMethod);
                    $quote->getBillingAddress()->addData($quoteShippingAddressParams);
                }
                if ($shippingMethod) {
                    $shippingAddress = $quote->getShippingAddress();
                    $shippingAddress->setCollectShippingRates(true)
                        ->collectShippingRates()
                        ->setShippingMethod($shippingMethod);
                }
                $quote->setPaymentMethod($paymentMethod);
                $quote->setInventoryProcessed(false);
                $quote->save();
                $quoteRepository->save($quote);
                $taxAmount = $quote->getShippingAddress()->getTaxAmount();

                if (is_array($paymentMethod) && count($paymentMethod) > 0) {
                    $ebizOption = isset($paymentMethod["ebzc_option"]) ? $paymentMethod["ebzc_option"] : "";
                    $ebizOptionType = isset($paymentMethod["ebzc_option_type"]) ? $paymentMethod["ebzc_option_type"] : "";

                    if ($ebizOptionType && strtolower($ebizOptionType) === strtolower(PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_CREDIT_CARD)) {
                        $customerFactory = $this->_customerFactory->create()->load($customerId);
                        $customerInternalId = $customerFactory->getEcCustInternalId();
                        $totalAmount = (float)$quote->getGrandTotal();
                        if ($ebizOption === "saved") {
                            $paymentMethodId = isset($paymentMethod["method_id"]) ? $paymentMethod["method_id"] : "";
                            $paymentObj = explode("||", $paymentMethodId);
                            $paymentMethodId = isset($paymentObj[0]) ? $paymentObj[0] : 0;
                            $paymentMethodName = isset($paymentObj[1]) ? $paymentObj[1] : "";
                        }
                        $paymentMethodCode = PaymentInterface::CODE;
                        $quote->getPayment()->setMethod($paymentMethodCode);
                    }
                }
                if ($this->surchargeModel->isSurchargeEnabled()) {
                    /** Surcharge API params */
                    $surchargeParams = [
                        'amount' => (float)$quote->getGrandTotal(),
                        'cardNumber' => $paymentMethod['cc_number'] ?? null,
                        'cardZipCode' => $paymentMethod['avs_zip'] ?? null,
                        'customerInternalId' => $customerInternalId ?? null,
                        'paymentMethodId' => $paymentMethodId ?? null
                    ];
                    $surchargeResponse = [];
                    if ($surchargeParams["cardNumber"] || $surchargeParams["paymentMethodId"]) {
                        $surchargeResponse = $this->surchargeModel->calculateSurcharge($surchargeParams);
                    }
                    if ($surchargeResponse && count($surchargeResponse) > 0) {
                        $surchargePercentage = isset($surchargeSettings["surchargePercentage"]) ? (float)$surchargeSettings["surchargePercentage"] : 0;
                        //  $surchargeAmount = round(($quote->getGrandTotal() * $surchargePercentage) / 100, 2);
                        $surchargeAmount = isset($surchargeResponse["surchargeAmount"]) ? $surchargeResponse["surchargeAmount"] : 0;
                    }
                }
                $quoteData = $quote->getData();
                $shippingAmount = $shippingAddress->getShippingAmount() ?? 0;
                $quote->setEcSurchargeAmount($surchargeAmount)
                    ->setEcSurchargePercentage($surchargePercentage)
                    ->setShippingAmount($shippingAmount)
                    ->save();

                $quoteData["tax_amount"] = $taxAmount;
                $quoteData["shipping_amount"] = $shippingAmount;
                $quoteData["ec_surcharge_amount"] = $surchargeAmount;
                $quoteData["ec_surcharge_percentage"] = $surchargePercentage;
                $customerGroup = $customer->getGroupId();
                $quoteGrandTotals = $quote->getGrandTotal();

                if (count($quote->getAllVisibleItems()) > 0) {
                    foreach ($quote->getAllVisibleItems() as $quoteItem) {
                        if (!$quoteItem || !$quoteItem->getId()) continue;
                        $product = $quoteItem->getProduct();
                        $taxClassId = $product->getTaxClassId();
                        $taxPercentage = $this->getTaxPercentageByTaxClassId($taxClassId, $quote->getShippingAddress(), $customerGroup, $storeId);
                        $quoteItem->setEcSurchargeAmount($surchargeAmount);
                        $quoteItem->setEcShippingAmount($shippingAmount);
                        $quoteItem->save();

                        $quoteData["items"][] = $quoteItem->getData();
                    }
                }
            }

        } catch (\Exception $ex) {
            $this->_ebizchargeLogger->addCritical(__("Exception during prepare quote. Error: " . $ex->getMessage()));
            // dump($ex->getMessage());
        }

        return $quoteData;
    }

    /**
     * @param $addressId *
     * @return array|AddressInterface
     */
    public function prepareCustomerBillingAddressById($addressId = null): array|AddressInterface
    {
        $quoteAddress = [];
        $address = $this->_addressFactory->create()->load($addressId);
        if ($address) {
            [
                'firstname' => $address->getFirstname(),
                'lastname' => $address->getLastname(),
                'street' => $address->getStreet(),
                'city' => $address->getCity(),
                'postcode' => $address->getPostcode(),
                'telephone' => $address->getTelephone(),
                'country_id' => $address->getCountryId(),
                'region' => $address->getRegion()
            ];
        }
        return $quoteAddress;
    }

    /**
     * @param $addressId
     * @param $shippingMethod
     * @return array|AddressInterface
     */
    public function prepareCustomerShippingAddressById(
        mixed $addressId = null,
        mixed $shippingMethod = ""): array|AddressInterface
    {
        $quoteAddress = [];
        $address = $this->_addressFactory->create()->load($addressId);
        if ($address) {
            [
                'firstname' => $address->getFirstname(),
                'lastname' => $address->getLastname(),
                'street' => $address->getStreet(),
                'city' => $address->getCity(),
                'postcode' => $address->getPostcode(),
                'telephone' => $address->getTelephone(),
                'country_id' => $address->getCountryId(),
                'region' => $address->getRegion(),
                'shipping_method' => $shippingMethod,
                'collect_shipping_rates' => true
            ];
        }
        return $quoteAddress;
    }

    /**
     * Set Ec Surcharge Percentage
     *
     * @param float|null $surchargePercentage
     * @return OrderInterface
     */
    public function setEcSurchargePercentage($surchargePercentage)
    {
        return $this->setData(SurchargeInterface::EC_SURCHARGE_PERCENTAGE, $surchargePercentage);
    }

    /**
     * @param $taxClassId
     * @param $shippingAddress
     * @param $customerGroupId
     * @param $storeId
     * @return float|int
     */
    public function getTaxPercentageByTaxClassId(
        $taxClassId = 0,
        $shippingAddress = null,
        $customerGroupId = null,
        $storeId = 0): float|int
    {

        $taxRateRequest = $this->taxCalculation->getRateRequest(
            $shippingAddress,
            null,
            $customerGroupId,
            $storeId
        );
        $taxRateRequest->setProductClassId($taxClassId);
        $taxRate = $this->taxCalculation->getRate($taxRateRequest);

        $taxPercentage = $taxRate * 100;
        return $taxPercentage;
    }

    /**
     * @param $addressId
     * @param $customerId
     * @return array|AddressInterface
     */
    public function prepareQuoteAddress($addressId = null, $customerId = null)
    {
        $address = $this->_addressFactory->create()->load($addressId);
        $quoteAddress = [];
        if ($address) {
            $quoteAddress = $this->_quoteAddressInterface
                ->setCustomerId($customerId)
                ->setCountryId($address->getCountryId())
                ->setFirstname($address->getFirstname())
                ->setLastname($address->getLastname())
                ->setStreet($address->getStreet())
                ->setCity($address->getCity())
                ->setRegion($address->getRegion())
                ->setPostcode($address->getPostcode())
                ->setCountryId($address->getCountryId())
                ->setTelephone($address->getTelephone());
        }

        return $quoteAddress;
    }

    /**
     * @param string $ecSoftwareId
     * @return Order
     */
    private function setEcSoftwareId(string $ecSoftwareId)
    {
        return $this->setData(OrderInterface::EBIZCHARGE_SOFTWARE_ID, $ecSoftwareId);
    }


}

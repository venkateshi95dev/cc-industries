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

use Ebizcharge\Ebizcharge\Api\Data\CustomerInterface;
use Ebizcharge\Ebizcharge\Api\Data\PaymentInterface;
use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Ebizcharge\Ebizcharge\Api\Data\SoapApiModelInterface;
use Ebizcharge\Ebizcharge\Api\Data\SurchargeInterface;
use Ebizcharge\Ebizcharge\Api\Data\SyncAssetsInterface;
use Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface;
use Ebizcharge\Ebizcharge\Block\Customer\Account\PaymentHistory;
use Ebizcharge\Ebizcharge\Helper\Data;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\ConfigFactory as EbizConfigFactory;
use Ebizcharge\Ebizcharge\Model\PaymentHistory as PaymentHistoryModel;
use Ebizcharge\Ebizcharge\Model\PaymentHistory\Collection as PaymentHistoryCollection;
use Exception;
use Magento\Backend\Model\Session\Quote as BackendQuote;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface as CustomerInterfaceAlias;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\AccountConfirmation;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\Config\Share;
use Magento\Customer\Model\Customer as customerModel;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Address\CollectionFactory;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResourceModel;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Customer\Model\Session;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Eav\Model\Config as ConfigModel;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Math\Random;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\LoginAsCustomerAssistance\Model\ResourceModel\SaveLoginAsCustomerAssistanceAllowed;
use Magento\Payment\Model\Config as PaymentConfig;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use SoapClient;
use SoapFault;
use stdClass;

/**
 * Customer Model Class
 *
 * Class Customer
 */
class Customer extends customerModel
{

    /**
     * @var array
     */
    public array $paymentMethodProfile = [];
    /**
     * @var SyncAssetsFactory
     */
    protected $_syncAssetsFactory;
    /**
     * @var EbizchargeLogger
     */
    protected $_ebizchargeLogger;
    /**
     * @var Registry
     */
    protected $_registry;
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var ConfigModel
     */
    protected $_config;
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var CustomerResourceModel
     */
    protected $_resource;
    /**
     * @var Share
     */
    protected $_configShare;
    /**
     * @var AddressFactory
     */
    protected $_addressFactory;
    /**
     * @var CollectionFactory
     */
    protected $_addressesFactory;
    /**
     * @var TransportBuilder
     */
    protected $_transportBuilder;
    /**
     * @var GroupRepositoryInterface
     */
    protected $_groupRepository;
    /**
     * @var EncryptorInterface
     */
    protected $_encryptor;
    /**
     * @var DateTime
     */
    protected $_dateTime;
    /**
     * @var CustomerInterfaceFactory
     */
    protected CustomerInterfaceFactory $_customerDataFactory;
    /**
     * @var DataObjectProcessor
     */
    protected $_dataObjectProcessor;
    /**
     * @var DataObjectHelper
     */
    protected $_dataObjectHelper;
    /**
     * @var CustomerMetadataInterface
     */
    protected CustomerMetadataInterface $_metadataService;
    /**
     * @var IndexerRegistry
     */
    protected IndexerRegistry $_indexerRegistry;
    /**
     * @var AbstractDb|null
     */
    protected $_resourceCollection;
    /**
     * @var AccountConfirmation|null
     */
    protected ?AccountConfirmation $_ccountConfirmation;
    /**
     * @var Random|null
     */
    protected ?Random $_mathRandom;
    /**
     * @var CustomerInterfaceFactory
     */
    protected CustomerInterfaceFactory $_customerInterfaceFactory;
    /**
     * @var CustomerRepositoryInterface
     */
    protected CustomerRepositoryInterface $_customerRepository;
    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;
    /**
     * @var CountryFactory
     */
    protected CountryFactory $_countryFactory;
    /**
     * @var RegionFactory
     */
    protected RegionFactory $_regionFactory;
    /**
     * @var EbizConfigFactory
     */
    protected EbizConfigFactory $_ebizConfigFactory;
    /**
     * @var ShellCommand
     */
    protected ShellCommand $_shellCommand;
    /**
     * @var TranApiFactory
     */
    protected TranApiFactory $tranApiFactory;
    /**
     * @var SoapClient|null
     */
    protected ?SoapClient $_soapClient;
    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $_collectionFactory;
    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $_searchCriteriaBuilder;
    /**
     * @var Collection
     */
    protected Collection $_customerCollection;
    /**
     * @var PaymentConfig
     */
    protected PaymentConfig $_paymentConfig;
    /**
     * @var FilterBuilder
     */
    protected FilterBuilder $_filterBuilder;
    /**
     * @var PaymentHistory
     */
    protected PaymentHistory $_paymentHistory;
    /**
     * @var DataObjectFactory
     */
    protected DataObjectFactory $_dataObjectFactory;
    /**
     * @var PaymentHistoryCollection
     */
    protected PaymentHistoryCollection $_paymentHistoryCollection;
    /**
     * @var DateTime\TimezoneInterface
     */
    protected DateTime\TimezoneInterface $_timezoneInterface;
    /**
     * @var AddressRepositoryInterface
     */
    protected AddressRepositoryInterface $_addressRepository;
    /**
     * @var PriceHelper
     */
    protected PriceHelper $_priceHelper;
    /**
     * @var \Ebizcharge\Ebizcharge\Model\PaymentHistory
     */
    protected \Ebizcharge\Ebizcharge\Model\PaymentHistory $_paymentHistoryModel;
    /**
     * @var RecurringRepositoryInterface
     */
    protected RecurringRepositoryInterface $_recurringRepositoryInterface;
    /**
     * @var OrderFactory
     */
    protected OrderFactory $_orderFactory;
    /**
     * @var RemoteAddress
     */
    protected RemoteAddress $_remoteAddress;
    /**
     * @var Payment
     */
    protected Payment $_paymentModel;
    /**
     * @var Session
     */
    protected Session $_customerSession;
    /**
     * @var CheckoutSession
     */
    protected CheckoutSession $_checkoutSession;
    /**
     * @var BackendQuote
     */
    protected BackendQuote $_backendQuoteSession;


    /**
     * @var array
     */
    protected array $_downloadedCustomerEmails = [];

    /**
     * @var QuoteFactory
     */
    protected QuoteFactory $_quoteFactory;
    /**
     * @var CountryCollectionFactory
     */
    protected CountryCollectionFactory $_countryCollectionFactory;
    /**
     * @var SaveLoginAsCustomerAssistanceAllowed
     */
    protected SaveLoginAsCustomerAssistanceAllowed $saveLoginAsCustomerAssistanceAllowed;
    /**
     * @var UrlInterface
     */
    protected UrlInterface $urlInterface;
    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;
    /**
     * @var SessionManagerInterface
     */
    private SessionManagerInterface $sessionManagerInterface;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     * @param ConfigModel $config
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerResourceModel $resource
     * @param Share $configShare
     * @param AddressFactory $addressFactory
     * @param CollectionFactory $addressesFactory
     * @param TransportBuilder $transportBuilder
     * @param GroupRepositoryInterface $groupRepository
     * @param EncryptorInterface $encryptor
     * @param AddressRepositoryInterface $addressRepository
     * @param DateTime $dateTime
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param DataObjectProcessor $dataObjectProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param CustomerMetadataInterface $metadataService
     * @param IndexerRegistry $indexerRegistry
     * @param SyncAssetsFactory $syncAssetsFactory
     * @param EbizchargeLogger $ebizchargeLogger
     * @param CustomerInterfaceFactory $customerInterfaceFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CustomerFactory $customerFactory
     * @param CountryFactory $countryFactory
     * @param RegionFactory $regionFactory
     * @param FilterBuilder $filterBuilder
     * @param TimezoneInterface $timezoneInterface
     * @param TranApiFactory $tranApiFactory
     * @param ConfigFactory $ebizConfigFactory
     * @param ShellCommand $shellCommand
     * @param Collection $customerCollectionFactory
     * @param CollectionFactory $collectionFactory
     * @param PaymentHistory $paymentHistory
     * @param RecurringRepositoryInterface $recurringRepository
     * @param PaymentHistoryCollection $paymentHistoryCollection
     * @param \Ebizcharge\Ebizcharge\Model\PaymentHistory $paymentHistoryModel
     * @param PaymentConfig $paymentConfig
     * @param Payment $paymentModel
     * @param PriceHelper $priceHelper
     * @param OrderFactory $orderFactory
     * @param DataObjectFactory $dataObjectFactory
     * @param RemoteAddress $remoteAddress
     * @param Session $customerSession
     * @param CheckoutSession $checkoutSession
     * @param BackendQuote $backendQuoteSession
     * @param QuoteFactory $quoteFactory
     * @param SessionManagerInterface $sessionManagerInterface
     * @param CountryCollectionFactory $countryCollectionFactory
     * @param SaveLoginAsCustomerAssistanceAllowed $saveLoginAsCustomerAssistanceAllowed
     * @param UrlInterface $urlInterface
     * @param RequestInterface $request
     * @param AbstractDb|null $resourceCollection
     * @param AccountConfirmation|null $accountConfirmation
     * @param Random|null $mathRandom
     * @param array $data
     */
    public function __construct(
        Context                              $context,
        Registry                             $registry,
        StoreManagerInterface                $storeManager,
        ConfigModel                          $config,
        ScopeConfigInterface                 $scopeConfig,
        CustomerResourceModel                $resource,
        Share                                $configShare,
        AddressFactory                       $addressFactory,
        CollectionFactory                    $addressesFactory,
        TransportBuilder                     $transportBuilder,
        GroupRepositoryInterface             $groupRepository,
        EncryptorInterface                   $encryptor,
        AddressRepositoryInterface           $addressRepository,
        DateTime                             $dateTime,
        CustomerInterfaceFactory             $customerDataFactory,
        DataObjectProcessor                  $dataObjectProcessor,
        DataObjectHelper                     $dataObjectHelper,
        CustomerMetadataInterface            $metadataService,
        IndexerRegistry                      $indexerRegistry,
        SyncAssetsFactory                    $syncAssetsFactory,
        EbizchargeLogger                     $ebizchargeLogger,
        CustomerInterfaceFactory             $customerInterfaceFactory,
        CustomerRepositoryInterface          $customerRepository,
        SearchCriteriaBuilder                $searchCriteriaBuilder,
        CustomerFactory                      $customerFactory,
        CountryFactory                       $countryFactory,
        RegionFactory                        $regionFactory,
        FilterBuilder                        $filterBuilder,
        DateTime\TimezoneInterface           $timezoneInterface,
        TranApiFactory                       $tranApiFactory,
        EbizConfigFactory                    $ebizConfigFactory,
        ShellCommand                         $shellCommand,
        Collection                           $customerCollectionFactory,
        CollectionFactory                    $collectionFactory,
        PaymentHistory                       $paymentHistory,
        RecurringRepositoryInterface         $recurringRepository,
        PaymentHistoryCollection             $paymentHistoryCollection,
        PaymentHistoryModel                  $paymentHistoryModel,
        PaymentConfig                        $paymentConfig,
        Payment                              $paymentModel,
        PriceHelper                          $priceHelper,
        OrderFactory                         $orderFactory,
        DataObjectFactory                    $dataObjectFactory,
        RemoteAddress                        $remoteAddress,
        Session                              $customerSession,
        CheckoutSession                      $checkoutSession,
        BackendQuote                         $backendQuoteSession,
        QuoteFactory                         $quoteFactory,
        SessionManagerInterface              $sessionManagerInterface,
        CountryCollectionFactory             $countryCollectionFactory,
        SaveLoginAsCustomerAssistanceAllowed $saveLoginAsCustomerAssistanceAllowed,
        UrlInterface                         $urlInterface,
        RequestInterface                     $request,
        AbstractDb                           $resourceCollection = null,
        AccountConfirmation                  $accountConfirmation = null,
        Random                               $mathRandom = null,
        array                                $data = []
    )
    {
        /** parent Construct */
        parent::__construct(
            $context,
            $registry,
            $storeManager,
            $config,
            $scopeConfig,
            $resource,
            $configShare,
            $addressFactory,
            $addressesFactory,
            $transportBuilder,
            $groupRepository,
            $encryptor,
            $dateTime,
            $customerDataFactory,
            $dataObjectProcessor,
            $dataObjectHelper,
            $metadataService,
            $indexerRegistry,
            $resourceCollection,
            $data,
            $accountConfirmation,
            $mathRandom
        );

        /** @var $_syncAssetsFactory */
        $this->_syncAssetsFactory = $syncAssetsFactory;
        /** @var $_ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var $_registry */
        $this->_registry = $registry;
        /** @var $_storeManager */
        $this->_storeManager = $storeManager;
        /** @var $_config */
        $this->_config = $config;
        /** @var $_scopeConfig */
        $this->_scopeConfig = $scopeConfig;
        /** @var $_resource */
        $this->_resource = $resource;
        /** @var $_configShare */
        $this->_configShare = $configShare;
        /** @var $_addressFactory */
        $this->_addressFactory = $addressFactory;
        /** @var $_addressesFactory */
        $this->_addressesFactory = $addressesFactory;
        /** @var $_transportBuilder */
        $this->_transportBuilder = $transportBuilder;
        /** @var $_groupRepository */
        $this->_groupRepository = $groupRepository;
        /** @var $_addressRepository */
        $this->_addressRepository = $addressRepository;
        /** @var $_data */
        $this->_data = $data;
        /** @var $_encryptor */
        $this->_encryptor = $encryptor;
        /** @var $_dateTime */
        $this->_dateTime = $dateTime;
        /** @var $_customerDataFactory */
        $this->_customerDataFactory = $customerDataFactory;
        /** @var $_dataObjectProcessor */
        $this->_dataObjectProcessor = $dataObjectProcessor;
        /** @var $_dataObjectHelper */
        $this->_dataObjectHelper = $dataObjectHelper;
        /** @var $_metadataService */
        $this->_metadataService = $metadataService;
        /** @var $_indexerRegistry */
        $this->_indexerRegistry = $indexerRegistry;
        /** @var $_resourceCollection */
        $this->_resourceCollection = $resourceCollection;
        /** @var $_ccountConfirmation */
        $this->_ccountConfirmation = $accountConfirmation;
        /** @var $_mathRandom */
        $this->_mathRandom = $mathRandom;
        /** @var $_customerInterfaceFactory */
        $this->_customerInterfaceFactory = $customerInterfaceFactory;
        /** @var $_customerRepository */
        $this->_customerRepository = $customerRepository;
        /** @var $_customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var $_countryFactory */
        $this->_countryFactory = $countryFactory;
        /** @var $_regionFactory */
        $this->_regionFactory = $regionFactory;
        /** @var $_configModelResource */
        /** @var  $_ebizConfigFactory */
        $this->_ebizConfigFactory = $ebizConfigFactory;
        /** @var $_shellCommand */
        $this->_shellCommand = $shellCommand;
        /** @var $_soapApiModel */
        $this->tranApiFactory = $tranApiFactory;

        /** @var $_collectionFactory */
        $this->_collectionFactory = $collectionFactory;
        /** @var $_searchCriteriaBuilder */
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        /** @var $_customerCollection */
        $this->_customerCollection = $customerCollectionFactory;
        /** @var $_paymentConfig */
        $this->_paymentConfig = $paymentConfig;
        /** @var $_filterBuilder */
        $this->_filterBuilder = $filterBuilder;
        /** @var $_paymentHistory */
        $this->_paymentHistory = $paymentHistory;
        /** @var $_dataObjectFactory */
        $this->_dataObjectFactory = $dataObjectFactory;
        /** @var $_paymentHistoryCollection */
        $this->_paymentHistoryCollection = $paymentHistoryCollection;
        /** @var $_timezoneInterface */
        $this->_timezoneInterface = $timezoneInterface;
        /** @var $_priceHelper */
        $this->_priceHelper = $priceHelper;
        /** @var $_paymentHistoryModel */
        $this->_paymentHistoryModel = $paymentHistoryModel;
        /** @var $_recurringRepositoryInterface */
        $this->_recurringRepositoryInterface = $recurringRepository;
        /** @var $_orderFactory */
        $this->_orderFactory = $orderFactory;
        /** @var $_remoteAddress */
        $this->_remoteAddress = $remoteAddress;
        /** @var $_paymentModel */
        $this->_paymentModel = $paymentModel;
        /** @var $_customerSession */
        $this->_customerSession = $customerSession;
        /** @var $_checkoutSession */
        $this->_checkoutSession = $checkoutSession;
        /** @var $_backendQuoteSession */
        $this->_backendQuoteSession = $backendQuoteSession;

        /** @var $_quoteFactory */
        $this->_quoteFactory = $quoteFactory;
        /** @var $sessionManagerInterface */
        $this->sessionManagerInterface = $sessionManagerInterface;
        /**
         * Country Collection Factory
         */
        $this->_countryCollectionFactory = $countryCollectionFactory;

        /** save login as customer assistance **/
        $this->saveLoginAsCustomerAssistanceAllowed = $saveLoginAsCustomerAssistanceAllowed;

        $this->urlInterface = $urlInterface;
        /** @var $request */
        $this->request = $request;

    }

    /**
     * @param $addressID
     * @return AddressInterface|null
     * @throws LocalizedException
     */
    public function loadCustomerAddress($addressID = null)
    {
        $customerAddress = null;
        try {
            $customerAddress = $this->_addressRepository->getById($addressID);
        } catch (NoSuchEntityException $e) {
            $this->_ebizchargeLogger->addCritical(__("Could not found the address against provided addressID " .
                $addressID . " Error: " . $e->getMessage()));
        }
        return $customerAddress;
    }

    /**
     * Ebizcharge Customers
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function syncCustomersToEbizcharge()
    {

        /** @var  $syncAssetsFactory */
        $syncAssetsFactory = $this->_syncAssetsFactory->create();
        /**
         * Get Latest Ebizcharge Customers
         */
        $ebizchargeLatestCustomers = $syncAssetsFactory->getLatestEbizchargeCustomers();
        return $ebizchargeLatestCustomers;
    }

    /**
     * Get Guest Customer
     *
     * @return bool|mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getGuestCustomer()
    {
        $store = $this->_storeManager->getStore();
        $storeId = $store->getId();

        $defaultCountry = $this->_ebizConfigFactory->create()->getDefaultCountryCode($storeId) ?
            $this->_ebizConfigFactory->create()->getDefaultCountryCode($storeId) : '*';
        $defaultCity = $this->_ebizConfigFactory->create()->getDefaultStoreCity($storeId) ?
            $this->_ebizConfigFactory->create()->getDefaultStoreCity($storeId) : '*';
        $defaultZipCode = $this->_ebizConfigFactory->create()->getDefaultStorePostCode($storeId) ?
            $this->_ebizConfigFactory->create()->getDefaultStorePostCode($storeId) : '*';
        $defaultState = $this->_ebizConfigFactory->create()->getDefaultStoreRegionId($storeId) ?
            $this->_ebizConfigFactory->create()->getDefaultStoreRegionId($storeId) : '*';
        $defaultPhone = $this->_ebizConfigFactory->create()->getDefaultStorePhone($storeId) ?
            $this->_ebizConfigFactory->create()->getDefaultStorePhone($storeId) : '*';
        $defaultAddress = $this->_ebizConfigFactory->create()->getDefaultStoreAddress($storeId) ?
            $this->_ebizConfigFactory->create()->getDefaultStoreAddress($storeId) : '*';

        /** @var $customer */
        $customer = $this->loadByEmail(CustomerInterface::GUEST_CUSTOMER_EMAIL);
        /** if customer id */
        if ($customer && $customer->getId()) {
            $customerId = $customer->getId();
            return $customerId;
        } else {

            /** @var  $ebizchargeCustomerData */
            $ebizchargeCustomerData = [
                'firstname' => CustomerInterface::GUEST_CUSTOMER_FIRST_NAME,
                'lastname' => CustomerInterface::GUEST_CUSTOMER_LAST_NAME,
                'email' => CustomerInterface::GUEST_CUSTOMER_EMAIL,
                'company' => CustomerInterface::GUEST_DEAFULT_COMPANY,
                'phone' => $defaultPhone,
                'software_id' => CustomerInterface::GUEST_DEAFULT_COMPANY,
                'ebiz_sync_status' => true,
                'ebiz_internal_id' => '',
                'ebiz_customer_id' => '',
                'ebiz_customer_token' => '',
                // phpcs:ignore
                'ebiz_customer_last_syncdate' => @$this->_syncAssetsFactory->create()
                    ->getCurrentDateTime(),
                'billing_address' => [
                    'firstname' => CustomerInterface::GUEST_CUSTOMER_FIRST_NAME,
                    'lastname' => CustomerInterface::GUEST_CUSTOMER_LAST_NAME,
                    'company' => CustomerInterface::GUEST_DEAFULT_COMPANY,
                    'phone' => $defaultPhone,
                    'street1' => $defaultAddress,
                    'street2' => $defaultAddress,
                    'country' => $defaultCountry,
                    'city' => $defaultCity,
                    'state' => $defaultState,
                    'zipcode' => $defaultZipCode,
                    'is_default' => true,
                ],
                'shipping_address' => [
                    'firstname' => CustomerInterface::GUEST_CUSTOMER_FIRST_NAME,
                    'lastname' => CustomerInterface::GUEST_CUSTOMER_LAST_NAME,
                    'company' => CustomerInterface::GUEST_DEAFULT_COMPANY,
                    'phone' => $defaultPhone,
                    'street1' => $defaultAddress,
                    'street2' => $defaultAddress,
                    'country' => $defaultCountry,
                    'city' => $defaultCity,
                    'state' => $defaultState,
                    'zipcode' => $defaultZipCode,
                    'is_default' => true
                ],
                'ebiz_custom_fields' => [],
                'ebiz_division_id' => '',
                'ebiz_created_at' => '',
                // phpcs:ignore
                'ebiz_modified_at' => @$this->_syncAssetsFactory->create()->getCurrentDateTime()
            ];

            /** save Ebizcharge Customer To Local */
            $this->_ebizchargeLogger->addInfo(__("Saving the Guest as customer "));

            return $this->saveEbizchargeCustomerToLocal($ebizchargeCustomerData);
        }
    }

    /**
     * Load By Email
     *
     * @param string $customerEmail
     * @return Customer
     * @throws LocalizedException
     */
    public function loadByEmail($customerEmail)
    {
        $this->setWebsiteId($this->getStore()->getWebsiteId());
        $this->setStore($this->getStore());
        return parent::loadByEmail($customerEmail);
    }

    /**
     * @param $ebizchargeCustomer
     * @param bool $isDownload
     * @return false|mixed
     * @throws NoSuchEntityException
     */
    public function saveEbizchargeCustomerToLocal($ebizchargeCustomer = null, bool $isDownload = false)
    {

        $saveCustomerResp = [
            "error" => true,
            "status" => "error",
            "customer_id" => "",
            "message" => __("Error during saving Gateway customer locally. ")
        ];

        if (!$ebizchargeCustomer) {
            $saveCustomerResp["message"] = __("Error during saving locally, EBizCharge customer is empty.");
            return $saveCustomerResp;
        }
        /**
         * if Customer Download and Module is not active
         */
        $isDownloadCustomerActive = $this->downloadCustomerIsActive();
        if (!$isDownloadCustomerActive) {
            $saveCustomerResp["message"] = __("Download customers is not active from admin side.");
            return $saveCustomerResp;
        }
        /** @var $store */
        $store = $this->_storeManager->getStore();
        $storeId = $this->_storeManager->getStore()->getId();
        $websiteId = $this->_storeManager->getWebsite()->getId();
        $groupId = $this->_storeManager->getGroup()->getId();
        $envPrefix = $this->_ebizConfigFactory->create()->getEnvoirnmentPrefix($storeId);
        $divisionId = $this->_ebizConfigFactory->create()->getDivisionID($storeId) ??
            $this->tranApiFactory->create()->getDivisionId();
        $softwareId = $this->tranApiFactory->create()->getSoftwareId();
        $customerId = 0;

        try {

            $ebizchargeCustomer = (array)$ebizchargeCustomer;
            /** check if customer exists locally */
            $ebizchargeCustomerInternalId = $ebizchargeCustomer["CustomerInternalId"] ?? "";
            $ebizCustomerId = $ebizchargeCustomer["CustomerId"] ?? "";
            $ebizCustomerToken = $ebizchargeCustomer["CustomerToken"] ?? "";
            $ebizCustomerDivisionId = $ebizchargeCustomer["DivisionId"] ?? "";
            $ebizCustomerSoftwareId = $ebizchargeCustomer["SoftwareId"] ?? "";
            $ebizCustomerLastSyncDate = $ebizchargeCustomer["DateTimeModified"] ?? "";

            if (empty($ebizchargeCustomerInternalId) || empty($ebizCustomerId)) {
                $saveCustomerResp["message"] = __("CustomerID or InternalID doest not exists.");
                return $saveCustomerResp;
            }
            $ebizchargeCustomerData = $this->prepareEbizchargeCustomerData($ebizchargeCustomer);

            $ebizchargeCustomerBillingAddress = $ebizchargeCustomerData['billing_address'] ?? [];
            $ebizchargeCustomerShippingAddress = $ebizchargeCustomerData['shipping_address'] ?? [];
            $customerEmail = isset($ebizchargeCustomer["Email"]) ? $ebizchargeCustomer["Email"] : '';


            $modifiedAt = $ebizchargeCustomerData["ebiz_modified_at"] ??
                $this->tranApiFactory->create()->getCurrentDateTime('Y-m-d h:i:s');
            $lastSyncDateTime = $ebizchargeCustomerData["ebiz_customer_last_syncdate"] ??
                $ebizchargeCustomerData["ebiz_created_at"];

            /** @var $localCustomerId */
            $customer = $this->loadByEmail($customerEmail);

            $ebizchargeCustomerData['firstname'] = ($ebizchargeCustomerData['firstname'] !== "") ?
                $ebizchargeCustomerData['firstname'] : 'cbs';
            $ebizchargeCustomerData['lastname'] = ($ebizchargeCustomerData['lastname'] !== "") ?
                $ebizchargeCustomerData['lastname'] : '-';

            $this->sessionManagerInterface->unsIsDownload();
            $this->sessionManagerInterface->setIsDownload($isDownload);
            $ebizCustomFields = $ebizchargeCustomerData["ebiz_custom_fields"] ?? "";

            $customFields = [];
            if (count($ebizCustomFields) > 0) {
                foreach ($ebizCustomFields as $ebizCustomField) {
                    $ebizCustomField = (array)$ebizCustomField;
                    $attributeId = isset($ebizCustomField["FieldId"]) ? $ebizCustomField["FieldId"] : "";
                    $attributeValue = isset($ebizCustomField["FieldValue"]) ? $ebizCustomField["FieldValue"] : "";

                    if ($attributeId === "gender") {
                        if (strtolower($attributeValue) === "male") {
                            $attributeValue = 1;
                        } elseif (strtolower($attributeValue) === "female") {
                            $attributeValue = 2;
                        } else {
                            $attributeValue = 3;
                        }

                    }
                    $customFields[$attributeId] = $attributeValue;
                }
            }
            $customerGender = isset($customFields["gender"]) ? $customFields["gender"] : "";
            $customerDob = isset($customFields["dob"]) ? $customFields["dob"] : "";
            $customerTaxVat = isset($customFields["taxvat"]) ? $customFields["taxvat"] : "";
            $customerConfirmation = isset($customFields["confirmation"]) ? $customFields["confirmation"] : 0;


            $billingFirstName = isset($ebizchargeCustomerBillingAddress['firstname']) ? $ebizchargeCustomerBillingAddress['firstname'] : '';
            $billingLastName = isset($ebizchargeCustomerBillingAddress['lastname']) ? $ebizchargeCustomerBillingAddress['lastname'] : '';
            $billingStreet = isset($ebizchargeCustomerBillingAddress['address']) ? $ebizchargeCustomerBillingAddress['address'] : '';
            $billingPhone = isset($ebizchargeCustomerBillingAddress['phone']) ? $ebizchargeCustomerBillingAddress['phone'] : '';
            $billingState = isset($ebizchargeCustomerBillingAddress['state']) ? $ebizchargeCustomerBillingAddress['state'] : '';
            $billingCity = isset($ebizchargeCustomerBillingAddress['city']) ? $ebizchargeCustomerBillingAddress['city'] : '';

            $shippingFirstName = isset($ebizchargeCustomerShippingAddress['firstname']) ? $ebizchargeCustomerShippingAddress['firstname'] : '';
            $shippingLastName = isset($ebizchargeCustomerShippingAddress['lastname']) ? $ebizchargeCustomerShippingAddress['lastname'] : '';
            $shippingStreet = isset($ebizchargeCustomerShippingAddress['address']) ? $ebizchargeCustomerShippingAddress['address'] : '';
            $shippingPhone = isset($ebizchargeCustomerShippingAddress['phone']) ? $ebizchargeCustomerShippingAddress['phone'] : '';
            $shippingState = isset($ebizchargeCustomerShippingAddress['state']) ? $ebizchargeCustomerShippingAddress['state'] : '';
            $shippingCity = isset($ebizchargeCustomerShippingAddress['city']) ? $ebizchargeCustomerShippingAddress['city'] : '';

            /** customer Id exists  */
            if ($customer->getId()) {
                $customerId = $customer->getId();
                $customer
                    ->setFirstname($ebizchargeCustomerData['firstname'] ?? '')
                    ->setLastname($ebizchargeCustomerData['lastname'] ?? '')
                    ->setCreatedIn($ebizchargeCustomerData['software_id'] ?? $softwareId)
                    ->setFirstname($ebizchargeCustomerData['firstname'] ?? '')
                    ->setEmail($ebizchargeCustomerData['email'] ?? '')
                    ->setTelephone(isset($ebizchargeCustomerData['phone']) ? $ebizchargeCustomerData['phone'] :
                        $this->_ebizConfigFactory->create()->getDefaultStorePhone())
                    ->setCompany($ebizchargeCustomerData['company'] ?? '')
                    ->setEcCustId($ebizchargeCustomerData['ebiz_customer_id'] ?? '')
                    ->setEcCustToken($ebizchargeCustomerData['ebiz_customer_token'] ?? '')
                    ->setEcSoftwareId($ebizchargeCustomerData['software_id'] ?? $softwareId)
                    ->setEcDivisionId($ebizchargeCustomerData['ebiz_division_id'] ?? $divisionId)
                    ->setEcCustSyncStatus($ebizchargeCustomerData['ebiz_sync_status'] ?? 1)
                    ->setEcCustInternalId($ebizchargeCustomerData['ebiz_internal_id'] ?? '')
                    ->setEcCustLastSyncDate($lastSyncDateTime)
                    ->setIsDownload($isDownload);

                /**
                 * update Customer Remote assistance
                 */
                $this->saveLoginAsCustomerAssistanceAllowed->execute((int)$customerId);

                /** @var $customerId */
                $customerId = $customer->getId();
                $customerDefaultBillingAddress = $customer->getDefaultBillingAddress();
                $customerDefaultShippingAddress = $customer->getDefaultShippingAddress();


                $defaultBillingAddressId = is_object($customerDefaultBillingAddress) ?
                    $customerDefaultBillingAddress->getId() : 0;
                $defaultShippingAddressId = is_object($customerDefaultShippingAddress) ?
                    $customerDefaultShippingAddress->getId() : 0;

                $customerData = $customer->getData();
                $customerData = array_merge($customerData, $customFields);
                $customer->setData($customerData);

                $customer
                    ->setGender($customerGender)
                    ->setConfirmation($customerConfirmation)
                    ->setTaxvat($customerTaxVat)
                    ->setDob($customerDob)
                    ->setIsFromCron(true);

                /** saving customer to the database */
                $currentCustomer = $customer->save();

                /** save Customer billing Address */
                $this->saveCustomerAddress(
                    $customerId,
                    $ebizchargeCustomerBillingAddress,
                    'billing',
                    $defaultBillingAddressId,
                    $isDownload
                );

                if (
                    strtolower((string)$billingFirstName) !== strtolower((string)$shippingFirstName) ||
                    strtolower((string)$billingLastName) !== strtolower((string)$shippingLastName) ||
                    strtolower((string)$billingStreet) !== strtolower((string)$shippingStreet) ||
                    strtolower((string)$billingPhone) !== strtolower((string)$shippingPhone) ||
                    strtolower((string)$billingState) !== strtolower((string)$shippingState) ||
                    strtolower((string)$billingCity) !== strtolower((string)$shippingCity)
                ) {
                    /** save Customer Shipping Address */
                    $this->saveCustomerAddress(
                        $customerId,
                        $ebizchargeCustomerShippingAddress,
                        'shipping',
                        $defaultShippingAddressId,
                        $isDownload
                    );
                }

                $this->_ebizchargeLogger->addInfo(__("EBizCharge Customer has been updated into the database"));

                /** return current customer Id */
                $saveCustomerResp["error"] = false;
                $saveCustomerResp["status"] = "success";
                $saveCustomerResp["customer_id"] = $currentCustomer->getId();
                $saveCustomerResp["message"] = __("Success, the customer has been saved locally Customer ID:" . $currentCustomer->getId());

            } else {
                /** if customer exits update customer  */
                $hashPassword = $this->_encryptor->getHash($ebizchargeCustomerData['firstname']);
                $customerFactory = $this->_customerFactory->create();
                /** setting Customer Data  */
                $customerFactory
                    ->setWebsiteId($websiteId)
                    ->setPasswordHash($hashPassword)
                    ->setStore($this->getStore())
                    ->setWebsiteId($store->getWebsiteId())
                    ->setGroupId($store->getStoreGroupId())
                    ->setCreatedIn($ebizchargeCustomerData['software_id'] ?? $softwareId)
                    ->setFirstname($ebizchargeCustomerData['firstname'] ?? '')
                    ->setLastname($ebizchargeCustomerData['lastname'] ?? '')
                    ->setEmail($ebizchargeCustomerData['email'] ?? '')
                    ->setPasswordHash($hashPassword)
                    ->setTelephone(isset($ebizchargeCustomerData['phone']) ? $ebizchargeCustomerData['phone'] :
                        $this->_ebizConfigFactory->create()->getDefaultStorePhone())
                    ->setCompany($ebizchargeCustomerData['company'] ?? '')
                    ->setEcCustId($ebizchargeCustomerData['ebiz_customer_id'] ?? '')
                    ->setEcCustToken($ebizchargeCustomerData['ebiz_customer_token'] ?? '')
                    ->setEcSoftwareId($ebizchargeCustomerData['software_id'] ?? $softwareId)
                    ->setEcDivisionId($ebizchargeCustomerData['ebiz_division_id'] ?? $divisionId)
                    ->setEcCustSyncStatus($ebizchargeCustomerData['ebiz_sync_status'] ?? 1)
                    ->setEcCustInternalId($ebizchargeCustomerData['ebiz_internal_id'] ?? '')
                    ->setEcCustLastSyncDate($lastSyncDateTime)
                    ->setIsDownload($isDownload)
                    ->setIsFromCron(true);

                $customerData = $customerFactory->getData();
                $customerData = array_merge($customerData, $customFields);
                $customerFactory->setData($customerData);

                $customerFactory
                    ->setGender($customerGender)
                    ->setConfirmation($customerConfirmation)
                    ->setTaxvat($customerTaxVat)
                    ->setDob($customerDob);

                /** saving customer to the database */
                $currentCustomer = $customerFactory->save();

                $this->_ebizchargeLogger->addInfo(__("EBizCharge Customer has been Saved to the current database"));

                /** @var $customerId */
                $customerId = $currentCustomer->getEntityId();

                /** save Customer billing Address */
                $this->saveCustomerAddress($customerId, $ebizchargeCustomerBillingAddress, 'billing');

                if (
                    strtolower((string)$billingFirstName) !== strtolower((string)$shippingFirstName) ||
                    strtolower((string)$billingLastName) !== strtolower((string)$shippingLastName) ||
                    strtolower((string)$billingStreet) !== strtolower((string)$shippingStreet) ||
                    strtolower((string)$billingPhone) !== strtolower((string)$shippingPhone) ||
                    strtolower((string)$billingState) !== strtolower((string)$shippingState) ||
                    strtolower((string)$billingCity) !== strtolower((string)$shippingCity)
                ) {
                    /** save Customer Shipping Address */
                    $this->saveCustomerAddress($customerId, $ebizchargeCustomerShippingAddress, 'shipping');

                }

                /**
                 * update Customer Remote assistance
                 */
                $this->saveLoginAsCustomerAssistanceAllowed->execute((int)$customerId);
                $saveCustomerResp["error"] = false;
                $saveCustomerResp["status"] = "success";
                $saveCustomerResp["customer_id"] = $currentCustomer->getId();
                $saveCustomerResp["message"] = __("Success, the customer has been saved locally Customer ID:" . $currentCustomer->getId());


            }

            $this->_ebizchargeLogger->addInfo(__("EBizCharge Customer Saved to the current database"));
            $this->sessionManagerInterface->unsIsDownload();

            if ($saveCustomerResp["error"] === false) {
                /** @var  $bind */
                $bind = [
                    CustomerInterface::EBIZCHARGE_CUSTOMER_SYNC_STATUS => "1",
                    CustomerInterface::EBIZCHARGE_CUSTOMER_INTERNAL_ID => $ebizchargeCustomerInternalId,
                    CustomerInterface::EBIZCHARGE_CUSTOMER_ID => $ebizCustomerId,
                    CustomerInterface::EBIZCHARGE_DIVISION_ID => $ebizCustomerDivisionId,
                    CustomerInterface::EBIZCHARGE_SOFTWARE_ID => $ebizCustomerSoftwareId,
                    CustomerInterfaceAlias::CREATED_IN => $ebizCustomerSoftwareId,
                    CustomerInterface::EBIZCHARGE_CUSTOMER_TOKEN => $ebizCustomerToken,
                    CustomerInterface::EBIZCHARGE_CUSTOMER_LAST_SYNC_DATE => $ebizCustomerLastSyncDate
                ];
                /** updating the extra fields via Customer Resource Model */
                $this->getResource()->getConnection()->update(
                    $this->getResource()->getEntityTable(),
                    $bind,
                    $this->getResource()->getConnection()->quoteInto(
                        CustomerInterface::EBIZCHARGE_CUSTOMER_ENTITY_ID . " = ?",
                        $customerId
                    )
                );
            }

        } catch (Exception $exception) {
            $message = __("Exception occurred during creating customer error: " . $exception->getMessage());
            $this->_ebizchargeLogger->addCritical($message);
            $saveCustomerResp["message"] = $message;
        }

        return $saveCustomerResp;
    }

    /**
     * Download Customer Is Active
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function downloadCustomerIsActive()
    {
        $isDownloadCustomersActive = false;
        $storeId = $this->_ebizConfigFactory->create()->getStoreId();
        $isModuleActive = $this->_ebizConfigFactory->create()->isActive($storeId);
        // phpcs:ignore
        //  $isDownloadCustomerEnabled = $this->_ebizConfigFactory->create()->isDownlaodCustomersEnabled($storeId);

        if ($isModuleActive) {
            $isDownloadCustomersActive = true;
        } else {
            $this->_ebizchargeLogger->addCritical(__(
                "EbizCharge Hub econnect or download customers is not enabled from configuration, please enable it."
            ));
        }
        return $isDownloadCustomersActive;
    }

    /**
     * Prepare Ebizcharge Customer Data
     *
     * @param array|null $ebizchargeCustomer
     * @return array
     * @throws NoSuchEntityException
     */
    public function prepareEbizchargeCustomerData(array $ebizchargeCustomer = null): array
    {
        /** @var  $ebizchargeCustomerData */
        $ebizchargeCustomerData = [];

        if (!$ebizchargeCustomer) {
            return $ebizchargeCustomerData;
        }

        $billingAddress = isset($ebizchargeCustomer["BillingAddress"]) ?
            (array)$ebizchargeCustomer["BillingAddress"] : [];
        $shippingAddress = isset($ebizchargeCustomer["ShippingAddress"]) ?
            (array)$ebizchargeCustomer["ShippingAddress"] : [];
        $ebizchargeCustomerToken = isset($ebizchargeCustomer["CustomerToken"]) && !empty($ebizchargeCustomer["CustomerToken"]) ? $ebizchargeCustomer["CustomerToken"] : "";
        $ebizCustomerId = isset($ebizchargeCustomer["CustomerId"]) && !empty($ebizchargeCustomer["CustomerId"]) ? $ebizchargeCustomer["CustomerId"] : "";

        $store = $this->getStore();
        $storeId = $this->getStoreId();
        if (!$ebizchargeCustomerToken) {
            $ebizCustomerResult = (array)$this->getEbizCustomerById($ebizCustomerId);
            if (isset($ebizCustomerResult["GetCustomerResult"]) && is_object($ebizCustomerResult["GetCustomerResult"])) {
                $ebizCustomer = isset($ebizCustomerResult["GetCustomerResult"]) && is_object($ebizCustomerResult["GetCustomerResult"]) ? (array)$ebizCustomerResult["GetCustomerResult"] : [];
                $ebizchargeCustomerToken = isset($ebizCustomer["CustomerToken"]) && !empty($ebizCustomer["CustomerToken"]) ? $ebizCustomer["CustomerToken"] : "";
                $ebizchargeCustomer["CustomerToken"] = $ebizchargeCustomerToken;
            }
        }

        $customerFirstName = isset($ebizchargeCustomer["FirstName"]) && !empty($ebizchargeCustomer["FirstName"]) ? $ebizchargeCustomer["FirstName"] : "-";
        $customerLastName = isset($ebizchargeCustomer["LastName"]) && !empty($ebizchargeCustomer["LastName"]) ? $ebizchargeCustomer["LastName"] : "-";
        $customerEmail = isset($ebizchargeCustomer["Email"]) && !empty($ebizchargeCustomer["Email"]) ? $ebizchargeCustomer["Email"] : $customerFirstName . "@localnet.com";
        $divisionId = isset($ebizchargeCustomer["DivisionId"]) && !empty($ebizchargeCustomer["DivisionId"]) ? $ebizchargeCustomer["DivisionId"] : $this->_ebizConfigFactory->create()->getDivisionID($storeId);
        $dateCreated = isset($ebizchargeCustomer["DateTimeCreated"]) && !empty($ebizchargeCustomer["DateTimeCreated"]) ? $ebizchargeCustomer["DateTimeCreated"] : "";
        $dateModified = isset($ebizchargeCustomer["DateTimeModified"]) && !empty($ebizchargeCustomer["DateTimeModified"]) ? $ebizchargeCustomer["DateTimeModified"] : $dateCreated;
        $customerCustomFields = isset($ebizchargeCustomer["CustomerCustomFields"]) ?
            (array)$ebizchargeCustomer["CustomerCustomFields"] : [];
        $customerCustomFields = isset($customerCustomFields["EbizCustomField"]) ?
            (array)$customerCustomFields["EbizCustomField"] : [];

        $companyName = isset($ebizchargeCustomer["CompanyName"]) && !empty($ebizchargeCustomer["CompanyName"]) ? $ebizchargeCustomer["CompanyName"] : "*";
        $phone = isset($ebizchargeCustomer["Phone"]) && !empty($ebizchargeCustomer["Phone"]) ? $ebizchargeCustomer["Phone"] : "000";
        $softwareId = isset($ebizchargeCustomer["SoftwareId"]) && !empty($ebizchargeCustomer["SoftwareId"]) ? $ebizchargeCustomer["SoftwareId"] : $this->tranApiFactory->create()->getSoftwareId();
        $ebizCustomerInternalId = isset($ebizchargeCustomer["CustomerInternalId"]) && !empty($ebizchargeCustomer["CustomerInternalId"]) ? $ebizchargeCustomer["CustomerInternalId"] : "";
        $ebizCustomerId = isset($ebizchargeCustomer["CustomerId"]) && !empty($ebizchargeCustomer["CustomerId"]) ? $ebizchargeCustomer["CustomerId"] : "";


        $billingFirstName = isset($billingAddress["FirstName"]) && !empty($billingAddress["FirstName"]) ?
            $billingAddress["FirstName"] : $customerFirstName;
        $billingLastName = isset($billingAddress["LastName"]) && !empty($billingAddress["LastName"]) ?
            $billingAddress["LastName"] : $customerLastName;
        $billingCompanyName = isset($billingAddress["CompanyName"]) && !empty($billingAddress["CompanyName"]) ?
            $billingAddress["CompanyName"] : $companyName;
        $billingPhone = isset($billingAddress["Phone"]) && !empty($billingAddress["Phone"]) ? $billingAddress["Phone"] : $phone;
        $billingStreet = isset($billingAddress["Address1"]) && !empty($billingAddress["Address1"]) ? $billingAddress["Address1"] : "NA";
        $billingStreet2 = isset($billingAddress["Address2"]) && !empty($billingAddress["Address2"]) ? $billingAddress["Address2"] : "";
        $billingCountry = isset($billingAddress["Country"]) && !empty($billingAddress["Country"]) ? $billingAddress["Country"] : CustomerInterface::EBIZCHARGE_DEFAULT_BILLING_ADDRESS_COUNTRY;
        $billingCity = isset($billingAddress["City"]) && !empty($billingAddress["City"]) ? $billingAddress["City"] : "NA";
        $billingState = isset($billingAddress["State"]) && !empty($billingAddress["State"]) ? $billingAddress["State"] : "NA";
        $billingZipCode = isset($billingAddress["ZipCode"]) && !empty($billingAddress["ZipCode"]) ? $billingAddress["ZipCode"] : "NA";

        /** shipping address detail */
        $shippingFirstName = isset($shippingAddress["FirstName"]) && !empty($shippingAddress["FirstName"]) ?
            $shippingAddress["FirstName"] : $billingFirstName;
        $shippingLastName = isset($shippingAddress["LastName"]) && !empty($shippingAddress["LastName"]) ?
            $shippingAddress["LastName"] : $billingLastName;
        $shippingCompanyName = isset($shippingAddress["CompanyName"]) && !empty($shippingAddress["CompanyName"]) ?
            $billingAddress["CompanyName"] : $billingCompanyName;
        $shippingPhone = isset($shippingAddress["Phone"]) && !empty($shippingAddress["Phone"]) ? $shippingAddress["Phone"] : $billingPhone;
        $shippingStreet = isset($shippingAddress["Address1"]) && !empty($shippingAddress["Address1"]) ? $shippingAddress["Address1"] : $billingStreet;
        $shippingStreet2 = isset($shippingAddress["Address2"]) && !empty($shippingAddress["Address2"]) ? $shippingAddress["Address2"] : $billingStreet2;
        $shippingCountry = isset($shippingAddress["Country"]) && !empty($shippingAddress["Country"]) ? $shippingAddress["Country"] : $billingCountry;
        $shippingCity = isset($shippingAddress["City"]) && !empty($shippingAddress["City"]) ? $shippingAddress["City"] : $billingCity;
        $shippingState = isset($shippingAddress["State"]) && !empty($billingAddress["State"]) ? $billingAddress["State"] : $billingState;
        $shippingZipCode = isset($shippingAddress["ZipCode"]) && !empty($shippingAddress["ZipCode"]) ? $shippingAddress["ZipCode"] : $billingZipCode;
        $shippingAddressId = isset($shippingAddress["AddressId"]) && !empty($shippingAddress["AddressId"]) ? $shippingAddress["AddressId"] : "";

        /** @var  $ebizchargeCustomerData */
        $ebizchargeCustomerData = [
            'firstname' => $customerFirstName,
            'lastname' => $customerLastName,
            'email' => $customerEmail,
            'company' => $companyName,
            'phone' => $phone,
            'software_id' => $softwareId,
            'ebiz_sync_status' => true,
            'ebiz_internal_id' => $ebizCustomerInternalId,
            'ebiz_customer_id' => $ebizCustomerId,
            'ebiz_customer_token' => $ebizchargeCustomerToken,
            'ebiz_customer_last_syncdate' => $dateModified,
            'billing_address' => [
                'firstname' => $billingFirstName,
                'lastname' => $billingLastName,
                'company' => $billingCompanyName,
                'phone' => $billingPhone,
                'street1' => $billingStreet,
                'street2' => $billingStreet2,
                'country' => $billingCountry,
                'city' => $billingCity,
                'state' => $billingState,
                'zipcode' => $billingZipCode,
                'is_default' => true,
            ],
            'shipping_address' => [
                'firstname' => $shippingFirstName,
                'lastname' => $shippingLastName,
                'company' => $shippingCompanyName,
                'phone' => $shippingPhone,
                'street1' => $shippingStreet,
                'street2' => $shippingStreet2,
                'country' => $shippingCountry,
                'city' => $shippingCity,
                'state' => $shippingState,
                'zipcode' => $shippingZipCode,
                'is_default' => true,
                'address_id' => $shippingAddressId,
            ],
            'ebiz_custom_fields' => $customerCustomFields,
            'ebiz_division_id' => $divisionId,
            'ebiz_created_at' => $dateCreated,
            'ebiz_modified_at' => $dateCreated
        ];

        return $ebizchargeCustomerData;
    }

    /**
     * @param $ebizCustomerId
     * @return bool|null
     */
    public function getEbizCustomerById($ebizCustomerId = null)
    {
        return $this->loadEbizCustomerById($ebizCustomerId);
    }

    /**
     * Load EBizCharge Customer By Id
     *
     * @param string|null $customerId
     * @return bool
     */
    public function loadEbizCustomerById(string $customerId = null)
    {
        $customer = null;

        if ($customerId) {

            try {
                /** @var $store */
                $store = $this->_ebizConfigFactory->create()->getStore();
                /** @var $storeId */
                $storeId = $store->getStoreId();
                $soapApiFactory = $this->tranApiFactory->create();
                /** @var $seToken */
                $seToken = $soapApiFactory->getUeSecurityToken($storeId);

                /** @var $customerParams */
                $customerParams = [
                    'securityToken' => $seToken,
                    'customerId' => $customerId
                ];
                /** @var $customer */
                $customer = $soapApiFactory->getClient($storeId)->GetCustomer($customerParams);

                /** Customer Get Customer Results */
                if (is_object($customer) && $customer->GetCustomerResult) {
                    $this->_ebizchargeLogger->addInfo(__("Customer ID: " . $customerId .
                        " exists at EBizCharge Hub."));

                    $customer = $customer->GetCustomerResult;
                }

            } catch (Exception $exception) {
                $this->_ebizchargeLogger->addCritical(__("Exception occurred during loading customer ID: " .
                    $customerId . " from EBizCharge Hub. Error:" . $exception->getMessage()));
                $customer = null;
            }
        }
        return $customer;
    }

    /**
     * Set Ebizcharge Customer Last Sync Date
     *
     * @param mixed $ecCustLastSyncDate
     * @return Customer
     */
    public function setEcCustLastSyncDate($ecCustLastSyncDate)
    {
        return $this->setData(CustomerInterface::EBIZCHARGE_CUSTOMER_LAST_SYNC_DATE, $ecCustLastSyncDate);
    }

    /**
     * Set Ebizcharge customer Internal Id
     *
     * @param mixed $ecCustInternalId
     * @return Customer
     */
    public function setEcCustInternalId($ecCustInternalId)
    {
        return $this->setData(CustomerInterface::EBIZCHARGE_CUSTOMER_INTERNAL_ID, $ecCustInternalId);
    }

    /**
     * Set Ebizcharge Customer Sync Status
     *
     * @param mixed $ecCustSyncStatus
     * @return Customer
     */
    public function setEcCustSyncStatus($ecCustSyncStatus)
    {
        return $this->setData(CustomerInterface::EBIZCHARGE_CUSTOMER_SYNC_STATUS, $ecCustSyncStatus);
    }

    /**
     * Set Division Id
     *
     * @param mixed $ecDivisionId
     * @return Customer
     */
    public function setEcDivisionId($ecDivisionId)
    {
        return $this->setData(CustomerInterface::EBIZCHARGE_DIVISION_ID, $ecDivisionId);
    }

    /**
     * Set Ebiz Software Id
     *
     * @param mixed $ecSoftwareId
     * @return Customer
     */
    public function setEcSoftwareId($ecSoftwareId)
    {
        return $this->setData(CustomerInterface::EBIZCHARGE_SOFTWARE_ID, $ecSoftwareId);
    }

    /**
     * Set EBizcharge Customer
     *
     * @param mixed $ecCustToken
     * @return Customer
     */
    public function setEcCustToken($ecCustToken)
    {
        return $this->setData(CustomerInterface::EBIZCHARGE_CUSTOMER_TOKEN, $ecCustToken);
    }

    /**
     * Set Ebizcharge Customer Id
     *
     * @param mixed $ecCustId
     * @return Customer
     */
    public function setEcCustId($ecCustId)
    {
        return $this->setData(CustomerInterface::EBIZCHARGE_CUSTOMER_ID, $ecCustId);
    }

    /**
     * Save Customer Address
     *
     * @param mixed $customerId
     * @param mixed $addressParams
     * @param mixed $addressType
     * @param mixed $addressId
     * @param mixed $isDownload
     * @return bool
     */
    public function saveCustomerAddress(
        $customerId = 0,
        $addressParams = [],
        $addressType = 'billing',
        $addressId = 0,
        $isDownload = false
    )
    {
        /** address params or customer id is not given */
        if (!$customerId || !$addressParams) {
            return false;
        }

        try {

            $customer = $this->load($customerId);
            /** @var $customerAddressFactory */
            $customerAddressFactory = $this->_addressFactory->create();

            if ($addressId !== 0) {
                /** @var $customerAddressFactory */
                $customerAddressFactory = $this->_addressFactory->create()->load($addressId);
            }

            /** @var $defaultCountry */
            $store = $this->_storeManager->getStore();
            $storeId = $store->getId();

            $defaultCountry = $this->_ebizConfigFactory->create()->getDefaultCountryCode($storeId) ?
                $this->_ebizConfigFactory->create()->getDefaultCountryCode($storeId) : 'US';
            $defaultCity = $this->_ebizConfigFactory->create()->getDefaultStoreCity($storeId) ?
                $this->_ebizConfigFactory->create()->getDefaultStoreCity($storeId) : '';
            $defaultZipCode = $this->_ebizConfigFactory->create()->getDefaultStorePostCode($storeId) ?
                $this->_ebizConfigFactory->create()->getDefaultStorePostCode($storeId) : '';
            $defaultState = $this->_ebizConfigFactory->create()->getDefaultStoreRegionId($storeId) ?
                $this->_ebizConfigFactory->create()->getDefaultStoreRegionId($storeId) : '';
            $defaultPhone = $this->_ebizConfigFactory->create()->getDefaultStorePhone($storeId) ?
                $this->_ebizConfigFactory->create()->getDefaultStorePhone($storeId) : '';
            $defaultAddress = $this->_ebizConfigFactory->create()->getDefaultStoreAddress($storeId) ?
                $this->_ebizConfigFactory->create()->getDefaultStoreAddress($storeId) : '';

            $customerCountry = isset($addressParams['country']) ? $addressParams['country'] : $defaultCountry;
            $customerState = isset($addressParams['state']) ? $addressParams['state'] : $defaultState;
            $customerCity = isset($addressParams['city']) ? $addressParams['city'] : $defaultCity;

            $countryCode = $this->getCountryCodeByName($customerCountry);

            /** validating the address data */
            $addressParams['firstname'] = isset($addressParams['firstname']) ? $addressParams['firstname'] : "";
            $addressParams['lastname'] = isset($addressParams['lastname']) ? $addressParams['lastname'] : "";
            $addressParams['country'] = $countryCode;
            $addressParams['city'] = $customerCity;
            $addressParams['zipcode'] = $addressParams['zipcode'] ?? $defaultZipCode;
            $addressParams['state'] = $customerState;
            $addressParams['phone'] = isset($addressParams['phone']) ? $addressParams['phone'] : $defaultPhone;
            $addressParams['company'] = $addressParams['company'] ?? CustomerInterface::GUEST_DEAFULT_COMPANY;
            $addressParams['street1'] = $addressParams['street1'] ?? $defaultAddress;
            $addressParams['street2'] = $addressParams['street2'] ?? $defaultAddress;
            $isDefaultAddress = isset($addressParams['is_default']) ? $addressParams['is_default'] : 0;

            $streetAddresses = [];

            $streetAddresses[] = $addressParams['street1'];
            $streetAddresses[] = $addressParams['street2'];

            /** Customer Address Factory */
            //  if($addressId == 0) {
            $customerAddressFactory->setCustomerId($customerId);
            //   }

            $customerAddressFactory
                ->setFirstname($addressParams['firstname'])
                ->setLastname($addressParams['lastname'])
                // ->setCountry($customerCountry)
                ->setCountryId($countryCode)
                ->setPostcode($addressParams['zipcode'])
                ->setCity($addressParams['city'])
                ->setRegion($addressParams['state'])
                ->setTelephone($addressParams['phone'])
                ->setCompany($addressParams['company'])
                ->setStreet($streetAddresses)
                ->setIsDownlaod(true);

            /** Shipping & Billing saving */
            if ($addressType === 'shipping') {
                $customerAddressFactory
                    ->setSaveInAddressBook('1');
                if ($isDefaultAddress) {
                    $customerAddressFactory->setIsDefaultShipping($isDefaultAddress);
                }
            }

            if ($addressType === 'billing') {
                $customerAddressFactory
                    ->setSaveInAddressBook('1');
                if ($isDefaultAddress) {
                    $customerAddressFactory->setIsDefaultBilling($isDefaultAddress);
                }
            }

            /** save customer address factory */
            $customerAddressFactory->save();

            $this->_ebizchargeLogger->addInfo(__("Updated Bill Typed: " . $addressType .
                " Address to the address book to your local system"));

            return true;
        } catch (Exception $exception) {
            $this->_ebizchargeLogger->addCritical(__("Exception occurred during updating Billing Type: " .
                $addressType . " customer Error:" . $exception->getMessage()));
            return false;
        }
    }

    /**
     * Get Country Code By Name
     *
     * @param string $countryName
     * @return string
     */
    public function getCountryCodeByName(string $countryName = ""): string
    {
        $countryCode = "";
        $countries = $this->getCountries();
        $countryLength = strlen($countryName);

        if ($countryLength < 4) {
            $countryCode = substr($countryName, 0, 2);
        }
        if (!$countryCode) {
            if (count($countries) > 0) {
                foreach ($countries as $country) {
                    $countryLabel = isset($country["label"]) ? (string)$country["label"] : "";
                    if ($countryLabel === $countryName) {
                        $code = isset($country["value"]) ? (string)$country["value"] : "";
                        $countryCode = $code;
                    }
                }
            }
        }
        return $countryCode;
    }

    /**
     * Retrieve list of countries in array option
     *
     * @return array
     */
    public function getCountries()
    {
        return $options = $this->getCountryCollection()
            ->setForegroundCountries($this->getTopDestinations())
            ->toOptionArray();
    }

    /**
     * Get Country Collection
     *
     * @return \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    public function getCountryCollection()
    {
        $collection = $this->_countryCollectionFactory->create()->loadByStore();
        return $collection;
    }

    /**
     * Retrieve list of top destinations countries
     *
     * @return array
     */
    protected function getTopDestinations()
    {
        $destinations = (string)$this->_scopeConfig->getValue(
            'general/country/destinations',
            ScopeInterface::SCOPE_STORE
        );
        return !empty($destinations) ? explode(',', $destinations) : [];
    }

    /**
     * Get Ebiz Customer By Id
     *
     * @param mixed $ebizCustomerId
     * @return false|stdClass
     * @throws NoSuchEntityException
     */
    public function getEbizCustomerByIdDeprecated($ebizCustomerId = null)
    {
        /** @var  $ebizCustomer */
        $ebizCustomer = false;
        if (!$ebizCustomerId) {
            return $ebizCustomer;
        }
        try {
            /**
             * Ebiz Customer Results
             */
            $ebizCustomerResults = $this->searchCustomersListAtEbizcharge(
                true,
                true,
                0,
                10,
                "",
                0,
                $ebizCustomerId,
                false
            );

            if (is_array($ebizCustomerResults) && count($ebizCustomerResults) > 0) {
                $ebizCustomer = new stdClass();
                $ebizsearchedCustomer = isset($ebizCustomerResults[0]) ? $ebizCustomerResults[0] : false;
                if (is_object($ebizsearchedCustomer)) {
                    $ebizCustomer->GetCustomerResult = $ebizsearchedCustomer;
                }
            }
        } catch (SoapFault $soapFault) {
            $this->_ebizchargeLogger->addError(__('Soap Fault occurred during fetching Customer By Id Error: ' .
                $soapFault->getMessage()));
        }
        return $ebizCustomer;
    }

    /**
     * Search Customers List At Ebizcharge
     *
     * @param bool $includeCustomerTokens
     * @param bool $includeAccountProfiles
     * @param int $position
     * @param int $limit
     * @param string $sort
     * @param int $countOnly
     * @param string $ebizCustomerId
     * @param bool $isDivisionId
     * @return array
     * @throws NoSuchEntityException
     */
    public function searchCustomersListAtEbizcharge(
        bool   $includeCustomerTokens = true,
        bool   $includeAccountProfiles = false,
        int    $position = 0,
        int    $limit = 100,
        string $sort = "",
        int    $countOnly = 0,
        string $ebizCustomerId = "",
        bool   $isDivisionId = true,
        string $ebizCustomerInternalId = ""
    ): array
    {
        $customersCollection = [];
        $storeId = $this->getStoreId();
        /** @var  $securityToken */
        $securityToken = $this->tranApiFactory->create()->getUeSecurityToken($storeId);
        $divisionId = $this->_ebizConfigFactory->create()->getDivisionID($storeId);
        $start = $position !== 0 ? $position : 0;
        $countOnly = $countOnly !== 0 ? $countOnly : 0;
        $includeCustomerTokens = $includeCustomerTokens !== false ? $includeCustomerTokens : false;
        $limit = $limit ? $limit : SoapApiModelInterface::EBIZCHARGE_DEFAULT_REQUEST_MAX_LIMIT;
        $searchFilters = [];
        $maxSize = 0;


        /*
        if ($divisionId) {
            $searchFilters["SearchFilter"][] = [
                "FieldName" => "DivisionId",
                "ComparisonOperator" => "eq",
                "FieldValue" => $divisionId
            ];
        }
        */
        if ($ebizCustomerId) {
            $searchFilters["SearchFilter"][] = [
                "FieldName" => "CustomerId",
                "ComparisonOperator" => "eq",
                "FieldValue" => $ebizCustomerId
            ];
        }
        if ($ebizCustomerInternalId) {
            $searchFilters["SearchFilter"][] = [
                "FieldName" => "CustomerInternalId",
                "ComparisonOperator" => "eq",
                "FieldValue" => $ebizCustomerInternalId
            ];
        }

        try {
            /**
             * Searching do while
             */
            do {
                /** Search Customer Params for EBizCharge API SOAP */
                /** @var  $searchCustomerParams */
                $searchCustomerParams = [
                    'securityToken' => $securityToken,
                    'filters' => $searchFilters,
                    'start' => $start,
                    'limit' => $limit,
                    'sort' => $sort,
                    'includePaymentMethodProfiles' => (int)$includeAccountProfiles,
                    'includeCustomerToken' => (int)$includeCustomerTokens,
                    'countOnly' => (int)$countOnly,
                ];
                $searchCustomerParams["includePaymentMethodProfiles"] = 0;
                $searchCustomerParams["includeCustomerToken"] = 0;
                // var_dump("<pre>", $searchCustomerParams);exit;
                if ($ebizCustomerId) {
                    $searchCustomerParams["includePaymentMethodProfiles"] = 1;
                    $searchCustomerParams["includeCustomerToken"] = 1;
                }
                if ($ebizCustomerInternalId) {
                    $searchCustomerParams["includePaymentMethodProfiles"] = 1;
                    $searchCustomerParams["includeCustomerToken"] = 1;
                }

                $ebizchargeCustomers = $this->tranApiFactory->create()
                    ->getClient($storeId)
                    ->SearchCustomerList($searchCustomerParams);
                $this->_ebizchargeLogger->addInfo(__("Fetching customers (" . $start . " - " .
                    ((int)$start + (int)$limit) . ")"));
                /**
                 * Sending request Ebizcharge get the latest customers
                 * @Ebizcharge SOAP Api Gateway
                 */
                if (!$this->tranApiFactory->create()->getClient($storeId)) {
                    $this->_ebizchargeLogger->addCritical(__(
                        "Error: Soap Api client is not available, please connect to the internet."
                    ));
                    return $customersCollection;
                }

                /** @var  $ebizchargeCustomers */
                $ebizchargeCustomers = $this->tranApiFactory->create()
                    ->getClient($storeId)
                    ->SearchCustomerList($searchCustomerParams);

                // var_dump("start=" . $start, "maxsize=" . $maxSize, "limit=" . $limit);

                /** fetching customer results */
                if (!isset($ebizchargeCustomers->SearchCustomerListResult) ||
                    !isset($ebizchargeCustomers->SearchCustomerListResult->CustomerList->Customer)
                ) {
                    $customersCollection = [];
                    $resultCount = 0;

                } elseif (is_array($ebizchargeCustomers->SearchCustomerListResult->CustomerList->Customer)
                    && (count((array)$ebizchargeCustomers->SearchCustomerListResult->CustomerList->Customer)) > 1) {

                    $ebzcCustomers = $ebizchargeCustomers->SearchCustomerListResult->CustomerList->Customer;
                    $resultCount = count($ebizchargeCustomers->SearchCustomerListResult->CustomerList->Customer);
                    $customersCollection = [...$customersCollection, ...$ebzcCustomers];

                } else {
                    $ebzcCustomers[] = $ebizchargeCustomers->SearchCustomerListResult->CustomerList->Customer;
                    $customersCollection = [...$customersCollection, ...$ebzcCustomers];
                    $resultCount = 1;
                    $maxSize = 1;
                }

                /** result count */
                if ($resultCount < $limit) {
                    $maxSize = 1;
                }
                $start = $start + $limit;
                //  var_dump("start=" . $start, "maxsize=" . $maxSize, "limit=" . $limit);

            } while ($maxSize === 0);

        } catch (SoapFault $soapFault) {
            $this->_ebizchargeLogger->addCritical(__("Exception occurred during fetching customers. error: " .
                $soapFault->getMessage()));
        }

        $ebizCustomersCollection = [];
        /**
         * filtering the collections
         */
        if (count($customersCollection) > 0 && !$ebizCustomerId) {
            foreach ($customersCollection as $ebizChargeCustomerObj) {
                $ebizChargeCustomer = (array)$ebizChargeCustomerObj;
                $ebizCustomerId = isset($ebizChargeCustomer["CustomerId"]) && empty($ebizChargeCustomer["CustomerId"]);
                $ebizEmail = isset($ebizChargeCustomer["Email"]) && empty($ebizChargeCustomer["Email"]);
                $ebizSoftwareId = isset($ebizChargeCustomer["SoftwareId"]) ? $ebizChargeCustomer["SoftwareId"] : "";
                $ebizDivisionId = isset($ebizChargeCustomer["DivisionId"]) ? $ebizChargeCustomer["DivisionId"] : "";
                $ebizOrigDivisionId = isset($ebizChargeCustomer["DivisionId"]) ? $ebizChargeCustomer["DivisionId"] : "";

                if ($ebizCustomerId || $ebizEmail) continue;


                if (empty($ebizDivisionId)) {
                    $ebizDivisionId = $ebizSoftwareId;
                    $ebizChargeCustomerObj->DivisionId = $ebizDivisionId;
                }
                $ebizNewDivisionId = empty($ebizOrigDivisionId) && ((string)$ebizDivisionId === (string)$ebizSoftwareId);

                if (!$ebizNewDivisionId || (string)$ebizChargeCustomer["DivisionId"] === (string)$divisionId
                ) {
                    $ebizCustomersCollection[] = $ebizChargeCustomerObj;
                }

            }
        } else {
            $ebizCustomersCollection = $customersCollection;
        }

        // dump( $ebizCustomersCollection, count($ebizCustomersCollection),  $divisionId, $searchCustomerParams);exit;
        /**
         * Fetch all Records in an array list End
         */
        return $ebizCustomersCollection;
    }

    /**
     * Load By Customer Internal Id
     *
     * @param mixed $ebizCustomerInternalId
     * @return Customer
     */
    public function loadByEbizCustomerInternalId($ebizCustomerInternalId)
    {
        $customerId = $this->getResource()->loadByEbizchargeCustomerInternalId($ebizCustomerInternalId);
        return $this->load($customerId);
    }

    /**
     * Load EbizCharge Customer
     *
     * @param string $ebizCustomerId
     * @return array|bool
     * @throws NoSuchEntityException
     */
    public function loadEbizCustomer($ebizCustomerId = '')
    {
        if (!$ebizCustomerId) {
            return false;
        }

        /** @var  $customer */
        $customer = $this->loadByEbizCustomerId($ebizCustomerId);
        if (!$customer->getEntityId()) {
            return false;
        }

        $customer->setId($customer->getEntityId());
        if ($customer->getEntityId()) {
            $customer = $this->prepareEbizCustomerParams($customer);
        } else {
            $customer = $this->loadCustomerFromEbizchargeById($ebizCustomerId);
        }
        return $customer;
    }

    /**
     * Load By Customer Id
     *
     * @param mixed $ebizCustomerId
     * @return Customer
     */
    public function loadByEbizCustomerId($ebizCustomerId)
    {
        $customerId = $this->getResource()->loadByEbizchargeCustomerId($ebizCustomerId);
        return $this->load($customerId);
    }

    /**
     * Prepare Ebizcharge Customer Params
     *
     * @param Customer|null $customer
     * @return array|bool
     * @throws LocalizedException
     */
    public function prepareEbizCustomerParams(Customer $customer = null)
    {
        if (!$customer) {
            return false;
        }

        $customerId = $customer->getEntityId();

        $billingAddress = is_object($customer->getDefaultBillingAddress()) ?
            $customer->getDefaultBillingAddress() : '';
        if (!is_object($billingAddress)) {
            return false;
        }

        $billingAddressId = $billingAddress->getEntityId();
        $isDefaultBillingAddress = $this->getIsDefaultBillingAddress($customerId, $billingAddressId);

        $shippingAddress = is_object($customer->getDefaultShippingAddress()) ?
            $customer->getDefaultShippingAddress() : '';
        if (!is_object($shippingAddress)) {
            return false;
        }

        $shippingAddressId = $billingAddress->getEntityId();
        $isDefaultShippingAddress = $this->getIsDefaultShippingAddress($customerId, $shippingAddressId);

        // phpcs:disable
        $firstName = is_object($billingAddress) ? @$billingAddress->getFirstname() : $customer->getName();
        $lastName = is_object($billingAddress) ? @$billingAddress->getLastname() : '*';
        $company = is_object($billingAddress) ? @$billingAddress->getCompany() : '*';
        $telephone = is_object($billingAddress) ? @$billingAddress->getTelephone() : '*';
        $billingAddressStreet = is_object($billingAddress) ? @$billingAddress->getStreet() : [];
        $shippingAddressStreet = is_object($shippingAddress) ? @$shippingAddress->getStreet() : [];

        /** @var  $ebizchargeCustomerData */
        $ebizchargeCustomerData = [
            'firstname' => @$firstName,
            'lastname' => @$lastName,
            'email' => @$customer->getEmail(),
            'company' => @$company,
            'phone' => @$telephone,
            'software_id' => @$customer->getEcSoftwareId(),
            'ebiz_sync_status' => $customer->getEcCustSyncStatus(),
            'ebiz_internal_id' => @$customer->getEcCustInternalId(),
            'ebiz_customer_id' => @$customer->getEcCustId(),
            'ebiz_customer_token' => @$customer->getEcCustToken(),
            'ebiz_customer_last_syncdate' => @$customer->getEcCustLastSyncDate(),
            'billing_address' => [
                'firstname' => is_object($billingAddress) ? @$billingAddress->getFirstname() : $customer->getName(),
                'lastname' => is_object($billingAddress) ? @$billingAddress->getLastname() : '*',
                'company' => is_object($billingAddress) ? @$billingAddress->getCompany() : '*',
                'phone' => is_object($billingAddress) ? @$billingAddress->getTelephone() : '*',
                'street1' => @isset($billingAddressStreet[0]) ? $billingAddressStreet[0] : '',
                'street2' => @isset($billingAddressStreet[1]) ? $billingAddressStreet[1] : '',
                'country' => is_object($billingAddress) ? @$this->getCountryName($billingAddress->getCountryId()) : 'US',
                'city' => is_object($billingAddress) ? @$billingAddress->getCity() : '*',
                'state' => is_object($billingAddress) ? @$billingAddress->getRegion() : '*',
                'zipcode' => is_object($billingAddress) ? @$billingAddress->getPostcode() : '*',
                'is_default' => $isDefaultBillingAddress,
            ],
            'shipping_address' => [
                'firstname' => is_object($shippingAddress) ? @$shippingAddress->getFirstname() : '*',
                'lastname' => is_object($shippingAddress) ? @$shippingAddress->getLastname() : '*',
                'company' => is_object($shippingAddress) ? @$shippingAddress->getCompany() : '*',
                'phone' => is_object($shippingAddress) ? @$shippingAddress->getTelephone() : '*',
                'street1' => @isset($shippingAddressStreet[0]) ? $shippingAddressStreet[0] : '',
                'street2' => @isset($shippingAddressStreet[1]) ? $shippingAddressStreet[1] : '',
                'country' => is_object($shippingAddress) ? @$this->getCountryName($billingAddress->getCountryId()) : 'US',
                'city' => is_object($shippingAddress) ? @$shippingAddress->getCity() : '*',
                'state' => is_object($shippingAddress) ? @$shippingAddress->getRegion() : '*',
                'zipcode' => is_object($shippingAddress) ? @$shippingAddress->getPostcode() : '*',
                'is_default' => $isDefaultShippingAddress,
            ],
            'ebiz_custom_fields' => @(array)$customer->getCustomFields(),
            'ebiz_division_id' => @$customer->getEcDivisionId(),
            'ebiz_created_at' => @$customer->getCreatedAt(),
            'ebiz_modified_at' => @$customer->getUpdatedAt()
        ];
        // phpcs:enable

        return $ebizchargeCustomerData;
    }

    /**
     * Get Is Default Billing Address
     *
     * @param int $customerId
     * @param int $addressId
     * @return bool
     */
    public function getIsDefaultBillingAddress($customerId = 0, $addressId = 0)
    {
        $isDefaultBillingAdress = false;

        $customer = $this->load($customerId);

        if ($customer->getEntityId()) {
            $addressIds = $customer->getPrimaryAddressIds();
            if (in_array($addressId, $addressIds)) {
                $isDefaultBillingAdress = true;
            }
        }
        return $isDefaultBillingAdress;
    }

    /**
     * Get Is Default Shipping Address
     *
     * @param int $customerId
     * @param int $addressId
     * @return bool
     */
    public function getIsDefaultShippingAddress($customerId = 0, $addressId = 0)
    {
        $isDefaultShippingAdress = false;

        $customer = $this->load($customerId);

        if ($customer->getEntityId()) {
            $addressIds = $customer->getPrimaryAddressIds();
            if (in_array($addressId, $addressIds)) {
                $isDefaultShippingAdress = true;
            }
        }
        return $isDefaultShippingAdress;
    }

    /**
     * Get Ebiz Software Id
     *
     * @return string
     */
    public function getEcSoftwareId()
    {
        return $this->getData(CustomerInterface::EBIZCHARGE_SOFTWARE_ID);
    }

    /**
     * Get Ebizcharge Customer Sync Status
     *
     * @return array|mixed|null
     */
    public function getEcCustSyncStatus()
    {
        return $this->getData(CustomerInterface::EBIZCHARGE_CUSTOMER_SYNC_STATUS);
    }

    /**
     * Get Ebizcharge Customer Internal Id
     *
     * @return array|mixed|null
     */
    public function getEcCustInternalId()
    {
        return $this->getData(CustomerInterface::EBIZCHARGE_CUSTOMER_INTERNAL_ID);
    }

    /**
     * Get Ebizcharge Customer Id
     *
     * @return array|mixed|null
     */
    public function getEcCustId()
    {
        return $this->getData(CustomerInterface::EBIZCHARGE_CUSTOMER_ID);
    }

    /**
     * Get Ebizcharge Customer Token
     *
     * @return array|mixed|null
     */
    public function getEcCustToken()
    {
        return $this->getData(CustomerInterface::EBIZCHARGE_CUSTOMER_TOKEN);
    }

    /**
     * Get Ebizcharge Customer Last Sync Date
     *
     * @return array|mixed|null
     */
    public function getEcCustLastSyncDate()
    {
        return $this->getData(CustomerInterface::EBIZCHARGE_CUSTOMER_LAST_SYNC_DATE);
    }

    /**
     * Get Country Name
     *
     * @param string $countryCode
     * @return string
     */
    public function getCountryName(string $countryCode = ""): string
    {
        $country = $this->_countryFactory->create()->loadByCode($countryCode);
        if (!$country) {
            return "";
        }
        return $country->getName() ?? "";
    }

    /**
     * Get Custom Fields
     *
     * @return string
     */
    public function getCustomFields()
    {
        return '';
    }

    /**
     * Get Ec Division Id
     *
     * @return string
     */
    public function getEcDivisionId()
    {
        return $this->getData(CustomerInterface::EBIZCHARGE_DIVISION_ID);
    }

    /**
     * Load Customer From Ebizcharege By Id
     *
     * @param string $ebizchargeCustomerId
     * @return array|bool
     * @throws NoSuchEntityException
     */
    public function loadCustomerFromEbizchargeById($ebizchargeCustomerId = '')
    {
        /** @var $customer */
        $ebizchargeCustomer = $this->tranApiFactory->create()->getEbizchargeCustomerById($ebizchargeCustomerId);
        /** @var $customer */
        $customer = $this->prepareEbizchargeCustomerData($ebizchargeCustomer);

        return $customer;
    }

    /**
     * Get EbizCustomer By Internal Id
     *
     * @param null|mixed $ebizCustomerInternalId
     * @return bool
     */
    public function getEbizCustomerByInternalId($ebizCustomerInternalId = null)
    {
        if (!$ebizCustomerInternalId) {
            return false;
        }
        try {
            return $this->tranApiFactory->create()->getEbizchargeCustomerByInternalId($ebizCustomerInternalId);
        } catch (SoapFault $soapFault) {
            $this->_ebizchargeLogger->addError(__('Soap Fault occured during fetching Customer By Internal Id'));
            return false;
        }
    }

    /**
     * @return AbstractDb|AbstractCollection|null
     * @throws NoSuchEntityException
     */
    public function checkTotalLocalCustomers(): AbstractDb|AbstractCollection|null
    {
        return $this->getLocalCustomersToUpload();
    }

    /**
     * Get Customer Collection
     *
     * @param $params
     * @return AbstractDb|AbstractCollection|null
     * @throws NoSuchEntityException
     */
    public function getLocalCustomersToUpload($params = [])
    {
        /** @var  $storeId */
        $storeId = $this->_ebizConfigFactory->create()->getStoreId();
        $envPrefix = $this->_ebizConfigFactory->create()->getEnvoirnmentPrefix($storeId);

        /** @var  $customersCollection */
        $customersCollection = $this->getCollection();
        $customersCollection->addFieldToSelect("entity_id");
        $customersCollection->getSelect()->where('is_active = 1');
        /***
         * $customersCollection->getSelect()->where(CustomerInterface::EBIZCHARGE_DIVISION_ID .
         * ' like(\'' . $envPrefix . '%\') ');
         **/
        $customersCollection->getSelect()->columns("entity_id");
        $conditions = CustomerInterface::EBIZCHARGE_CUSTOMER_INTERNAL_ID . ' IS NULL  OR ';
        $conditions .= CustomerInterface::EBIZCHARGE_CUSTOMER_INTERNAL_ID . '=""   OR ';
        $conditions .= CustomerInterface::EBIZCHARGE_CUSTOMER_ID . ' IS NULL   OR ';
        $conditions .= CustomerInterface::EBIZCHARGE_CUSTOMER_ID . '=""   OR ';
        $conditions .= CustomerInterface::EBIZCHARGE_DIVISION_ID . ' IS NULL   OR ';
        $conditions .= CustomerInterface::EBIZCHARGE_DIVISION_ID . '=""  ';
        //   $conditions .= CustomerInterface::EBIZCHARGE_CUSTOMER_TOKEN . ' IS NULL   OR ';
        //   $conditions .= CustomerInterface::EBIZCHARGE_CUSTOMER_TOKEN . '=""   OR ';
        // $conditions .= CustomerInterface::EBIZCHARGE_CUSTOMER_ID . ' like(\'' . $envPrefix . '%\')  ';

        $customersCollection->getSelect()->where($conditions);
        // var_dump($customersCollection->getSelect()->__toString(), count($customersCollection)); exit;
        return $customersCollection;
    }

    /**
     * Get Customers
     *
     * @return Collection|AbstractDb
     */
    public function getCustomers()
    {
        /** @var $customersCollection */
        $customersCollection = $this->_customerCollection
            ->addFieldToSelect('*')
            ->load();
        /** Customer Collection */
        $customersCollection->getSelect()
            ->where('is_active=?', 1)
            ->where(
                CustomerInterface::EBIZCHARGE_CUSTOMER_INTERNAL_ID,
                ['neq' => 'NULL']
            );

        return $customersCollection;
    }

    /**
     * Get Total Local Customers
     *
     * @return int
     */
    public function getTotalLocalCustomers()
    {
        return $this->_customerCollection->count();
    }

    /**
     * Get Total Active Customers
     *
     * @param int $active
     * @return mixed
     */
    public function getTotalActiveCustomers($active = 1)
    {
        /** @var  $active */
        $active = $active == 1 ?? 0;

        /** @var $totalCustomers */
        $totalCustomers = $this->_customerCollection->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter(
                'status',
                [
                    'eq' => $active
                ]
            )->count();
        return $totalCustomers;
    }

    /**
     * Load Ebiz Customer By Id
     *
     * @param string|null $customerInternalId
     * @return bool
     */
    public function loadEbizCustomerByInternalId(string $customerInternalId = null)
    {
        $customer = false;

        if ($customerInternalId) {
            try {

                /** @var $store */
                $store = $this->_storeManager->getStore();

                /** @var $storeId */
                $storeId = $store->getId() ?? $this->getStoreId();
                /** @var $seToken */
                $ebizToken = $this->tranApiFactory->create()->getUeSecurityToken($storeId);

                /** @var $customerParams */
                $customerParams = [
                    'securityToken' => $ebizToken,
                    'customerInternalId' => $customerInternalId
                ];

                /** @var $customer */
                $customer = $this->tranApiFactory->create()->getClient($storeId)->GetCustomer($customerParams);

                /** Customer Get Customer Results */
                if ($customer->GetCustomerResult) {
                    $this->_ebizchargeLogger->addInfo(__("Customer with Customer internal ID: " .
                        $customerInternalId . " found at EBizCharge Hub."));
                    return $customer->GetCustomerResult;
                }
                return false;
            } catch (Exception $exception) {
                $this->_ebizchargeLogger->addCritical(__(
                    "Exception occured during loading customer internal ID: " . $customerInternalId .
                    " from EBizCharge Hub. Error:" . $exception->getMessage()
                ));
                $customer = false;
            }
        }
        return $customer;
    }

    /**
     *  Get customers List At EbizCharge API Gateway
     *
     * @param null|mixed $requestParams
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCustomersAtEbizcharge($requestParams = null): array
    {
        /** @var  $ebizCustomers */
        $ebizCustomers = [];

        /** @var  $storeId */
        $storeId = $this->getStoreId();
        $ebizCustomerID = isset($requestParams["ebiz_customer_id"]) ? $requestParams["ebiz_customer_id"] : "";
        $requestParams["ebiz_customer_internal_id"] = isset($requestParams["ebiz_customer_internal_id"]) ? $requestParams["ebiz_customer_internal_id"] : "";
        $requestParams["ebiz_customer_id"] = isset($requestParams["ebiz_customer_id"]) ? $requestParams["ebiz_customer_id"] : "";
        $requestParams["http_request"] = isset($requestParams["http_request"]) ? $requestParams["http_request"] : false;
        $requestParams["prefix"] = $this->_ebizConfigFactory->create()->getEnvoirnmentPrefix($storeId);
        $requestParams["position"] = isset($requestParams["position"]) ? $requestParams["position"] : 0;
        $requestParams["store_id"] = $storeId;
        $requestParams["count_only"] = isset($requestParams["count_only"]) ? $requestParams["count_only"] : 0;
        $requestParams["sort"] = isset($requestParams["sort"]) ? $requestParams["sort"] : "";
        $requestParams["limit"] = isset($requestParams["limit"]) ? $requestParams["limit"] : TranApi::EBIZCHARGE_DEFAULT_REQUEST_MAX_LIMIT;
        $requestParams["division_id"] = isset($requestParams["division_id"]) ? $requestParams["division_id"] : $this->_ebizConfigFactory->create()->getDivisionID($storeId);


        /** $ebizCustomerCollection **/
        $ebizCustomersCollection = $this->getCustomerResourcesAtEbizcharge($requestParams);

        /** @var  $totalEbizCustomers */
        $totalEbizCustomers = count($ebizCustomersCollection);
        $counter = 0;
        $envPrfix = $this->_ebizConfigFactory->create()->getEnvoirnmentPrefix($storeId);

        if ($totalEbizCustomers > 0) {

            foreach ($ebizCustomersCollection as $ebizChargeCustomer) {
                $ebizCustomer = (array)$ebizChargeCustomer;
                $ebizEmail = isset($ebizCustomer["Email"]) ?
                    trim(str_replace([" "], [""], (string)$ebizCustomer["Email"])) : "";
                $ebizCustomerID = isset($ebizCustomer["CustomerId"]) ?
                    trim(str_replace([" "], [""], (string)$ebizCustomer["CustomerId"])) : "";
                $ebizDivisionID = isset($ebizCustomer["DivisionId"]) ?
                    trim(str_replace([" "], [""], (string)$ebizCustomer["DivisionId"])) : "";

                if (str_contains($ebizDivisionID, $envPrfix) === false) {
                    continue;
                }
                if (!$ebizCustomerID || !$ebizEmail || $ebizEmail === "") {
                    continue;
                }
                if (in_array($ebizEmail, $this->_downloadedCustomerEmails)) {
                    continue;
                }

                /** @var $isLocalCustomer */
                $isLocalCustomer = $this->isEbizCustomerExistInLocal($ebizCustomer);

                if (!in_array($ebizEmail, $this->_downloadedCustomerEmails)) {
                    $this->_downloadedCustomerEmails[] = $ebizEmail;
                }
                if ($isLocalCustomer) {
                    continue;
                }
                $counter++;
                $ebizCustomers[] = $ebizChargeCustomer;
            }
        }

        return $ebizCustomers;
    }

    /**
     * Check EbizCharge Customers
     *
     * @param $requestParams
     * @return array
     */
    public function getCustomerResourcesAtEbizcharge($requestParams = [])
    {
        /** @var  $syncAssetsFactory */
        $syncAssetsFactory = $this->_syncAssetsFactory->create();
        $syncAssetsFactory = $syncAssetsFactory->loadByProcessCode(
            SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_CUSTOMERS
        );
        //  $startPosition = $syncAssetsFactory->getData(SyncAssetsInterface::LAST_SYNC_COUNTER);
        return $this->getEbizchargeApiCustomers($requestParams);
    }

    /**
     * Download Customers
     *
     * @param array $requestParams
     * @return array
     */
    public function getEbizchargeApiCustomers($requestParams = []): array
    {
        /** @var $startPosition */
        $startPosition = isset($requestParams['position']) ? $requestParams['position'] : 0;
        $ebizCustomerId = isset($requestParams['ebiz_customer_id']) ? $requestParams['ebiz_customer_id'] : '';
        $ebizCustomerInternalId = isset($requestParams['ebiz_customer_internal_id']) ? $requestParams['ebiz_customer_internal_id'] : '';
        $limit = isset($requestParams['limit']) ? $requestParams['limit'] : SoapApiModelInterface::EBIZCHARGE_DEFAULT_REQUEST_MAX_LIMIT;
        $isHttpRequest = isset($requestParams['http_request']) ? $requestParams['http_request'] : "";
        $countOnly = isset($requestParams['count_only']) ? $requestParams['count_only'] : 0;
        $sort = isset($requestParams['sort']) ? $requestParams['sort'] : "";
        $isDivisionId = isset($requestParams["division_id"]) ? true : false;

        $includeAccountProfiles = true;
        $includeCustomerToken = true;
        if ($isHttpRequest) {
            $includeAccountProfiles = false;
            $includeCustomerToken = false;
        }
        if ($ebizCustomerId || $ebizCustomerInternalId) {
            $includeAccountProfiles = true;
            $includeCustomerToken = true;
        }
        /** @var  $customersAtEbizCharge */
        return $this->searchCustomersListAtEbizcharge(
            $includeCustomerToken,
            $includeAccountProfiles,
            $startPosition,
            $limit,
            $sort,
            $countOnly,
            $ebizCustomerId,
            $isDivisionId,
            $ebizCustomerInternalId
        );
    }

    /**
     * Is Ebiz Customer Exist In Local
     *
     * @param array $ebizCustomer
     * @return bool
     * @throws Exception
     */
    public function isEbizCustomerExistInLocal($ebizCustomer = []): bool
    {
        /** @var  $isLocalCustomer */
        $isLocalCustomer = false;
        $store = $this->getStore();
        $storeId = $store->getId();
        $envPrefix = $this->_ebizConfigFactory->create()->getEnvoirnmentPrefix($storeId);
        $ebizCustomerEmail = isset($ebizCustomer["Email"]) ? $ebizCustomer["Email"] : "";
        $ebizCustomerInternalId = $ebizCustomer["CustomerInternalId"] ?? "";
        $ebizCustomerID = isset($ebizCustomer["CustomerId"]) ? $ebizCustomer["CustomerId"] : "";
        $ebizDateModified = isset($ebizCustomer["DateTimeModified"]) ? $ebizCustomer["DateTimeModified"] : "";

        /** @var  $customer */
        // $customer = $this->loadByEbizCustomerId($ebizCustomerID);
        $customer = $this->loadByEmail($ebizCustomerEmail);

        if ($customer && $customer->getId()) {
            $isLocalCustomer = true;
        }

        if ($customer->getId() && $customer->getEcCustId() === $ebizCustomerID) {
            $lastSyncDateTime = $customer->getEcCustLastSyncDate() ?? "";
            $isCustomerNeedTobeUpdated = $this->isCustomerNeedTobeUpdated($lastSyncDateTime, $ebizDateModified);

            if (!$isCustomerNeedTobeUpdated) {
                $isLocalCustomer = true;
            }
        }
        return $isLocalCustomer;
    }
    // phpcs:enable

    /**
     * Is Customer Need To be Updated
     *
     * @param mixed $startDateModified
     * @param mixed $endDateModified
     * @return bool
     * @throws Exception
     */
    public function isCustomerNeedTobeUpdated($startDateModified = null, $endDateModified = null)
    {
        //var_dump($startDateModified, $endDateModified);
        $isNeedTobeUpdated = false;
        $startDateTime = new \DateTime($startDateModified);
        $endDateTime = new \DateTime($endDateModified);
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
     * Get Store Id())
     *
     * @return int|string
     * @throws NoSuchEntityException
     */
    public function getStoreId(){
        return $this->_ebizConfigFactory->create()->getStoreId() ?? "0";
    }

    /**
     * Search Customers At Ebizcharge
     *
     * @param string $magCustomerId
     * @param string $customerInternalId
     * @param int $limit
     * @param int $position
     * @return array
     */
    public function searchCustomersAtEbizcharge(
        string $magCustomerId = '',
        string $customerInternalId = '',
        int    $limit = SoapApiModelInterface::EBIZCHARGE_DEFAULT_REQUEST_MAX_LIMIT,
        int    $position = 0
    ): array
    {
        $storeId = $this->_ebizConfigFactory->create()->getStoreId() ?? "0";
        /** getting Ebizcharge $securityToken */
        $securityToken = $this->tranApiFactory->create()->getUeSecurityToken($storeId);

        $ebzcCustomer = '';
        $maxSize = 0;
        $start = 0;

        /**
         * Define Customer Object
         */
        /** @var $gatewayCustomers */
        $gatewayCustomers = [];

        try {
            /**
             * Searching do while
             */
            do {
                /** Search Customer Params for EBizCharge API SOAP */
                /** @var  $searchCustomerParams */
                $searchCustomerParams = [
                    'securityToken' => $securityToken,
                    'customerId' => $magCustomerId,
                    'customerInternalId' => $customerInternalId,
                    'start' => $start,
                    'limit' => $limit,
                    'sort' => '',
                    'filters' => []
                ];

                $this->_ebizchargeLogger->addInfo(__("Fetching customers (" . $start . " - " .
                    ((int)$start + (int)$limit) . ")"));
                /**
                 * Sending request Ebizcharge get the latest customers
                 * @Ebizcharge SOAP Api Gateway
                 */
                if (!$this->tranApiFactory->create()->getClient($storeId)) {
                    $this->_ebizchargeLogger->addCritical(__(
                        "Error: Soap Api client is not available, please connect to the internet."
                    ));
                    return $gatewayCustomers;
                }
                /** @var  $ebizchargeCustomers */
                $ebizchargeCustomers = $this->tranApiFactory->create()
                    ->getClient($storeId)
                    ->SearchCustomers($searchCustomerParams);

                /** fetching customer results */
                if (!isset($ebizchargeCustomers->SearchCustomersResult)) {
                    $gatewayCustomers = [];
                    $resultCount = 0;

                } elseif (is_array($ebizchargeCustomers->SearchCustomersResult->Customer)
                    && (count((array)$ebizchargeCustomers->SearchCustomersResult->Customer)) > 1) {

                    $ebzcCustomer = $ebizchargeCustomers->SearchCustomersResult->Customer;
                    $resultCount = count($ebizchargeCustomers->SearchCustomersResult->Customer);
                    $gatewayCustomers = [...$gatewayCustomers, ...$ebzcCustomer];

                } else {
                    $gatewayCustomers = [...$gatewayCustomers, ...$ebizchargeCustomers->SearchCustomersResult->Customer];
                    $resultCount = 1;
                    $maxSize = 1;
                }

                /** result count */
                if ($resultCount < $limit) {
                    $maxSize = 1;
                }
                $start = $start + $limit;
                //  var_dump("start=" . $start, "maxsize=" . $maxSize, "limit=" . $limit);

            } while ($maxSize === 0);

        } catch (Exception $exception) {
            $this->_ebizchargeLogger->addCritical(__("Exception occurred during fetching customers. Error: " .
                $exception->getMessage()));
            //var_dump($exception->getMessage());
        }
        /**
         * Fetch all Records in an array list End
         */
        return $gatewayCustomers;
    }

    /**
     * Search Customer Download Merged
     *
     * @param string $magCustomerId
     * @param string $customerInternalId
     * @param int $limit
     * @param int $position
     * @return array
     */
    public function searchCustomersDownloadMerged(
        $magCustomerId = '',
        $customerInternalId = '',
        $limit = 1000,
        $position = 0
    )
    {
        $storeId = $this->getStoreId();
        /**getting Ebizcharge $securityToken */
        $securityToken = $this->tranApiFactory->create()->getUeSecurityToken($storeId);

        $ebzcCustomer = '';
        $maxSize = 0;
        $start = $position !== 0 ? $position : 0;

        /**
         * Define Customer Object
         */
        /** @var $customersObj */
        $customersObj = [];

        do {

            /** @var  $customerFilters */
            $customerFilters = [
                'SearchFilter' => [
                    'FieldName' => 'SoftwareId',
                    'ComparisonOperator' => 'eq',
                    'FieldValue' => $this->tranApiFactory->create()->getSoftwareId()
                ]
            ];

            /** Search Customer Params for EBizCharge API SOAP */

            /** @var  $searchCustomerParams */
            $searchCustomerParams = [
                'securityToken' => $securityToken,
                'customerId' => $magCustomerId,
                'customerInternalId' => $customerInternalId,
                'start' => $start,
                'limit' => $limit,
                'sort' => 'SalesOrderNumber',
                'includeCustomerToken' => 1,
                'includePaymentMethodProfiles' => 0,
                'countOnly' => 0,
                'filters' => $customerFilters
            ];

            /**
             * Sending request Ebizcharge get the latest customers
             * @Ebizcharge SOAP Api Gateway
             */
            /** @var  $ebizchargeCustomers */
            $ebizchargeCustomers = $this->tranApiFactory->create()->getClient($storeId)
                ->SearchCustomerList($searchCustomerParams);

            /** fetching customer results */
            if (!isset($ebizchargeCustomers->SearchCustomerListResult->CustomerList->Customer)) {
                $customersObj = [];
                $resultCount = 0;
            } elseif ((is_array($ebizchargeCustomers->SearchCustomerListResult->CustomerList->Customer)) &&
                (count($ebizchargeCustomers->SearchCustomerListResult->CustomerList->Customer)) > 1) {
                $ebzcCustomer = $ebizchargeCustomers->SearchCustomerListResult->CustomerList->Customer;
                $resultCount = count($ebizchargeCustomers->SearchCustomerListResult->CustomerList->Customer);

                $customersObj = array_merge($customersObj, $ebzcCustomer);
            } else {
                $customersObj = $ebizchargeCustomers->SearchCustomerListResult->CustomerList;
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
        return ($customersObj);
    }

    /**
     * Is Customer Exists at Ebizcharge
     *
     * @param string $customerEbizInternalid
     * @param array $ebizchargeCustomers
     * @return bool
     */
    public function isCustomerExistsAtEbizcharge($customerEbizInternalid = '', $ebizchargeCustomers = [])
    {
        $isCustomerExists = false;
        foreach ($ebizchargeCustomers as $customer) {
            if ($customer->CustomerInternalId == $customerEbizInternalid) {
                $isCustomerExists = true;
            }
        }
        return $isCustomerExists;
    }

    /**
     * Run Shell Command
     *
     * @return bool
     */
    public function processDownloadCustomersFromEbizcharge()
    {
        /**
         * Download customers Disabled
         */
        $isDownloadCustomerActive = $this->downloadCustomerIsActive();
        if (!$isDownloadCustomerActive) {
            return false;
        }
        try {
            $downloadCustomersShellCommand = ShellCommand::SHELL_COMMAND_PROCESS_DOWNLOAD_CUSTOMERS;

            /** run shell command  */
            return $this->_shellCommand->run($downloadCustomersShellCommand);
        } catch (LocalizedException $exception) {
            $this->_ebizchargeLogger->addCritical(__(
                "Exception occured during downloading customers Error: " . $exception->getMessage()
            ));
            return false;
        }
    }

    /**
     * Run Shell Command to upload customers
     *
     * @return bool
     */
    public function processUploadCustomersToEbizcharge()
    {
        /**
         * Upload customers Disabled
         */
        $isUploadCustomerActive = $this->uploadCustomerIsActive();
        if (!$isUploadCustomerActive) {
            return false;
        }
        try {
            $downloadCustomersShellCommand = ShellCommand::SHELL_COMMAND_PROCESS_UPLOAD_CUSTOMERS;
            /** run shell command  */
            return $this->_shellCommand->run($downloadCustomersShellCommand);
        } catch (LocalizedException $exception) {
            $this->_ebizchargeLogger->addCritical(__(
                "Exception occured during downloading customers Error: " . $exception->getMessage()
            ));
            return false;
        }
    }

    /**
     * Upload Customer Is Active
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function uploadCustomerIsActive()
    {
        $isUploadCustomersActive = false;
        $storeId = $this->_ebizConfigFactory->create()->getStoreId();
        $isModuleActive = $this->_ebizConfigFactory->create()->isActive($storeId);
        //  $isUploadCustomerEnabled = $this->_ebizConfigFactory->create()->isUploadCustomersEnabled($storeId);

        //   if ($isModuleActive && $isUploadCustomerEnabled) {
        if ($isModuleActive) {
            $isUploadCustomersActive = true;
        } else {
            $this->_ebizchargeLogger->addCritical(__(
                'EbizCharge Hub econnect or upload customers is not enabled from configuration, please enable it.'
            ));
        }

        return $isUploadCustomersActive;
    }

    /**
     * @param $customerId
     * @return bool
     * @throws NoSuchEntityException
     */
    public function syncLocalCustomerToEbizhcarge($customerId = null)
    {
        /**
         * Syncing is disabled from configuration
         */
        $isUploadCustomerActive = $this->uploadCustomerIsActive();

        if (!$isUploadCustomerActive) {
            return false;
        }

        try {

            /** @var $localCustomer */
            $localCustomer = $this->load($customerId);

            /** @var $ebizCustInternalId */
            $ebizCustInternalId = $localCustomer->getEcCustInternalId();
            /** @var $ebizCustId */
            $ebizCustId = $localCustomer->getEcCustId();
            /** @var  $ebizCustToken */
            $ebizCustToken = $localCustomer->getEcCustToken();

            $this->_ebizchargeLogger->addInfo(__('Local Customer: ' . $customerId .
                ' is going to update to Ebizcharge Gateway'));

            /** syncing customer to Ebizhcarge */
            $ebizCustomer = $this->saveLocalCustomerToEbizcharge($localCustomer);

            /** if ebizcharge customer */
            if (is_array($ebizCustomer)
                && isset($ebizCustomer['status'])
                && $ebizCustomer['status'] == 'success'
            ) {
                $ebizCustInternalId = $ebizCustomer['ebiz_internal_id'];
                $ebizCustId = $ebizCustomer['ebiz_customer_id'];
                $ebizCustToken = $ebizCustomer['ebiz_customer_token'];
                $ebizCustomerDate = $ebizCustomer['ec_cust_lastsyncdate'];
                $ebizSoftwareId = $ebizCustomer['ebiz_software_id'];
                $ebizDivisionId = $ebizCustomer['ebiz_division_id'];

                $localCustomer
                    ->setEcCustId($ebizCustId)
                    ->setEcCustInternalId($ebizCustInternalId)
                    ->setEcCustToken($ebizCustToken)
                    // ->setEcCustLastsyncdate($ebizCustomerDate)
                    ->setEcCustSyncStatus(CustomerInterface::EBIZ_CUSTOMER_STATUS_ACTIVE)
                    ->setEcSoftwareId($ebizSoftwareId)
                    ->setEcDivisionId($ebizDivisionId)
                    ->save();

                $this->_ebizchargeLogger->addInfo(__('Success Customer ' . $customerId .
                    ' synced to the Ebicharge Customer'));
                return true;
            } elseif (is_array($ebizCustomer) && isset($ebizCustomer['status']) &&
                isset($ebizCustomer['error_code']) && $ebizCustomer['error_code'] === 2) {
                $this->_ebizchargeLogger->addInfo(__('Success, the  customer ' . $customerId .
                    ' has been updated at Ebizcharge '));
                return true;
            } else {
                $this->_ebizchargeLogger->addError(__('Error occurred during sync of customer ' .
                    $customerId . ' to ebizcharge'));

                return false;
            }
        } catch (Exception $exception) {
            $this->_ebizchargeLogger->addCritical(__('Exception occurred during syncing customer Error: ' .
                $exception->getMessage()));
            return false;
        }
    }

    /**
     * Save Local Customer to Ebizcharge
     *
     * @param mixed $localCustomer
     * @return array|bool|string[]
     * @throws NoSuchEntityException
     */
    public function saveLocalCustomerToEbizcharge($localCustomer = null): array|bool
    {
        return $this->addCustomerToEbizcharge($localCustomer);
    }

    /**
     * Add Customer to EbizCharge
     *
     * @param $localCustomer
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function addCustomerToEbizcharge($localCustomer = null): array
    {
        $ebizConfigFactory = $this->_ebizConfigFactory->create();
        $soapApiFactory = $this->tranApiFactory->create();
        $storeId = $ebizConfigFactory->getStoreId();
        $localCustomer = $this->load($localCustomer->getId());
        $storeId = $localCustomer->getStoreId() ?? $storeId;
        $localCustomerId = $localCustomer->getEntityId();
        /**
         * customerResponse
         */
        $customerResponse = [
            CustomerInterface::EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS =>
                CustomerInterface::EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS_FAILED,
            CustomerInterface::EBIZCHARGE_CUSTOMER_INTERNAL_ID => $localCustomer->getEcCustInternalId() ?? "",
            CustomerInterface::EBIZCHARGE_CUSTOMER_ID => $localCustomer->getEcCustId() ?? "",
            CustomerInterface::EBIZCHARGE_CUSTOMER_TOKEN => $localCustomer->getEcCustToken() ?? "",
            CustomerInterface::EBIZCHARGE_SOFTWARE_ID => $localCustomer->getEcSoftWareId() ?? "",
            CustomerInterface::EBIZCHARGE_DIVISION_ID => $localCustomer->getEcDivisionId() ?? "",
            CustomerInterface::EBIZCHARGE_CUSTOMER_LAST_SYNC_DATE =>
                $soapApiFactory->getCurrentDateTime("Y-m-d H:i:s"),
            CustomerInterface::EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS_CODE =>
                CustomerInterface::EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS_CODE_FAILED,
            CustomerInterface::EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS_ERROR =>
                __('Exception occurred during syncing Customer to EBizCharge Hub.'),
            CustomerInterface::EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS_ERROR_CODE => '000'
        ];

        /**
         * if Customer upload and Module is not active
         */
        $isUploadCustomerActive = $this->uploadCustomerIsActive();
        if (!$isUploadCustomerActive) {
            return $customerResponse;
        }

        try {

            if ($localCustomer->getId()) {
                /** @var  $customerParams */
                $customerParams = $this->prepareCustomerParams($localCustomer);
            } else {
                $guestCustomerId = $this->_checkoutSession->getQuoteId() ?? 0;
                /**
                 * Guest is not allowed to register at EBizCharge
                 */
                /** @var  $customerParams */
                $customerParams = $this->prepareGuestCustomerParams($guestCustomerId) ?? [];
            }

            if (count($customerParams) === 0) {
                return $customerResponse;
            }

            /** @var $ebizCustomer */
            $ebizCustomerId = isset($customerParams['customer']) ? $customerParams['customer']['CustomerId'] : '';
            $ebizCustomer = $localCustomer;

            if (
                !$customerResponse[CustomerInterface::EBIZCHARGE_CUSTOMER_ID] ||
                !$customerResponse[CustomerInterface::EBIZCHARGE_CUSTOMER_INTERNAL_ID] ||
                !$customerResponse[CustomerInterface::EBIZCHARGE_CUSTOMER_TOKEN] ||
                !$customerResponse[CustomerInterface::EBIZCHARGE_DIVISION_ID]
            ) {
                $ebizCustomer = $this->getEbizCustomerById($ebizCustomerId);
                if (!is_object($ebizCustomer)) {
                    /**
                     * Adding Customer
                     */
                    $ebizCustomer = $soapApiFactory->getClient($storeId)->AddCustomer($customerParams);
                    if (is_object($ebizCustomer)) {
                        $this->_ebizchargeLogger->addInfo(__("Success, the customer Id: " .
                            $ebizCustomerId . " has been added to EBizCharge Hub."));
                    }
                }
            }

            if ($ebizCustomer) {
                $customerParams["customerId"] = $ebizCustomerId;
                /**
                 * Update the customer
                 */
                $ebizCustomer = $soapApiFactory->getClient($storeId)->UpdateCustomer($customerParams);

                if (is_object($ebizCustomer)) {
                    $this->_ebizchargeLogger->addInfo(__("Success, the customer Id: " . $ebizCustomerId .
                        " has been updated to EBizCharge Hub.")
                    );
                }
            }
            /**
             * fetching customer
             */
            $ebizCustomer = $this->getEbizCustomerById($ebizCustomerId);
            $ebizCustomerResp = (array)$ebizCustomer;

            /**
             * When the customer exists and only token is missing
             */
            if ($ebizCustomerResp && count($ebizCustomerResp) > 0) {
                $customerResponse[CustomerInterface::EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS] =
                    CustomerInterface::EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS_SUCCESS;
                $customerResponse[CustomerInterface::EBIZCHARGE_CUSTOMER_ID] =
                    isset($ebizCustomerResp["CustomerId"]) ? $ebizCustomerResp["CustomerId"] : "";
                $customerResponse[CustomerInterface::EBIZCHARGE_CUSTOMER_INTERNAL_ID] =
                    isset($ebizCustomerResp["CustomerInternalId"]) ? $ebizCustomerResp["CustomerInternalId"] : "";
                $customerResponse[CustomerInterface::EBIZCHARGE_CUSTOMER_TOKEN] =
                    isset($ebizCustomerResp["CustomerToken"]) ? $ebizCustomerResp["CustomerToken"] : "";
                $customerResponse[CustomerInterface::EBIZCHARGE_DIVISION_ID] =
                    isset($ebizCustomerResp["DivisionId"]) ? $ebizCustomerResp["DivisionId"] : "";
                $customerResponse[CustomerInterface::EBIZCHARGE_SOFTWARE_ID] =
                    isset($ebizCustomerResp["SoftwareId"]) ? $ebizCustomerResp["SoftwareId"] : "";
                $customerResponse[CustomerInterface::EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS_CODE] =
                    CustomerInterface::EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS_CODE_APPROVED;
                $customerResponse[CustomerInterface::EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS_ERROR] = "false";
                $customerResponse[CustomerInterface::EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS_ERROR_CODE] = "";
            }


        } catch (SoapFault $soapFault) {
            $this->_ebizchargeLogger->addCritical(__(
                "Exception occurred during adding Customer to EBizCharge Hub. error: " . $soapFault->getMessage()
            ));
        }
        /** @var  $bind */
        $bind = [
            CustomerInterface::EBIZCHARGE_CUSTOMER_SYNC_STATUS => "1",
            CustomerInterface::EBIZCHARGE_CUSTOMER_INTERNAL_ID => $customerResponse[CustomerInterface::EBIZCHARGE_CUSTOMER_INTERNAL_ID],
            CustomerInterface::EBIZCHARGE_CUSTOMER_ID => $customerResponse[CustomerInterface::EBIZCHARGE_CUSTOMER_ID],
            CustomerInterface::EBIZCHARGE_DIVISION_ID => $customerResponse[CustomerInterface::EBIZCHARGE_DIVISION_ID],
            CustomerInterface::EBIZCHARGE_SOFTWARE_ID => $customerResponse[CustomerInterface::EBIZCHARGE_SOFTWARE_ID],
            CustomerInterfaceAlias::CREATED_IN => $customerResponse[CustomerInterface::EBIZCHARGE_SOFTWARE_ID],
            CustomerInterface::EBIZCHARGE_CUSTOMER_TOKEN => $customerResponse[CustomerInterface::EBIZCHARGE_CUSTOMER_TOKEN],
            CustomerInterface::EBIZCHARGE_CUSTOMER_LAST_SYNC_DATE =>
                $customerResponse[CustomerInterface::EBIZCHARGE_CUSTOMER_LAST_SYNC_DATE]
        ];
        $connection = $this->getResource()->getConnection();
        if($localCustomer->getId()) {
            /** updating the extra fields via Customer Resource Model */
            $connection->update($this->getResource()->getEntityTable(), $bind, $this->getResource()
                ->getConnection()->quoteInto(
                    CustomerInterface::EBIZCHARGE_CUSTOMER_ENTITY_ID . " = ?",
                    $localCustomerId)
            );
        }

        return $customerResponse;
    }

    /**
     * @param $localCustomer
     * @param bool $isEdit
     * @return array
     * @throws NoSuchEntityException
     */
    public function prepareCustomerParams($localCustomer = null, bool $isEdit = false)
    {
        /** Return empty array if null */
        if (!$localCustomer) {
            return [];
        }

        /** @var  $store */
        $store = $this->_storeManager->getStore();
        /** @var $storeId */
        $storeId = $this->_storeManager->getStore()->getId();

        /** @var $userToken */
        $soapClient = $this->tranApiFactory->create()->getClient($storeId);
        $website = $store->getUrl();
        $customerAddresses = $localCustomer->getAddressesCollection();
        $customerAddressId = 0;
        $customerAddress = [];

        if (count($customerAddresses) > 0) {
            foreach ($customerAddresses as $address) {
                $customerAddressId = $address->getEntityId();
            }
        }

        $softwareId = $localCustomer->getEcSoftwareId() ? (string)($localCustomer->getEcSoftwareId()) :
            (string)$this->tranApiFactory->create()->getSoftwareId($storeId);
        $divisionId = $localCustomer->getEcDivisionId() ? (string)($localCustomer->getEcDivisionId()) :
            (string)$this->_ebizConfigFactory->create()->getDivisionID($storeId);

        $quote = null;
        $checkoutSession = $this->_checkoutSession;
        if (Data::getAreaCode() === Area::AREA_ADMINHTML) {
            $checkoutSession = $this->_backendQuoteSession;
        }

        if ($checkoutSession->getQuoteId()) {
            $quoteId = $checkoutSession->getQuoteId();
            $quote = $this->_quoteFactory->create()->load($quoteId);
        }

        $customerDefaultBillingId = $localCustomer->getDefaultBilling() ? $localCustomer->getDefaultBilling() :
            $customerAddressId;
        $customerDefaultShippingId = $localCustomer->getDefaultShipping() ? $localCustomer->getDefaultShipping() :
            $customerAddressId;

        $defaultBilling = null;
        $defaultShipping = null;

        if ($customerDefaultBillingId) {
            /** @var $defaultBilling */
            $defaultBilling = $this->_addressFactory->create()->load($customerDefaultBillingId);
        } else {
            if ($quote && $quote->getIsActive()) {
                $defaultBilling = $quote->getBillingAddress();
                $customerDefaultBillingId = $defaultBilling->getId();
            }
        }

        if ($customerDefaultShippingId) {
            /** @var $defaultShipping */
            $defaultShipping = $this->_addressFactory->create()->load($customerDefaultShippingId);
        } else {
            if ($quote && $quote->getIsActive()) {
                $defaultShipping = $quote->getShippingAddress();
                $customerDefaultShippingId = $defaultShipping->getId();
            }
        }

        /** @var  $billingStreet */
        $billingStreet = $defaultBilling ? $defaultBilling->getStreet() : [];

        /** @var $billingStreet */
        $shippingStreet = $defaultShipping ? $defaultShipping->getStreet() : [];

        /** @var $billingCompanyName */
        $billingCompanyName = "";
        $billingPhone = "";
        $billingFax = "";
        $billingCity = "";

        if ($defaultBilling) {
            $billingCompanyName = $defaultBilling->getCompany() ? $defaultBilling->getCompany() : '';
            $billingPhone = $defaultBilling->getTelephone() ? $defaultBilling->getTelephone() : '';
            $billingFax = $defaultBilling->getFax() ? $defaultBilling->getFax() : '';
            $billingCity = $defaultBilling->getCity() ? $defaultBilling->getCity() : '';
        }
        /** @var  $customAttributes */
        $customAttributes = $localCustomer->getCustomFields();
        $customerNotes = "";

        /** @var  $envoirnMentPrefix */
        $envoirnMentPrefix = $this->_ebizConfigFactory->create()->getEnvoirnmentPrefix($storeId);
        $customerAttributes = $localCustomer->getAttributes();
        $customerCustomFields = [];
        $refinedCustomAttributes = ["confirmation", "dob", "taxvat", "gender"];

        if (count($customerAttributes) > 0) {
            foreach ($customerAttributes as $customerAttribute) {
                $customerAttributeId = isset($customerAttribute["attribute_code"]) ?
                    $customerAttribute["attribute_code"] : "";
                $customerAttributeCaption = isset($customerAttribute["frontend_label"]) ?
                    $customerAttribute["frontend_label"] : "";
                $customerAttributeValue = $localCustomer->getData($customerAttributeId);
                if (in_array($customerAttributeId, $refinedCustomAttributes) && $customerAttributeValue) {

                    if ($customerAttributeId === "gender") {
                        $customerAttributeValue = (int)$customerAttributeValue;
                        if ($customerAttributeValue === 1) {
                            $customerAttributeValue = "Male";
                        }
                        if ($customerAttributeValue === 2) {
                            $customerAttributeValue = "Female";
                        }
                        if ($customerAttributeValue === 3) {
                            $customerAttributeValue = "Not Specified";
                        }
                    }

                    $customerCustomFields[] = [
                        "FieldId" => $customerAttributeId,
                        "FieldCaption" => $customerAttributeCaption,
                        "FieldName" => $customerAttributeId,
                        "FieldValue" => $customerAttributeValue,
                        "FieldType" => "text",
                        "FieldDataType" => "text",
                        "FieldDescription" => $customerAttributeCaption
                    ];
                }
            }
        }
        /** @var $customerParams */
        $customerParams = [
            'securityToken' => $this->tranApiFactory->create()->getUeSecurityToken($storeId),
            'customer' => [
                'FirstName' => trim((string)$localCustomer->getFirstname() ?? ""),
                'LastName' => trim((string)$localCustomer->getLastname() ?? ""),
                'CompanyName' => trim((string)$billingCompanyName ?? ""),
                'Phone' => trim((string)$billingPhone ?? ""),
                'CellPhone' => trim((string)$billingPhone ?? ""),
                'Fax' => trim((string)$billingFax ?? ""),
                'SoftwareId' => (string)$softwareId ?? "",
                'DivisionId' => (string)$divisionId ?? "",
                'Email' => trim((string)$localCustomer->getEmail() ?? ""),
                'WebSite' => trim((string)$website) ? trim((string)$website ?? "") : "0",
                'CustomerCustomFields' => $customerCustomFields,
                'CustomerNotes' => $customerNotes
            ]
        ];

        /** @var  $customerId */
        $customerId = $localCustomer->getEntityId() ?? "";
        $customerEbizId = $localCustomer->getEcCustId() ?? "";

        /**
         * if EBiz Customer is empty
         */
        if (!$localCustomer->getEcCustId()) {
            $customerEbizId = $this->prepareEbizCustomerId($customerId);
            $customerParams["customer"]["CustomerId"] = $customerEbizId;
        } else {
            $customerParams["customer"]["CustomerId"] = $customerEbizId;
        }

        if ($customerDefaultBillingId) {
            $defaultBillingCountryId = $defaultBilling->getCountryId() ? $defaultBilling->getCountryId() : "US";

            $customerParams['customer']['BillingAddress'] = [
                'FirstName' => trim($defaultBilling->getFirstname() ? $defaultBilling->getFirstname() : ''),
                'LastName' => trim($defaultBilling->getLastname() ? $defaultBilling->getLastname() : ''),
                'CompanyName' => trim($defaultBilling->getCompany() ? $defaultBilling->getCompany() : ''),
                'Address1' => isset($billingStreet[0]) ? $billingStreet[0] : '',
                'Address2' => isset($billingStreet[1]) ? $billingStreet[1] : '',
                'City' => trim($billingCity),
                'State' => trim($defaultBilling->getRegion() ? $defaultBilling->getRegion() : ''),
                'ZipCode' => trim($defaultBilling->getPostcode() ? $defaultBilling->getPostcode() : ''),
                'Country' => $this->getCountryName($defaultBillingCountryId),
                'IsDefault' => true
            ];
        }

        if ($customerDefaultShippingId) {
            $defaultShippingCountryId = $defaultShipping->getCountryId() ? $defaultShipping->getCountryId() : "USA";

            $customerParams['customer']['ShippingAddress'] = [
                'FirstName' => trim($defaultShipping->getFirstname() ? $defaultShipping->getFirstname() : ''),
                'LastName' => trim($defaultShipping->getLastname() ? $defaultShipping->getLastname() : ''),
                'CompanyName' => trim($defaultShipping->getCompany() ? $defaultShipping->getCompany() : ''),
                'Address1' => isset($shippingStreet[0]) ? $shippingStreet[0] : '',
                'Address2' => isset($shippingStreet[1]) ? $shippingStreet[1] : '',
                'City' => trim($defaultShipping->getCity() ? $defaultShipping->getCity() : ''),
                'State' => trim($defaultShipping->getRegion() ? $defaultShipping->getRegion() : ''),
                'ZipCode' => trim($defaultShipping->getPostcode() ? $defaultShipping->getPostcode() : ''),
                'Country' => $this->getCountryName($defaultShippingCountryId),
                'IsDefault' => true
            ];
        }

        return $customerParams;
    }

    /**
     * Prepare Envoirnment Prefix
     *
     * @param string $ebizchargeCustomerId
     * @return mixed
     */
    public function prepareEbizCustomerId($ebizchargeCustomerId = '')
    {
        return $this->tranApiFactory->create()->prepareEnvoirnmentPrefix($ebizchargeCustomerId);
    }

    /**
     * @param $customer
     * @return array
     */
    public function syncCustomerToEBizChargeHub($customer = null): array
    {
        $customerParams = [];
        $actionNameRequest = $this->request->getActionName() ?? "";
        $actionName = strtolower($actionNameRequest);

        if ($customer->getId()) {
            $customer = $this->load($customer->getId());
        }
        $this->_ebizchargeLogger->addCritical(__("current customer action: " . $actionName));

        if (in_array($actionName, CustomerInterface::EBIZCHARGE_CUSTOMER_CONTROLLER_ACTIONS)) {
            if ($customer->getEntityId() && empty($customer->getEcCustId())) {
                $this->_ebizchargeLogger->addCritical(__("Adding customer as customer does not exists at EBizCharge Gateway: "));
                return $this->addCustomerToEbizcharge($customer);
            }
        }

        if ($customer->getEntityId() && empty($customer->getEcCustId())) {
            $customerParams = $this->addCustomerToEbizcharge($customer);
        }
        return $customerParams;

    }

    /**
     * @return bool
     */
    public function isCheckoutPage()
    {
        $request = $this->getRequest();
        $fullActionName = $request->getFullActionName();
        $isCheckoutPage = false;
        if (str_contains($fullActionName, "ebizcharge") || $fullActionName === "checkout_index_index") {
            $isCheckoutPage = true;
        }
        return $isCheckoutPage;
    }


    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return string
     */
    public function getRequestActionName()
    {

        return $this->request->getActionName() ?? "";
    }

    /**
     * @return string
     */
    public function getRequestControllerName()
    {

        return $this->request->getControllerName() ?? "";
    }

    /**
     * @return string
     */
    public function getRequestRouteName()
    {

        return $this->request->getRouteName() ?? "";
    }

    /**
     * @return string
     */
    public function getRequestFrontName()
    {

        return $this->request->getFrontName() ?? "";
    }


    /**
     * @param $customer
     * @param $includeProfile
     * @param $includeToken
     * @param $limit
     * @return array|false
     * @throws NoSuchEntityException
     */
    public function getEbizCustomerByEmail(
        $customer = null,
        $includeProfile = 1,
        $includeToken = 1,
        $limit = 10
    )
    {
        /**
         * Customer Collection
         */
        $ebizCustomer = [];
        $ebizChargeFilteredCustomer = false;

        if (!$customer) {
            return $ebizCustomer;
        }
        $customersCollection = [];

        try {

            $customerEmail = $customer->getEmail() ?? "";
            $storeId = $this->getStoreId();
            /** getting Ebizcharge $securityToken */
            $securityToken = $this->tranApiFactory->create()->getUeSecurityToken($storeId);

            $start = 0;
            $countOnly = 1;
            $totalRecords = 0;
            $searchFilters = [
                "SearchFilter" => [
                    "FieldName" => "Email",
                    "ComparisonOperator" => "eq",
                    "FieldValue" => $customerEmail
                ]
            ];

            /** @var  $searchCustomerParams */
            $searchCustomerParams = [
                'securityToken' => $securityToken,
                'filters' => $searchFilters,
                'start' => $start,
                'limit' => $limit,
                'sort' => "",
                'includePaymentMethodProfiles' => $includeProfile,
                'includeCustomerToken' => $includeToken,
                'countOnly' => $countOnly,
            ];

            /** @var  $customerListResult */
            $customerListResult = $this->tranApiFactory->create()->getClient($storeId)
                ->SearchCustomerList($searchCustomerParams)
            ;

            if (is_object($customerListResult->SearchCustomerListResult)) {
                $totalRecords = $customerListResult->SearchCustomerListResult->Count;
                $totalRecords = (int)$totalRecords - (int)$start;
            }
            $countOnly = 0;


            /** Country Only */
            $searchCustomerParams['countOnly'] = $countOnly;

            if ($totalRecords > 100) {
                $limit = 100;
            }

            $searchCustomerParams['limit'] = $limit;

            /** @var  $customerListResult */
            // phpcs:ignore
            // $customerListResult = $this->tranApiFactory->create()->getClient($storeId)
            //->SearchCustomerList($searchCustomerParams);

            /** Total Customers at Ebizcharge **/
            if ($totalRecords > 0 && $countOnly === 0) {
                $requests = 0;
                $maxRequests = ceil($totalRecords / $limit);

                /**
                 * Define Customer Collection
                 */
                /** @var $customersCollection */
                $customersCollection = [];

                do {
                    $searchCustomerParams["countOnly"] = false;
                    $searchCustomerParams["includePaymentMethodProfiles"] = false;
                    $searchCustomerParams["start"] = $start;
                    $searchCustomerParams["limit"] = $limit;

                    /**
                     * Sending request EBizCharge get the latest customers
                     * @Ebizcharge SOAP Api Gateway
                     */
                    /** @var  $ebizchargeCustomers */
                    $ebizchargeCustomers = $this->tranApiFactory->create()->getClient($storeId)
                        ->SearchCustomerList($searchCustomerParams);

                    if (is_array((array)$ebizchargeCustomers->SearchCustomerListResult->CustomerList)) {
                        $customers = (array)$ebizchargeCustomers->SearchCustomerListResult->CustomerList;

                        if (isset($customers["Customer"]) && count($customers) > 1) {
                            foreach ($customers["Customer"] as $customer) {
                                $customersCollection[] = $customer;
                            }
                        } else {

                            $customer = isset($customers["Customer"]) ? (array)$customers["Customer"] : [];
                            if (count($customers) < 2) {
                                $customersCollection[] = array_merge($customersCollection, $customer);
                            }
                        }
                    }
                    /**
                     * Start and Limit
                     */
                    $start = $start + $limit;
                    $requests++;

                } while ($requests < $maxRequests);
            }

            if (isset($customersCollection[0][0])) {
                $customersCollection = $customersCollection[0];
            }

        } catch (SoapFault $soapFault) {

            $this->_ebizchargeLogger->addCritical(__("Exception occurred during fetching customers."));
        }

        if (count($customersCollection) > 0) {
            foreach ($customersCollection as $ebizCustomer) {
                // var_dump("<pre>", $ebizCustomer);
            }
            $ebizChargeFilteredCustomer->GetCustomerResult = $customersCollection[0];
        }

        /**
         * Fetch all Records in an array list End
         */
        return $ebizChargeFilteredCustomer;
    }

    /**
     * @param $localCustomer
     * @return array
     */
    public function updateCustomerAtEbizcharge($localCustomer = null)
    {
        /**
         * customerResponse
         */
        $customerResponse = [
            'status' => "failed",
            'ebiz_internal_id' => "",
            'ebiz_customer_id' => "",
            'ebiz_customer_token' => "",
            'ebiz_division_id' => "",
            'ebiz_software_id' => "",
            'ec_cust_lastsyncdate' => "",
            'status_code' => "000",
            'error' => __('Exception occurred during adding Customer.'),
            'error_code' => "000"
        ];
        /**
         * if Customer upload and Module is not active
         */
        $isUploadCustomerActive = $this->uploadCustomerIsActive();
        if (!$isUploadCustomerActive) {
            return $customerResponse;
        }

        try {
            $error = true;
            /** @var  $storeManager */
            $storeManager = $this->_storeManager->getStore();
            /** @var  $storeId */
            $storeId = $localCustomer->getStoreId() ?? $storeManager->getId();
            $localCustomer = $this->load($localCustomer->getId());

            /** @var get $customerId */
            $customerId = $localCustomer->getEcCustId() ?? "";
            $ebizchargeCustomerInternalId = $localCustomer->getEcCustInternalid() ?? "";
            $customerToken = $localCustomer->getEcCustToken() ?? "";

            /** @var $customerParams */
            $customerParams = (array)$this->prepareCustomerParams($localCustomer, true);
            $updateCustomerParams = $customerParams["customer"] ?? [];
            $customerRequestParams = [
                "securityToken" => $this->tranApiFactory->create()->getUeSecurityToken($storeId),
                "customerId" => $customerId,
                "customerInternalId" => $ebizchargeCustomerInternalId,
                "customer" => $updateCustomerParams
            ];

            /** updating customer call at Ebizcharge **/
            $updateCustomerResults = $this->tranApiFactory->create()->getClient($storeId)
                ->updateCustomer($customerRequestParams)
            ;
            $message = '';
            /** if update customer object */
            if (is_object($updateCustomerResults) && $updateCustomerResults->UpdateCustomerResult) {

                if ($updateCustomerResults->UpdateCustomerResult->Status == 'Success') {
                    $message = "Success this customer has been updated at EBizCharge Hub.";
                    $error = false;
                } else {
                    $errorCode = $updateCustomerResults->UpdateCustomerResult->ErrorCode;
                    $error = true;
                    $this->_ebizchargeLogger->addError(__("Error occurred during updating customer Error: " .
                        $errorCode));
                }
                $updatedCustomerResults = (array)$updateCustomerResults->UpdateCustomerResult;

                /** @var $customerUpdateResults */
                $customerResponse = [
                    'error' => $error,
                    'message' => $message,
                    'status' => $updatedCustomerResults["Status"] ?? false,
                    'ebiz_customer_id' => $updatedCustomerResults["CustomerId"] ?? "",
                    'ebiz_internal_id' => $updatedCustomerResults["CustomerInternalId"] ?? "",
                    'ebiz_customer_token' => $customerToken,
                    'ebiz_division_id' => $customerRequestParams["customer"]["DivisionId"] ?? "",
                    'ebiz_software_id' => $customerRequestParams["customer"]["SoftwareId"] ?? "",
                ];
            }

        } catch (Exception $exception) {
            $this->_ebizchargeLogger->addCritical(__("Exception occured during updating the customer Error: " .
                $exception->getMessage()));
            $customerResponse = [
                'status' => "failed",
                'ebiz_internal_id' => "",
                'ebiz_customer_id' => "",
                'ebiz_customer_token' => "",
                'ebiz_division_id' => "",
                'ebiz_software_id' => "",
                'ec_cust_lastsyncdate' => "",
                'status_code' => "000",
                'error' => __('Exception occurred during adding Customer. Error: ' . $exception->getMessage()),
                'error_code' => "000"
            ];
        }
        return $customerResponse;
    }

    /**
     * Update Ebizcharge Customer to Local
     *
     * @param null|mixed $ebizchargeCustomer
     * @return array
     * @throws NoSuchEntityException
     */
    public function updateEbizchargeCustomerToLocal($ebizchargeCustomer = null)
    {
        /** @var $customerUpdateResults */
        $customerUpdateResults = [
            'error' => true,
            'message' => '',
            'error_code' => '000'
        ];
        /**
         * if Customer download and Module is not active
         */
        $isDownloadCustomerActive = $this->downloadCustomerIsActive();
        if (!$isDownloadCustomerActive) {
            return $customerUpdateResults;
        }

        try {

            /** @var $store */
            $store = $this->_storeManager->getStore();
            /** @var $storeId */
            $storeId = $this->_storeManager->getStore()->getId();
            /** @var $websiteId */
            $websiteId = $this->_storeManager->getWebsite()->getId();
            /** @var $groupId */
            $groupId = $this->_storeManager->getGroup()->getId();
            $customerId = $ebizchargeCustomer->entity_id ? $ebizchargeCustomer->entity_id : 0;

            /** @var $ebizchargeCustomerData */
            $ebizchargeCustomerData = $this->prepareEbizchargeCustomerData($ebizchargeCustomer);

            $firstName = isset($ebizchargeCustomerData['firstname']) ? $ebizchargeCustomerData['firstname'] : '';
            $lastName = isset($ebizchargeCustomerData['lastname']) ? $ebizchargeCustomerData['lastname'] : '';
            $customerEmail = isset($ebizchargeCustomerData['email']) ? $ebizchargeCustomerData['email'] : '';
            $customerSyncStatus = $ebizchargeCustomerData['ebiz_sync_status'] ?? 0;

            $softwareId = $ebizchargeCustomerData['ec_software_id'] ?? SoapApiModelInterface::EBIZCHARGE_MAGENTO_SOFTWARE;
            $divisionId = $ebizchargeCustomerData['ec_division_id'] ?? SoapApiModelInterface::EBIZCHARGE_DIVISION_ID;

            $ebizchargeCustomerId = $ebizchargeCustomerData['ebiz_customer_id'] ?? 0;
            $ebizCustomerToken = $ebizchargeCustomerData['ebiz_customer_token'] ?? '';
            $ebizInternalId = $ebizchargeCustomerData['ebiz_internal_id'] ?? '';
            $ebizLastSyncDate = $ebizchargeCustomerData['ebiz_customer_last_syncdate'] ?? '';

            $billingAddress = $ebizchargeCustomerData['billing_address'] ?? [];
            $shippingAddress = $ebizchargeCustomerData['shipping_address'] ?? [];

            /** @var $localCustomerId */
            $customer = $this->load($customerId);

            /** if customer id given */
            if ($customer->getId()) {
                $customer
                    ->setFirstname($firstName)
                    ->setLastname($lastName)
                    ->setEmail($customerEmail)
                    ->setSoftwareId($softwareId)
                    ->setCreatedIn($softwareId)
                    ->setEcDivisionId($divisionId)
                    ->setEcSoftwareId($softwareId)
                    ->setEcCustId($ebizchargeCustomerId)
                    ->setEcCustToken($ebizCustomerToken)
                    ->setEcCustSyncStatus($customerSyncStatus)
                    ->setEcCustInternalId($ebizInternalId)
                    ->setEcCustLastSyncDate($ebizLastSyncDate);

                /** saving customer to the database */
                $updatedCustomer = $customer->save();

                /** update customer addresses */
                $this->saveCustomerAddress($customerId, $billingAddress, 'billing');

                /** update customer addresses */
                $this->saveCustomerAddress($customerId, $shippingAddress, 'shipping');

                $this->_ebizchargeLogger->addInfo(__(
                    "Ebizcharge Customer has been Updated to the current database"
                ));

                /** @var $customerUpdateResults */
                $customerUpdateResults = [
                    'error' => false,
                    'message' => 'Success the customer has been updated Ebizcharge Customer ID:' .
                        $ebizchargeCustomerId,
                    'error_code' => null
                ];

                return $customerUpdateResults;
            }
        } catch (Exception $exception) {
            $exceptionMessage = __("Exception occured during updating customer to local Exception:" .
                $exception->getMessage());
            $this->_ebizchargeLogger->addCritical($exceptionMessage);

            $customerUpdateResults['message'] = $exceptionMessage;
            $customerUpdateResults['error_code'] = '010';

            return $customerUpdateResults;
        }

        return $customerUpdateResults;
    }

    /**
     * Prepare Params for Payment Method
     *
     * @param mixed $customerId
     * @param array $customerParams
     * @return array
     */
    public function prepareParamsForPaymentMethod($customerId, $customerParams = []): array
    {
        if (!$customerId) {
            return [];
        }

        /** @var  $customer */
        $customer = $this->load($customerId);
        /** @var  $paymentMethodParams */
        $paymentMethodParams = $customerParams;
        $paymentMethodParams['street'] = isset($paymentMethodParams['avs_street']) ?
            [$paymentMethodParams['avs_street']] : [''];
        $paymentMethodParams['ebiz_customer_internal_id'] = $customer->getEcCustInternalId();
        $paymentMethodParams['ebiz_customer_token'] = $customer->getEcCustToken();
        $paymentMethodParams['postcode'] = isset($customerParams['avs_zip']) ? $customerParams['avs_zip'] : '';

        /** Payment Method Params */
        $paymentMethodParams['payment'] = [
            'cc_exp_year' => isset($paymentMethodParams['cc_exp_year']) ? $paymentMethodParams['cc_exp_year'] : '',
            'cc_exp_month' => isset($paymentMethodParams['cc_exp_month']) ? $paymentMethodParams['cc_exp_month'] : '',
            'cc_number' => isset($customerParams['cc_number']) ? $customerParams['cc_number'] : '',
            'cc_holder' => isset($customerParams['cc_owner']) ? $customerParams['cc_owner'] : '',
            'cc_type' => isset($customerParams['cc_type']) ? $customerParams['cc_type'] : '',
            'cc_cid' => isset($customerParams['cc_cid']) ? $customerParams['cc_cid'] : '',
            'default' => isset($customerParams['is_default']) ? $customerParams['is_default'] : 0,
        ];

        return $paymentMethodParams;
    }

    /**
     * Add New Payment Method
     *
     * @param null|mixed $customerId
     * @param array $customerParams
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function addNewPaymentMethod($customerId = null, $customerParams = []): array
    {
        /** @var  $responseMessage */
        // phpcs:ignore
        $responseMessage = __('Un-known error occurred during adding payment method with EBizCharge Gateway, please try again.');
        $error = true;

        /** set Customer Params */
        $this->unsetCustomerRequestParams();
        /**
         * Set Customer Params
         */
        $this->setCustomerRequestParams($customerParams);

        /**
         * Payment Method Response
         */
        $paymentMethodResponse = [
            'error' => true,
            'payment_method_id' => 0,
            'method_name' => '',
            'message' => $responseMessage,
            'response' => [
                'is_default' => false,
                'payment_method_id' => 0,
                'method_name' => '',
                'error_response' => ''
            ]
        ];

        /**
         * is add new Payment Method is not allowed.
         */
        if (!$this->isAddNewCreditCardAllowed()) {
            /** set Customer Params */
            $this->unsetCustomerRequestParams();
            return $paymentMethodResponse;
        }

        try {
            if (!isset($customerParams['payment'])) {
                $customerParams['payment'] = $customerParams;
            }

            /** @var  $paymentParams */
            $paymentParams = isset($customerParams['payment']) ? $customerParams['payment'] : [];
            /** @var  $isAjax */
            $isAjax = isset($customerParams['is_ajax']) && $customerParams['is_ajax'];
            /** @var $isAdminCheckout */
            $isAdminCheckout = $paymentParams['is_admin_checkout'] ?? false;
            /** @var  $isFromCustomerAccount */
            $isFromCustomerAccount = $customerParams['is_from_customer_account'] ?? false;
            $isCheckout = $customerParams["is_checkout"] ?? false;

            $customer = $this->load($customerId);

            $ebizCustomerInternalId = $customer->getEcCustInternalId();
            $ebizCustomerSID = $customer->getEcCustId();
            $ebizCustomerToken = $customer->getEcCustToken();
            $ebizCustomerSoftwareId = $customer->getEcSoftwareId();
            $ebizCustomerDivisionId = $customer->getEcDivisionId();


            if (!$ebizCustomerToken || !$ebizCustomerSID || !$ebizCustomerInternalId) {
                $ebizCustomerSID = $customer->getEcCustId() ?? $this->_checkoutSession->getQuoteId();
                $addCustomer = $this->addCustomerToEbizcharge($customer);
                $customer = $this->load($customerId);

                if ($addCustomer['status'] === "success") {

                    $ebizCustomerInternalId = $customer->getEcCustInternalId() ??
                        $addCustomer["ec_cust_internalid"];
                    $ebizCustomerToken = $customer->getEcCustToken() ??
                        $addCustomer["ec_cust_token"];
                    $ebizCustomerSID = $customer->getEcCustId() ??
                        $addCustomer["ec_cust_id"];

                    /** @var $customerInternalId */
                    $customerParams['ebiz_customer_internal_id'] = $ebizCustomerInternalId ??
                        $addCustomer["ec_cust_internalid"];
                    /** @var $customerToken */
                    $customerParams['ebiz_customer_token'] = $ebizCustomerToken ?? $addCustomer["ec_cust_token"];
                    $customerParams['ebiz_customer_id'] = $ebizCustomerSID ?? $addCustomer["ec_cust_id"];
                }
            }
            $customerParams['ebiz_customer_internal_id'] = $ebizCustomerInternalId;
            /** @var $customerToken */
            $customerParams['ebiz_customer_token'] = $ebizCustomerToken;
            $customerParams['ebiz_customer_id'] = $ebizCustomerSID;

            /**
             * if is checkout or admin checkout
             * then preauth bypassed
             */
            if (!$isCheckout && !$isAdminCheckout) {

                if ($isAjax === true && $this->_customerSession->getIsValid()) {
                    $this->_customerSession->unsIsValid();
                }
                if ($isFromCustomerAccount == true && $this->_customerSession->getIsValid()) {
                    $this->_customerSession->unsIsValid();
                }

                if (isset($customerParams['save_card_anyway']) && $customerParams['save_card_anyway']) {
                    $this->_customerSession->setIsValid(true);
                }

                if (!$this->_customerSession->getIsValid()) {
                    /** @var  $avsCvvValidationResp */
                    $avsCvvValidationResp = $this->processAvsCvvCardValidation($customerId, $customerParams);

                    if ($isAjax === true && !$isFromCustomerAccount) {
                        if (isset($avsCvvValidationResp['cvv_avs_warnings']) &&
                            $avsCvvValidationResp['cvv_avs_warnings'] === false) {
                            $this->_customerSession->setIsValid(true);
                        }
                        return $avsCvvValidationResp;
                    }
                }

                /**
                 * Not from Customer Account
                 */
                if (isset($avsCvvValidationResp['error']) && $avsCvvValidationResp['error'] === true &&
                    !$isFromCustomerAccount) {
                    $paymentMethodResponse['message'] = $avsCvvValidationResp['message'];

                    $resultCode = $avsCvvValidationResp['result_code'] ?? "";
                    if ($isAdminCheckout && $resultCode ===
                        PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_DECLINED) {
                        $paymentMethodResponse['message'] = 'Decline response: ' . $paymentMethodResponse['message'] .
                            '. Please update the entered information or try a different card.';
                        throw new LocalizedException(__($paymentMethodResponse['message']));
                    }

                    throw new LocalizedException(__("Payment authorization failed. " .
                        $paymentMethodResponse['message']));
                }

                $this->_customerSession->unsIsValid();
                $cvvAvsWarnings = $avsCvvValidationResp['cvv_avs_warnings'] ?? false;

                if ($cvvAvsWarnings && $isFromCustomerAccount) {
                    $paymentMethodResponse['message'] = $avsCvvValidationResp['message'];
                    return $paymentMethodResponse;
                }
            }

            $ccType = $paymentParams['cc_type'] ?? '';
            /** @var  $methodName */
            $methodName = $this->_ebizConfigFactory->create()->getPaymentMethodName($ccType);
            $isDefault = isset($paymentParams['default']) ? $paymentParams['default'] : 0;
            $avsAddress = isset($customerParams['street']) ? $customerParams['street'] : "";
            if (!$avsAddress) {
                $ebzcAvsStreet = $paymentParams['ebzc_avs_street'] ?? "";
                $avsAddress = isset($paymentParams['avs_street']) ? $paymentParams['avs_street'] : $ebzcAvsStreet;
                $avsAddress = [$ebzcAvsStreet];

            }
            // phpcs:ignore
            //$avsAddress = isset($customerParams['street']) ? $customerParams['street']: (array)$avsAddress;

            $billingAVS = $this->prepareStreetAddress($avsAddress);
            $expiryMonth = isset($paymentParams['cc_exp_month']) ? $paymentParams['cc_exp_month'] : "";
            $expiryYear = isset($paymentParams['cc_exp_year']) ? $paymentParams['cc_exp_year'] : "";
            $cardExpiration = $expiryYear . "-" . $expiryMonth;

            $ccOwner = $paymentParams['cc_holder'] ?? $paymentParams['cc_owner'] ?? "";

            /** @var $customerInternalId */
            $customerInternalId = $customerParams['ebiz_customer_internal_id'] ?? "";
            /** @var $customerToken */
            $customerToken = $customerParams['ebiz_customer_token'] ?? "";

            $createdAt = $this->tranApiFactory->create()->formateDateTime('', 'Y-m-d H:i:s');
            $createdAt = $this->tranApiFactory->create()->formatTZDateTime($createdAt);
            $modifiedAt = $this->tranApiFactory->create()->formateDateTime('', 'Y-m-d H:i:s');
            $modifiedAt = $this->tranApiFactory->create()->formatTZDateTime($modifiedAt);

            $paymentParams["method_type"] = $methodName;
            $paymentParams["email"] = $customer->getEmail() ?? $this->_checkoutSession->getQuote()->getCustomerEmail();
            $profilePaymentMethodName = $this->preparePaymentMethodNameParam($paymentParams, true);
            /** @var  $paymentMethodName */
            $paymentMethodName = $this->prepareProfileMethodName($profilePaymentMethodName);
            $billingAddress = $this->_checkoutSession->getQuote()->getBillingAddress();
            /**
             * New Payment Method Params
             */
            $paymentMethodParams = [
                'MethodName' => $profilePaymentMethodName,
                'AccountHolderName' => $ccOwner,
                'SecondarySort' => $isDefault,
                'Created' => $createdAt,
                'Modified' => $modifiedAt,
                'AvsStreet' => $billingAVS,
                'AvsZip' => isset($customerParams['postcode']) ? $customerParams['postcode'] :
                    $billingAddress->getPostcode(),
                'CardCode' => isset($paymentParams['cc_cid']) ? $paymentParams['cc_cid'] : '',
                'CardExpiration' => $cardExpiration,
                'CardNumber' => isset($paymentParams['cc_number']) ? $paymentParams['cc_number'] : '',
                'CardType' => isset($paymentParams['cc_type']) ? $paymentParams['cc_type'] : ''
            ];
            /** @var  $paymentMethodId */
            $paymentMethodResponse = $this->tranApiFactory
                ->create()
                ->addCustomerPaymentMethod($customerInternalId, $paymentMethodParams)
            ;
            /** if Payment Method Id is not null */
            if ($paymentMethodResponse['error'] === false) {
                $paymentMethodId = $paymentMethodResponse['method_id'];
                $error = false;
                $responseMessage = __('Success payment method has been added successfully.');
                $this->_ebizchargeLogger->addInfo(__(
                    "Success payment method has been added successfully. Method ID: " . $paymentMethodId
                ));

                if ($isDefault) {
                    $defaultPaymentMethod = $this->tranApiFactory->create()->setDefaultPaymentMethod(
                        $customerToken,
                        $paymentMethodId
                    );
                    $responseMessage = __(
                        'Success payment method has been added successfully and set as a default Payment Method'
                    );
                    // phpcs:ignore
                    $this->_ebizchargeLogger->addInfo(
                        __("Success payment method has been added successfully and has set as a default
                        payment Method ID: " . $paymentMethodId)
                    );
                }

                /** set Customer Params */
                $this->unsetCustomerRequestParams();

                /** sending back the message to Logger */
                $this->_ebizchargeLogger->addInfo($responseMessage);

                /** @var $paymentMethodResponse */
                $paymentMethodResponse = [
                    'error' => $error,
                    'payment_method_id' => $paymentMethodId,
                    'message' => $responseMessage,
                    'response' => [
                        'is_default' => $defaultPaymentMethod ?? false,
                        'payment_method_id' => $paymentMethodId,
                        'method_name' => $paymentMethodParams['MethodName'],
                        'error_response' => ''
                    ]
                ];

            } else {
                $paymentMethodResponse['error'] = true;
                $paymentMethodResponse['message'] = __('Error occurred during adding payment method. ' .
                    $paymentMethodResponse['message']);
                $paymentMethodResponse['response'] = [
                    'is_default' => false,
                    'payment_method_id' => 0,
                    'method_name' => '',
                    'error_response' => $paymentMethodResponse['message']
                ];
            }

        } catch (SoapFault $soapFault) {
            $this->_ebizchargeLogger->addCritical(__('Error during adding payment method Error:' .
                $soapFault->getMessage()));
            $paymentMethodResponse['message'] = __('Error during adding payment method Error:' .
                $soapFault->getMessage());
            $paymentMethodResponse['response'] = [
                'is_default' => false,
                'payment_method_id' => 0,
                'method_name' => '',
                'error_response' => $soapFault->getMessage()
            ];

        }
        return $paymentMethodResponse;
    }

    /**
     * Unset Request Params
     *
     * @return void
     */
    public function unsetCustomerRequestParams()
    {
        /** reset Customer Session Params */
        if ($this->_customerSession->getRequestParams() !== '') {
            $this->_customerSession->setRequestParams('');
        }
    }

    /**
     * Customer Request Params
     *
     * @param array $customerParams
     */
    public function setCustomerRequestParams(array $customerParams = [])
    {
        /** Set Customer Request Params */
        $this->_customerSession->setRequestParams($customerParams);
    }

    /**
     * Is Add New Credit Card Allowed
     *
     * @return false
     * @throws NoSuchEntityException
     */
    public function isAddNewCreditCardAllowed()
    {
        $isAddNewPaymentMethodAllowed = false;
        $storeId = $this->_ebizConfigFactory->create()->getStoreId();
        $isModuleActive = $this->_ebizConfigFactory->create()->isActive($storeId);
        $isCreditCardsAllowed = $this->_ebizConfigFactory->create()->isCreditCardEnabled($storeId);
        $isAddNewPaymentMethod = $this->_ebizConfigFactory->create()->getIsSaveCreditCards($storeId);

        if ($isModuleActive && $isAddNewPaymentMethod && $isCreditCardsAllowed) {
            $isAddNewPaymentMethodAllowed = true;
        } else {
            // phpcs:ignore
            $this->$this->_ebizchargeLogger->addInfo(__("Add new payment method credit cards are not allowed, please enable save cards from admin configuration. "));
        }
        return $isAddNewPaymentMethodAllowed;
    }

    /**
     * Process AVS CVV Crd Validation
     *
     * @param null|mixed $customerId
     * @param array $customerParams
     * @return array
     * @phpcs:disable
     */
    public function processAvsCvvCardValidation(mixed $customerId = null, array $customerParams = []): array
    {
        /**
         * @var $cvvAvsCardValidationResponse
         */
        $cvvAvsCardValidationResponse = [
            'error' => true,
            'status' => false,
            'valid' => false,
            'cvv_code' => '',
            'avs_code' => '',
            'cavv_code' => '',
            'cvv_avs_warnings' => false,
            'result_code' => 'D',
            'message' => __('Not valid avs|cvv response.'),
            'response' => [
                'avs' => [
                    __('Address: Do not Match'),
                    __('Zip/Postal Code: Do not Match')
                ],
                'cvv' => [
                    "code" => "N",
                    "label" => __("No match; indicates the code entered is incorrect."),
                    "msg" => __("Not a match;")
                ]
            ]
        ];

        /** @var $defaultPaymentMethod */
        try {

            if (!isset($customerParams["payment"])) {
                $customerParams["payment"] = $customerParams;
                $customerParams['payment']['avs_zip'] = $customerParams['ebzc_avs_zip'] ??
                    $customerParams['ebzc_avs_zip'] ?? $customerParams['postcode'] ?? "";
                $customerParams['payment']['avs_street'] = $customerParams['ebzc_avs_street'] ??
                    $customerParams['ebzc_avs_street'] ?? isset($customerParams['street'][0])?
                    $customerParams['street'][0]:"";
                $customerParams['payment']['cc_owner'] = $customerParams['cc_owner'] ??
                    $customerParams['payment']['cc_owner'] ?? $customerParams['payment']['cc_holder'] ?? "";

            }
            /** @var  $isCardVerifyEnabled */
            $isCardVerifyEnabled = $this->isVerifyCreditCardBeforeSave();
            $customerParams['payment']['avs_zip'] = $customerParams['payment']['ebzc_avs_street'] ??
                $customerParams['postcode'] ?? "";
            $customerParams['payment']['avs_street'] = $customerParams['payment']['ebzc_avs_street'] ??
                $customerParams['street'][0];
            $customerParams['payment']['cc_owner'] = $customerParams['payment']['cc_holder'] ??
                $customerParams['payment']['cc_owner'] ?? $customerParams['payment']['cc_holder'] ?? "";


            if ($isCardVerifyEnabled === false) {
                $cvvAvsCardValidationResponse["error"] = false;
                $cvvAvsCardValidationResponse["status"] = true;
                $cvvAvsCardValidationResponse["valid"] = true;
                $cvvAvsCardValidationResponse["message"] =
                    __("AVS|CVV is disabled from gateway, so not checking for AVS CVV");
                $this->_ebizchargeLogger->addCritical(__("AVS|CVV check is disabled from EbizCharge payment hub."));
                return $cvvAvsCardValidationResponse;
            }

            /**
             * if Verify Card Enabled
             */
            if ($isCardVerifyEnabled) {
                /** Customer Params add Customer Id */
                $customerParams['customer_id'] = $customerId;

                /** @var  $command */
                $command = PaymentInterface::EBIZCHARGE_COMMAND_AUTHONLY;

                /**
                 * First Run Pre Auth Transaction if every thing goes OK then
                 * We need to Void that Transaction
                 */
                /** @var  $preAuth */
                $preAuthTransactionResults = $this->runPreAuthTransaction($customerParams, $command);

                /** Pre Auth Transaction Results */
                if ($preAuthTransactionResults['error'] === false) {

                    /** @var  $transactionParams */
                    $transactionParams = $preAuthTransactionResults['response'] ?? [];
                    $preAuthResponse = $preAuthTransactionResults['response'] ?? [];
                    $resultCode = $preAuthResponse['ResultCode'] ?? "";
                    $cvvCardCodeResult = $preAuthResponse['CardCodeResultCode'] ?? "";
                    $avsCardCodeResult = $preAuthResponse['AvsResultCode'] ?? "";
                    $cardCodeLevelResult = $preAuthResponse['CardLevelResultCode'] ?? "";
                    $cvvAvsCardValidationResponse['result_code'] = $resultCode;
                    $cvvAvsCardValidationResponse['cvv_code'] = $cvvCardCodeResult;
                    $cvvAvsCardValidationResponse['avs_code'] = $avsCardCodeResult;
                    $cvvAvsCardValidationResponse['cavv_code'] = $cardCodeLevelResult;

                    $cvvResults = $preAuthResponse['CardCodeResult'] ?? "";

                    $isCvvAvsWarnedOutPut = $this->_paymentModel->isWarnedCvvAvsResponse(
                        $cvvCardCodeResult,
                        $avsCardCodeResult
                    );
                    $cvvAvsCardValidationResponse['cvv_avs_warnings'] = $isCvvAvsWarnedOutPut;

                    /** @var  $avsZipResults */
                    $avsZipResults = explode("&", $preAuthResponse['AvsResult'] ?? " & ");
                    /** @var  $validAvsResp */
                    $validAvsResponses = $this->getValidAvsCvvResponses();


                    /** if transaction Approved */
                    if (isset($transactionParams['ResultCode']) &&
                        $transactionParams['ResultCode'] ===
                        PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_AUTHENTICATED &&
                        in_array($avsCardCodeResult, $validAvsResponses) &&
                        $cvvCardCodeResult === PaymentInterface::CVV2_CARD_CODE_RESULT_M
                    ) {

                        /** @var  $transactionId */
                        $transactionId = $preAuthTransactionResults['transaction_id'] ??
                            $preAuthTransactionResults['transaction_id']
                        ;
                        /** Customer params update */
                        $customerParams['RefNum'] = $transactionId;
                        /**
                         * Void the Pre Auth Transaction
                         */
                        $command = PaymentInterface::EBIZCHARGE_METHOD_TRANSACTION_TYPE_VOID;

                        /** @var  $preAuth */
                        $preAuthVoidTransaction = $this->voidPreAuthTransaction($customerParams, $command);

                        $cvvAvsCardValidationResponse['error'] = false;
                        $cvvAvsCardValidationResponse['status'] = true;
                        $cvvAvsCardValidationResponse['valid'] = true;

                        $cvvAvsCardValidationResponse['message'] = $preAuthResponse['Error'] ?? '';
                        $cvvAvsCardValidationResponse['response'] = [
                            'avs' => [
                                __($avsZipResults[0] ?? $cvvAvsCardValidationResponse['response']['avs'][0]),
                                __($avsZipResults[1] ?? $cvvAvsCardValidationResponse['response']['avs'][1])
                            ],
                            'cvv' => [
                                "code" => $cardCodeLevelResult,
                                "label" => __($cvvResults),
                                "msg" => __($cvvResults)
                            ],
                            'results' => $preAuthTransactionResults['response'] ?? []
                        ];

                    } else {

                        if (isset($transactionParams['ResultCode'])
                            && $transactionParams['ResultCode'] ===
                            PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_AUTHENTICATED
                            && !in_array($avsCardCodeResult, $validAvsResponses)
                            && $cvvCardCodeResult === PaymentInterface::CVV2_CARD_CODE_RESULT_M
                        ) {
                            /** Customer params update */
                            $customerParams['RefNum'] = $preAuthTransactionResults['transaction_id'] ?? '';

                            /**
                             * Void the Pre Auth Transaction
                             */
                            $command = PaymentInterface::EBIZCHARGE_METHOD_TRANSACTION_TYPE_VOID;

                            /** @var  $preAuth */
                            $preAuthVoidTransaction = $this->voidPreAuthTransaction($customerParams, $command);

                            if (Data::getAreaCode() !==
                                PaymentInterface::EBIZCHARGE_FRAMEWORK_STATE_TYPE_ADMINHTML) {
                                $cvvAvsCardValidationResponse['error'] = false;
                                $cvvAvsCardValidationResponse['status'] = true;
                                $cvvAvsCardValidationResponse['valid'] = true;
                                $cvvAvsCardValidationResponse['cvv_avs_warnings'] = false;

                                $cvvAvsCardValidationResponse['message'] = $preAuthResponse['Error'] ?? '';
                                $cvvAvsCardValidationResponse['response'] = [
                                    'avs' => [
                                        __($avsZipResults[0] ??
                                            $cvvAvsCardValidationResponse['response']['avs'][0]),
                                        __($avsZipResults[1] ??
                                            $cvvAvsCardValidationResponse['response']['avs'][1])
                                    ],
                                    'cvv' => [
                                        "code" => $cardCodeLevelResult,
                                        "label" => __($cvvResults),
                                        "msg" => __($cvvResults)
                                    ],
                                    'results' => $preAuthTransactionResults['response'] ?? []
                                ];
                            }
                        }

                        if (isset($transactionParams['ResultCode'])
                            && $transactionParams['ResultCode'] !==
                            PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_AUTHENTICATED) {

                            if ($transactionParams['ResultCode'] ===
                                PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_ERROR) {
                                /** Customer params update */
                                $customerParams['RefNum'] = $preAuthTransactionResults['transaction_id'] ?? '';

                                /**
                                 * Void the Pre Auth Transaction
                                 */
                                $command = PaymentInterface::EBIZCHARGE_METHOD_TRANSACTION_TYPE_VOID;
                                /** @var  $preAuth */
                                $preAuthVoidTransaction = $this->voidPreAuthTransaction($customerParams, $command);
                            }
                            $errorMessage = isset($preAuthResponse['Error']) ? $preAuthResponse['Error'] : '';
                            $errorMessage = $errorMessage !== "Approved" ? $errorMessage : "AVS mismatched";
                            $cvvAvsCardValidationResponse['error'] = true;
                            $cvvAvsCardValidationResponse['status'] = false;
                            $cvvAvsCardValidationResponse['valid'] = false;
                            $cvvAvsCardValidationResponse['message'] = $errorMessage;
                            $cvvAvsCardValidationResponse['response'] = [
                                'avs' => [
                                    __($avsZipResults[0] ?? $cvvAvsCardValidationResponse['response']['avs'][0]),
                                    __($avsZipResults[1] ?? $cvvAvsCardValidationResponse['response']['avs'][1])
                                ],
                                'cvv' => [
                                    "code" => $cardCodeLevelResult,
                                    "label" => __($cvvResults),
                                    "msg" => __($cvvResults)
                                ],
                                'results' => $preAuthTransactionResults['response'] ?? []
                            ];
                        }
                    }

                } else {
                    $cvvAvsCardValidationResponse['message'] = __("Not valid card response.");
                }
            } else {
                $cvvAvsCardValidationResponse['error'] = false;
                $cvvAvsCardValidationResponse['status'] = true;
                $cvvAvsCardValidationResponse['valid'] = true;
                $cvvAvsCardValidationResponse['message'] = 'Pre auth is not applicable on this card.';
                $cvvAvsCardValidationResponse['response'] = [];
            }

        } catch (Exception $exception) {
            $this->_ebizchargeLogger->addCritical(__("Exception Error: " . $exception->getMessage()));
            $cvvAvsCardValidationResponse['message'] = __("Exception Error: " . $exception->getMessage());
        }

        return $cvvAvsCardValidationResponse;
    }

    /**
     * Is Verify Credit Cards Before Save
     *
     * @param int $storeId
     * @return bool
     */
    public function isVerifyCreditCardBeforeSave($storeId = 0): bool
    {
        $storeId = $storeId ? $storeId : $this->getStoreId();
        /** @var  $merchantTransanctionData */
        $merchantTransanctionData = $this->getMerchantTransactionData($storeId);

        $isAvsCvvEnabled = $this->_ebizConfigFactory->create()->isAvsCvvZipEnabled($storeId);

        /** @var  $verifyCreditCard */
        //$verifyCreditCard = false;

        //if (isset($merchantTransanctionData['VerifyCreditCardBeforeSaving']) || $isAvsCvvEnabled) {
        //    $verifyCreditCard = $merchantTransanctionData['VerifyCreditCardBeforeSaving'];
        //}
        //return $verifyCreditCard;
        /**
         * $enableAVSWarnings = $merchantTransanctionData['EnableAVSWarnings'] ?? false;
         * $enableCVVWarnings = $merchantTransanctionData['EnableCVVWarnings'] ?? false;
         * $verifyCreditCardBeforeSaving = $merchantTransanctionData['VerifyCreditCardBeforeSaving'] ?? false;
         *
         * return $enableAVSWarnings && $enableCVVWarnings && $verifyCreditCardBeforeSaving && $isAvsCvvEnabled;
         */

        /** @var  $enableAVSWarnings */
        $enableAVSWarnings = $merchantTransanctionData[SoapApiModelInterface::EBIZCHARGE_MERCHANT_DATA_AVS_WARNINGS_ENABLED] ?? false;
        /** @var  $verifyCreditCardBeforeSaving */
        $verifyCreditCardBeforeSaving = $merchantTransanctionData[SoapApiModelInterface::EBIZCHARGE_MERCHANT_DATA_VERIFIY_CREDIT_CARD_BEFORE_SAVING] ?? false;

        return $enableAVSWarnings && $verifyCreditCardBeforeSaving;
    }

    /**
     * Get Merchant Transaction Data
     *
     * @param mixed $storeId
     * @return array|null
     */
    public function getMerchantTransactionData($storeId = 0)
    {
        return $this->tranApiFactory->create()->getMerchantTransactionInfo($storeId);
    }

    /**
     * Run Pre Auth Transaction
     *
     * @param array $customerParams
     * @param string $command
     * @param bool $isAvsFullAmountEnabled
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function runPreAuthTransaction(
        $customerParams = [],
        $command = PaymentInterface::EBIZCHARGE_COMMAND_TYPE_AUTHONLY,
        $isAvsFullAmountEnabled = false
    ): array
    {
        /** @var
         * $transactionParams
         */
        $transactionResp = [
            "error" => true,
            "status" => false,
            "message" => "",
            "transaction_id" => "",
            "response" => []
        ];

        try {
            $storeId = $this->getStoreId();
            /** @var  $paymentParams */
            $transactionParams = $this->preparePreAuthPaymentParams($customerParams, $command, $isAvsFullAmountEnabled);
            /** @var  $preAuthTransactionParams */
            $preAuthTransactionParams = [
                'securityToken' => $this->tranApiFactory->create()->getUeSecurityToken($storeId),
                'tran' => $transactionParams
            ];
            $storeId = $this->_ebizConfigFactory->create()->getStoreId() ?? "0";

            /** @var  $preAuthTransactionResp */
            $preAuthTransactionResp = $this->tranApiFactory->create()
                ->getClient($storeId)
                ->runTransaction($preAuthTransactionParams)
            ;

            /**
             * Run Transaction Results from EBizCharge Api
             */
            if ($preAuthTransactionResp->runTransactionResult) {
                /** @var  $transactionResults */
                $transactionResults = (array)$preAuthTransactionResp->runTransactionResult;
                /** @var  $transactionResp */
                $transactionResp = [
                    "error" => false,
                    "status" => true,
                    "message" => "Success, the transaction response found",
                    "transaction_id" => isset($transactionResults['RefNum']) ? $transactionResults['RefNum'] : "",
                    "response" => $transactionResults
                ];
                $this->_ebizchargeLogger->addInfo(__(
                    "Success, the pre auth transaction has been made successfully."
                ));

            } else {
                /** @var  $transactionResults */
                $transactionResults = (array)$preAuthTransactionResp->runTransactionResult;
                $transactionResp = [
                    "error" => true,
                    "status" => false,
                    "message" => "Error occurred during fetching results",
                    "transaction_id" => "",
                    "response" => $transactionResults
                ];
                $this->_ebizchargeLogger->addInfo(__("Error occurred during fetching results"));
            }

        } catch (SoapFault $soapException) {

            $this->_ebizchargeLogger->addCritical(__("Exception during pre auth transaction Error: " .
                $soapException->getMessage()));
            /**
             * Trnsaction response
             */
            $transactionResp = [
                "error" => true,
                "status" => true,
                "message" => "Error during transaction Error: " . $soapException->getMessage(),
                "transaction_id" => "",
                "response" => []
            ];
        }

        return $transactionResp;
    }

    /**
     * Prepare Pre Auth Payment Params
     *
     * @param array $customerPaymentParams
     * @param string $command
     * @param bool $isAvsFullAmountEnabled
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function preparePreAuthPaymentParams(
        $customerPaymentParams = [],
        $command = "Authonly",
        $isAvsFullAmountEnabled = false
    )
    {
        /** @var  $paymentParams */
        $paymentParams = $customerPaymentParams['payment'];
        $isCheckout = $customerPaymentParams['is_checkout'] ?? false;
        $isAdminCheckout = $customerPaymentParams['is_admin_checkout'] ?? false;
        $customerId = $customerPaymentParams["customer_id"] ?? "";

        $quote = null;
        $checkoutSession = $this->_checkoutSession;
        if (Data::getAreaCode() === Area::AREA_ADMINHTML) {
            $checkoutSession = $this->_backendQuoteSession;
        }

        if ($checkoutSession->getQuoteId()) {
            $quoteId = $checkoutSession->getQuoteId();
            $quote = $this->_quoteFactory->create()->load($quoteId);
        }

        if (isset($paymentParams['customer_account'])) {

            /** @var  $customerId */
            $customerId = isset($customerPaymentParams['customer_id']) ? $customerPaymentParams['customer_id'] : null;
            /** @var  $customer */
            $customer = $this->load($customerId);

            /** @var  $ebizCustomerId */
            $ebizCustomerId = $customerPaymentParams['ebiz_customer_id'] ?? "";

            /** @var  $billingAddress */
            $billingAddress = $customer->getPrimaryBillingAddress();
            /** @var  $shippingAddress */
            $shippingAddress = $customer->getPrimaryShippingAddress();

        } else {

            /** @var  $ebizCustomerId */
            if (!$customerId) {
                $customerId = $quote ? $quote->getCustomerId() : "";
            }
            $customer = $this->load($customerId);
            $ebizCustomerId = $customer->getEcCustId();

            if ($quote && $quote->getCustomerIsGuest()) {
                /** @var  $ebizCustomerId */
                $ebizCustomerId = CustomerInterface::GUEST_CUSTOMER_LAST_NAME;
                $isCheckout = true;
            }
            /** @var  $billingAddress */
            $billingAddress = $quote ? $quote->getBillingAddress() : null;
            /** @var  $shippingAddress */
            $shippingAddress = $quote ? $quote->getShippingAddress() : null;
        }

        /**
         * Pre Auth Order Number
         */
        $preAuthOrderNumber = $this->preparePreAuthOrderNumber();
        $ccNumber = isset($paymentParams['cc_number']) ? $paymentParams['cc_number'] : '';
        /** @var  $preAuthOrderNumber */
        $preAuthOrderNumber = strtoupper($preAuthOrderNumber . substr((string)$ccNumber, 12));

        /** @var  $refNumber */
        $refNumber = isset($customerPaymentParams['RefNum']) ? $customerPaymentParams['RefNum'] : '';
        $ccExpMonth = isset($paymentParams['cc_exp_month']) ? $paymentParams['cc_exp_month'] : '';
        $ccExpYear = isset($paymentParams['cc_exp_year']) ? $paymentParams['cc_exp_year'] : '';
        //$isAvsFullAmountEnabled = $this->_ebizConfigFactory->create()->isAvsFullAmountEnabled();
        //$authAmount = $isAvsFullAmountEnabled && ($isCheckout || $isAdminCheckout)
        $grandTotal = PaymentInterface::EBIZCHARGE_TRANSACTION_PRE_AUTH_AMOUNT;
        if ($quote) {
            $grandTotal = $quote->getGrandTotal();
        }
        $authAmount = $isAvsFullAmountEnabled
            ? $grandTotal
            : PaymentInterface::EBIZCHARGE_TRANSACTION_PRE_AUTH_AMOUNT;

        /** @var  $preAuthPaymentParams */
        $preAuthPaymentParams = [
            'CustReceiptName' => '',
            'IsRecurring' => false,
            'InventoryLocation' => '',
            'IfAuthExpired' => '',
            'IgnoreDuplicate' => false,
            'LineItems' => [
                'LineItem' => [
                    'DiscountRate' => 0,
                    'ProductRefNum' => 'ref-preauth-product',
                    'SKU' => 'pre-auth-product',
                    'ProductName' => 'PreAuth Product',
                    'Description' => 'PreAuth Product',
                    'DiscountAmount' => 0,
                    'TaxRate' => 0,
                    'UnitOfMeasure' => '',
                    'UnitPrice' => $authAmount,//PaymentInterface::EBIZCHARGE_TRANSACTION_PRE_AUTH_AMOUNT,
                    'Qty' => 1,
                    'Taxable' => 'N',
                    'TaxAmount' => 0
                ]
            ],
            'Details' => [
                'NonTax' => true,
                'Tax' => 0,
                'Shipping' => 0.0,
                'ShipFromZip' => $paymentParams['avs_zip'] ?? ($paymentParams['ebzc_avs_zip'] ?? ''),
                'PONum' => $preAuthOrderNumber,
                'OrderID' => $preAuthOrderNumber,
                'Invoice' => $preAuthOrderNumber,
                'Duty' => 0,
                'Subtotal' => $authAmount, //PaymentInterface::EBIZCHARGE_TRANSACTION_PRE_AUTH_AMOUNT,
                'Discount' => 0,
                'Comments' => 'PreAuth Product',
                'Description' => 'PreAuth Product',
                'Currency' => $this->getStore()->getCurrentCurrencyCode(),
                'Clerk' => 'EBizCharge',
                'Amount' => $authAmount, //PaymentInterface::EBIZCHARGE_TRANSACTION_PRE_AUTH_AMOUNT,
                'AllowPartialAuth' => false,
                'Terminal' => 'pre-auth-product',
                'Tip' => 0
            ],
            'Software' => SoapApiModelInterface::EBIZCHARGE_MAGENTO_SOFTWARE,
            'CustReceipt' => false,
            'CustomerID' => $ebizCustomerId,

            'CreditCardData' => [
                'CardNumber' => $ccNumber,
                'CardExpiration' => $ccExpMonth . substr((string)$ccExpYear, 2),
                'CardCode' => $paymentParams['cc_cid'] ?? '',
                'CardType' => $paymentParams['cc_type'] ?? '',
                'AvsZip' => $paymentParams['avs_zip'] ?? ($paymentParams['ebzc_avs_zip'] ?? ''),
                'AvsStreet' => $paymentParams['avs_street'] ?? ($paymentParams['ebzc_avs_street'] ?? ''),
                'InternalCardAuth' => false,
                'CardPresent' => true
            ],
            'Command' => $command,
            'ClientIP' => $this->_remoteAddress->getRemoteAddress(false),
            'AccountHolder' => $paymentParams['cc_owner'] ?? ''
        ];

        if ($billingAddress) {
            $preAuthPaymentParams['BillingAddress'] = [
                'City' => $billingAddress->getData("city") ?? "",
                'Company' => $billingAddress->getData("company") ?? "",
                'Country' => $billingAddress->getData("country_id") ?? "",
                'Email' => $customer->getEmail() ?? "",
                'Fax' => $billingAddress->getData("telephone") ?? "",
                'FirstName' => $billingAddress->getData("firstname") ?? "",
                'LastName' => $billingAddress->getData("lastname") ?? "",
                'Phone' => $billingAddress->getData("telephone") ?? "",
                'State' => $billingAddress->getData("region") ?? "",
                //  'Street' => $billingAddress->getData("street"),
                'Street' => $paymentParams['avs_street'] ?? ($paymentParams['ebzc_avs_street'] ?? ''),
                'Street2' => '',
                'Zip' => $paymentParams['avs_zip'] ?? ($paymentParams['ebzc_avs_zip'] ?? ''),
            ];
        } else {
            $preAuthPaymentParams['BillingAddress'] = [];
        }

        if ($shippingAddress) {
            $preAuthPaymentParams['ShippingAddress'] = [
                'City' => $shippingAddress->getData("city") ?? "",
                'Company' => $shippingAddress->getData("company") ?? "",
                'Country' => $shippingAddress->getData("country_id") ?? "",
                'Email' => $customer->getEmail() ?? "",
                'Fax' => $shippingAddress->getData("telephone") ?? "",
                'FirstName' => $shippingAddress->getData("firstname") ?? "",
                'LastName' => $shippingAddress->getData("lastname") ?? "",
                'Phone' => $shippingAddress->getData("telephone") ?? "",
                'State' => $shippingAddress->getData("region") ?? "",
                'Street' => $paymentParams['avs_street'] ?? ($paymentParams['ebzc_avs_street'] ?? ''),
                'Street2' => '',
                'Zip' => $paymentParams['avs_zip'] ?? ($paymentParams['ebzc_avs_zip'] ?? ''),
            ];
        } else {
            $preAuthPaymentParams['ShippingAddress'] = [];
        }

        /** update Reference Number of a transactions */
        if ($refNumber !== "") {
            $preAuthPaymentParams["RefNum"] = $refNumber;
        }

        return $preAuthPaymentParams;
    }

    // phpcs:enable

    /**
     * Prepare Pre Auth Order Number
     *
     * @return string
     */
    public function preparePreAuthOrderNumber()
    {
        /** @var  $orderFactory */
        $orderFactory = $this->_orderFactory->create();
        /** @var  $preAuthOrderNumber */
        $preAuthOrderNumber = $orderFactory->preparePreAuthOrderNumber();

        return $preAuthOrderNumber;
    }

    /**
     * Get Valid Avs Cvv Responses
     *
     * @return array|string[]
     */
    public function getValidAvsCvvResponses()
    {
        return $this->_paymentModel->validAvsResultCodes;
    }

    /**
     * Void Pre Auth Transaction
     *
     * @param array $transactionParams
     * @param string $command
     * @param bool $isAvsFullAmountEnabled
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function voidPreAuthTransaction(
        $transactionParams = [],
        $command = "void",
        $isAvsFullAmountEnabled = false
    )
    {
        /**
         * Run Pre Auth Transactions
         */
        $transactionResponse = $this->runPreAuthTransaction($transactionParams, $command, $isAvsFullAmountEnabled);

        return $transactionResponse;
    }

    /**
     * Prepare Street Address
     *
     * @param array $streetAddress
     * @return string
     */
    public function prepareStreetAddress($streetAddress = [])
    {
        $address = '';
        if (count($streetAddress) > 0) {
            foreach ($streetAddress as $streetAddress) {
                $address .= $streetAddress . ' ';
            }
        }

        return trim($address);
    }

    /**
     * @param $paymentMethod
     * @param $isAddNew
     * @return mixed|string
     */
    public function preparePaymentMethodNameParam($paymentMethod = [], $isAddNew = false)
    {
        /** @var  $methodNameStr */
        $methodNameStr = "";
        if (!$isAddNew) {
            /** @var  $paymentMethodName */
            $paymentMethodName = isset($paymentMethod["method_name"]) ? $paymentMethod["method_name"] : "";

            $methodNameStr = $paymentMethodName;
            $paymentMethodNameJson = str_replace("'", "\"", $paymentMethodName ?? "");
            $paymentMethodNameObj = json_decode($paymentMethodNameJson);

            $ccHolder = isset($paymentMethod["cc_holder"]) ? $paymentMethod["cc_holder"] : "";
            $ccOwner = $paymentMethod["cc_owner"] ?? $ccHolder;

            if (is_object($paymentMethodNameObj)) {
                $paymentMethodNameObj->b = $ccOwner;
                $methodNameStr = isset($paymentMethodNameObj->a) ? str_replace(" ", "-", $paymentMethodNameObj->a) : $ccOwner;
                $methodNameStr .= isset($paymentMethodNameObj->b) ? "-" . $paymentMethodNameObj->b : "-" . $ccOwner;
            }
            if ($paymentMethodName === "") {
                $customerId = isset($paymentMethod["customer_id"]) ? $paymentMethod["customer_id"] : "";
                $customer = $this->load($customerId);
                $customerEmail = $customer->getEmail();
                $methodName = isset($paymentMethod["method_type"]) ? $paymentMethod["method_type"] : "";
                $ccNumber = $paymentMethod['cc_number'] ?? $paymentMethod['card_number'];
                $methodNameStr = $methodName . "-" . substr((string)$ccNumber, -4);
                if (!empty($ccHolder)) {
                    $methodNameStr .= "-" . $ccHolder;
                }
            }
        } else {
            $ccHolder = isset($paymentMethod["cc_holder"]) ? $paymentMethod["cc_holder"] : "";
            $ccHolder = isset($paymentMethod["cc_owner"]) ? $paymentMethod["cc_owner"] : $ccHolder;
            $customerId = isset($paymentMethod["customer_id"]) ? $paymentMethod["customer_id"] : "";

            $customer = $this->load($customerId);
            $customerEmail = isset($paymentMethod["email"]) ? $paymentMethod["email"] : "";
            $methodName = isset($paymentMethod["method_type"]) ? $paymentMethod["method_type"] : "";
            $ccNumber = isset($paymentMethod['cc_number']) ? $paymentMethod['cc_number'] : "";

            $methodNameStr = $methodName . "-" . substr((string)$ccNumber, -4);
            if (!empty($ccHolder)) {
                $methodNameStr .= "-" . $ccHolder;
            }
        }
        return $methodNameStr;
    }

    /**
     * Prepare Profile Method Name
     *
     * @param mixed $paymentMethodName
     * @return mixed|string
     */
    public function prepareProfileMethodName($paymentMethodName = "")
    {
        $paymentMethodNameStr = $paymentMethodName;
        $paymentMethod = json_decode($paymentMethodName);
        if (is_object($paymentMethod)) {
            $paymentMethodNameStr = $paymentMethod->a ?? "";
            $paymentMethodNameStr .= "-" . $paymentMethod->b ?? "";
        }
        return $paymentMethodNameStr;
    }

    /**
     * Set Default Payment Method
     *
     * @param null|mixed $customerToken
     * @param mixed $paymentMethodId
     * @return bool
     */
    public function setDefaultPaymentMethod($customerToken = null, $paymentMethodId = null)
    {
        try {
            $storeId = $this->_ebizConfigFactory->create()->getStoreId() ?? "0";
            $setDefaultMethod = $this->tranApiFactory->create()->getClient($storeId)
                ->SetDefaultCustomerPaymentMethodProfile(
                [
                    'securityToken' => $this->tranApiFactory->create()->getUeSecurityToken($storeId),
                    'customerToken' => $customerToken,
                    'paymentMethodId' => $paymentMethodId
                ]
            );

            if (isset($setDefaultMethod->SetDefaultCustomerPaymentMethodProfileResult)) {
                $this->_ebizchargeLogger->addInfo(__('Success the payment method has been set as default'));
                return true;
            }
            $this->_ebizchargeLogger->addError(__('Error occured during setting it as a default payment method'));
            return false;
        } catch (SoapFault $soapFault) {
            $this->_ebizchargeLogger->addCritical(
                'Exception occured during Setting default Payment Method Exception: ' . $soapFault->getMessage()
            );
            return false;
        }
    }

    /**
     * Is AVS full payment enabled
     *
     * @param int $storeId
     * @return bool
     */
    public function isAvsFullAmountEnabled(int $storeId = 0): bool
    {
        $storeId = $storeId ?: $this->getStoreId();
        $merchantTransactionData = $this->getMerchantTransactionData($storeId);
        $avsFullAmount = $merchantTransactionData['UseFullAmountForAVS'] ?? false;

        return (bool)$avsFullAmount;
    }

    /**
     * Process Validation Of Avs Cvv Response
     *
     * @param null|array $transactionResponseParams
     * @return array
     */
    public function processValidationOfAvsCvvResponse($transactionResponseParams = null)
    {
        /** @var  $transactionValidationResp */
        $transactionValidationResp = [
            'error' => true,
            'status' => false,
            'message' => "",
            'valid' => false,
            'response' => [
                'avs' => $this->_paymentModel->cvv2PaymentMessages[PaymentInterface::CVV2_CARD_CODE_RESULT_BLANK],
                'cvv' => $this->_paymentModel->avsPaymentMessages[PaymentInterface::AVS_CARD_CODE_RESULT_BLANK],
                'results' => $transactionResponseParams["response"] ?? []
            ]
        ];

        try {
            /** @var  $transactionResponse */
            $transactionResponse = $transactionResponseParams["response"];

            /**
             * Transaction Response
             */
            if ($transactionResponse && $transactionResponse['ResultCode'] === 'A') {

                /** @var  $resultStatus */
                $resultStatus = $transactionResponse['Status'];
                $resultStatusCode = $transactionResponse['StatusCode'];

                /** @var  $cardLevelResultCode */
                $cardLevelResultCode = $transactionResponse['CardLevelResultCode'];
                $cardLevelResult = $transactionResponse['CardLevelResult'];

                /** @var  $cardCodeResultCode */
                $cardCodeResultCode = $transactionResponse['CardCodeResultCode'];
                $cardCodeResult = $transactionResponse['CardCodeResult'];

                /** @var  $avsResultCode */
                $avsResultCode = $transactionResponse['AvsResultCode'];
                $avsResult = $transactionResponse['AvsResult'];

                $cardCodeResultResponse = [];
                $avsResultResponse = [];

                /**
                 * if Card Code Result code is Blank
                 */
                if ($cardCodeResultCode === "") {
                    $cardCodeResultResponse =
                        $this->_paymentModel->cvv2PaymentMessages[PaymentInterface::CVV2_CARD_CODE_RESULT_BLANK];
                } else {
                    /**
                     * if CVV in Array of Payment Response with messages
                     */
                    if (array_key_exists($cardCodeResultCode, $this->_paymentModel->cvv2PaymentMessages)) {
                        $cardCodeResultResponse = $this->_paymentModel->cvv2PaymentMessages[$cardCodeResultCode];
                    }
                }

                /**
                 * When AVS Result Code is Blank
                 */
                if ($avsResultCode === "") {
                    $avsResultResponse = $this->_paymentModel->avsPaymentMessages[PaymentInterface::AVS_CARD_CODE_RESULT_BLANK];

                } else {
                    /**
                     * if CVV in Array of Payment Response with messages
                     */
                    if (array_key_exists($avsResultCode, $this->_paymentModel->avsPaymentMessages)) {
                        $avsResultResponse = $this->_paymentModel->avsPaymentMessages[$avsResultCode];
                    }
                }

                /**
                 * Validation of AVS and CVV Codes
                 */
                if (isset($cardCodeResultResponse['code']) && $cardCodeResultResponse['code'] ===
                    PaymentInterface::CVV2_CARD_CODE_RESULT_M &&
                    isset($avsResultResponse['code']) && $avsResultResponse['code'] ===
                    PaymentInterface::AVS_CARD_CODE_RESULT_YYY
                ) {
                    /** @var  $transactionValidationResp */
                    $transactionValidationResp = [
                        'error' => false,
                        'status' => true,
                        'valid' => true,
                        'message' => "Success, the AVS | Zip approved && CVV Matched",
                        'response' => [
                            'avs' => $avsResultResponse,
                            'cvv' => $cardCodeResultResponse,
                            'results' => $transactionResponse
                        ]
                    ];

                } else {

                    /** @var  $transactionValidationResp */
                    $transactionValidationResp = [
                        'error' => true,
                        'status' => false,
                        'valid' => true,
                        'message' => "Error: could not match CVV or AVS",
                        'response' => [
                            'avs' => $avsResultResponse,
                            'cvv' => $cardCodeResultResponse,
                            'results' => $transactionResponse
                        ]
                    ];
                }
            }

        } catch (Exception $exception) {

            $this->_ebizchargeLogger->addCritical(__(
                "Exception occured during validating the transaction. Erro:" . $exception->getMessage()
            ));

            $transactionValidationResp['message'] =
                "Exception occurred during validating transaction response Exception: " . $exception->getMessage();
        }

        /** @var  $avsResultResponse */
        $avsResultResponse = explode("{br}", $transactionValidationResp['response']['avs']['msg']);
        /**
         * converting back to array response
         */
        $transactionValidationResp['response']['avs'] = $avsResultResponse;

        return $transactionValidationResp;
    }

    /**
     * Update Customer Payment Method
     *
     * @param string $customerId
     * @param array $methodParams
     * @return array
     */
    public function updateCustomerPaymentMethod(string $customerId, array $methodParams = []): array
    {
        $error = true;
        $paymentMethodParams = isset($methodParams['payment']) ? $methodParams['payment'] : $methodParams;
        $methodParams['payment'] = $paymentMethodParams;
        $paymentMethodName = isset($paymentMethodParams['method_name']) ? $paymentMethodParams['method_name'] : '';
        $storeId = $this->_ebizConfigFactory->create()->getStoreId() ?? "0";

        /** @var  $responseMessage */
        $responseMessage = __("Un-known error occurred during update of your payment method: \"" .
            $paymentMethodName . "\", please try again.");

        /**
         * Payment Method Response
         */
        $paymentMethodResponse = [
            'error' => $error,
            'payment_method_id' => 0,
            'method_name' => '',
            'message' => $responseMessage,
            'response' => [
                'is_default' => false,
                'payment_method_id' => 0,
                'method_name' => '',
                'error_response' => ''
            ]
        ];

        try {
            /** @var  $customerToken */
            $customerToken = isset($methodParams['ebiz_customer_token']) ? $methodParams['ebiz_customer_token'] : '';

            if ($customerToken === '') {
                return $paymentMethodResponse;
            }

            $paymentMethodId = $methodParams['method_id'] ?? ($methodParams['payment_method_id'] ?? '');
            $savedPaymentMethod = $this->getSavedPaymentMethodById($paymentMethodId, $customerToken);
            $paymentMethodName = isset($savedPaymentMethod["MethodName"]) ? $savedPaymentMethod["MethodName"] : "";
            $paymentMethodName = isset($paymentMethodParams['method_name']) ? $paymentMethodParams['method_name'] : $paymentMethodName;

            if ($savedPaymentMethod) {
                $ebzcOption = $methodParams["ebzc_option"] ?? "";
                $ccOwner = $methodParams["cc_holder"] ?? $methodParams["cc_owner"] ?? "";
                $ebzcOption = $methodParams["ebzc_option"] ?? "";

                $methodParams["method_name"] = $paymentMethodName ?? isset($savedPaymentMethod["MethodName"]) ?
                    $savedPaymentMethod["MethodName"] : "";
                if ($ebzcOption === "update" && !$ccOwner) {
                    $methodParams["method_name"] = $savedPaymentMethod["MethodName"] ?? "";
                    $paymentMethodParams["cc_holder"] = $savedPaymentMethod["AccountHolderName"] ?? "";
                }
            }

            $isDefault = isset($methodParams['is_default']) ? $methodParams['is_default'] : 0;
            $isUpdate = isset($methodParams['is_update']) ? $methodParams['is_update'] : "";
            $expiryMonth = isset($paymentMethodParams['cc_exp_month']) ? $paymentMethodParams['cc_exp_month'] : '';
            $expiryYear = isset($paymentMethodParams['cc_exp_year']) ? $paymentMethodParams['cc_exp_year'] : '';
            $paymentMethodExpiry = $this->preparePaymentMethodExpiry($expiryYear, $expiryMonth);
            $ccOwner = $methodParams["cc_holder"] ?? $methodParams["cc_owner"] ?? "";
            $cardHolder = isset($paymentMethodParams['cc_holder']) ? $paymentMethodParams['cc_holder'] : $ccOwner;
            /** @var  $profilePaymentMethodName */
            $profilePaymentMethodName = $this->preparePaymentMethodNameParam($methodParams);
            $creditCardNumber = isset($paymentMethodParams['card_number']) ? $paymentMethodParams['card_number'] : '';

            /** @var  $paymentMethodName */
            $paymentMethodName = $this->prepareProfileMethodName($profilePaymentMethodName);

            if ($savedPaymentMethod) {
                $ebzcOption = $methodParams["ebzc_option"] ?? "";
                $ccOwner = $methodParams["cc_holder"] ?? $methodParams["cc_owner"] ?? "";
                $ebzcOption = $methodParams["ebzc_option"] ?? "";

                $methodParams["method_name"] = $paymentMethodName ?? isset($savedPaymentMethod["MethodName"]) ?
                    $savedPaymentMethod["MethodName"] : "";

                $profilePaymentMethodName = $profilePaymentMethodName ?? $savedPaymentMethod["MethodName"];

                if ($ebzcOption === "update" && !$ccOwner) {
                    $profilePaymentMethodName = $savedPaymentMethod["MethodName"] ?? "";
                }
                if ($isUpdate && $isUpdate === "update") {
                    $origPaymentMethod = explode("-", $profilePaymentMethodName) ?? [];
                    if (is_array($origPaymentMethod) && isset($origPaymentMethod[count($origPaymentMethod) - 1])) {
                        $origPaymentMethod[count($origPaymentMethod) - 1] = $cardHolder;
                        $profilePaymentMethodName = implode("-", $origPaymentMethod) ?? $profilePaymentMethodName;
                    }
                }
                $createdAt = isset($savedPaymentMethod["Created"]) ? $savedPaymentMethod["Created"] : "";
                $paymentMethodParams['modified_at'] = $savedPaymentMethod["Modified"] ?? "";
                $createdAt = $this->tranApiFactory->create()->formateDateTime($createdAt, 'Y-m-d H:i:s');
                $paymentMethodParams['created_at'] = $this->tranApiFactory->create()->formatTZDateTime($createdAt);
                $modifiedAt = $this->tranApiFactory->create()->formateDateTime('', 'Y-m-d H:i:s');
                $modifiedAt = $this->tranApiFactory->create()->formatTZDateTime($modifiedAt);
                $paymentMethodParams['modified_at'] = $modifiedAt;
                $cardHolder = $cardHolder ?? $savedPaymentMethod["AccountHolderName"];
                $creditCardNumber = $savedPaymentMethod["CardNumber"] ?? $creditCardNumber;
            }

            $paymentMethodProfileParams = [
                'MethodID' => $paymentMethodId,
                'Created' => isset($paymentMethodParams['created_at']) ? $paymentMethodParams['created_at'] : '',
                'Modified' => isset($paymentMethodParams['modified_at']) ? $paymentMethodParams['modified_at'] : '',
                'MethodName' => $profilePaymentMethodName,
                'AccountHolderName' => $cardHolder,
                'AvsStreet' => isset($paymentMethodParams['avs_street']) ? $paymentMethodParams['avs_street'] : '',
                'AvsZip' => isset($paymentMethodParams['avs_zip']) ? $paymentMethodParams['avs_zip'] : "",
                'ReloadSchedule' => json_encode(['cardholder' => $cardHolder]),
                'CardExpiration' => $paymentMethodExpiry,
                'CardNumber' => $creditCardNumber
            ];
            $updateCustomerPaymentMethodParams = [
                'securityToken' => $this->tranApiFactory->create()->getUeSecurityToken($storeId),
                'customerToken' => $customerToken,
                'paymentMethodProfile' => $paymentMethodProfileParams
            ];

            /** @var $updatedMethodProfile */
            $updatedMethodProfile = $this->tranApiFactory->create()->getClient($storeId)
                ->updateCustomerPaymentMethodProfile($updateCustomerPaymentMethodParams);
            /** sending request to Gateway */
            if (isset($updatedMethodProfile->UpdateCustomerPaymentMethodProfileResult)) {
                if ($updatedMethodProfile->UpdateCustomerPaymentMethodProfileResult) {

                    $paymentMethodResponse = [
                        'error' => false,
                        'payment_method_id' => $paymentMethodId,
                        'message' => __("Success, you have successfully updated your Payment Method \"" .
                            $paymentMethodName . "\". "),
                        'response' => [
                            'is_default' => false,
                            'payment_method_id' => $paymentMethodId,
                            'method_name' => $paymentMethodName,
                            'error_response' => ''
                        ]
                    ];

                    $this->_ebizchargeLogger->addInfo(__(
                        "Success, the payment method has been updated successfully. " . $paymentMethodName
                    ));
                }
                if (!empty($isDefault)) {
                    $isDefaultResponse = $this->setDefaultPaymentMethod($customerToken, $paymentMethodId);

                    if ($isDefaultResponse) {
                        $response['is_default'] = true;
                        $response['message'] = __("Success: Your method \"" . $paymentMethodName .
                            "\" has been set as a default Payment Method.");
                    }
                }
            }

        } catch (SoapFault $soapFault) {
            $this->_ebizchargeLogger->addCritical(__(
                'Exception occurred during updating payment method Error:' . $soapFault->getMessage()
            ));
            $paymentMethodResponse['message'] = __("Error occurred during update of  \"" . $paymentMethodName .
                "\" Error:" . $soapFault->getMessage() . ".");
            $paymentMethodResponse['response'] = [
                'is_default' => false,
                'payment_method_id' => 0,
                'method_name' => $paymentMethodName,
                'error_response' => $soapFault->getMessage()
            ];

        }
        return $paymentMethodResponse;
    }

    /**
     * Get Saved Payment Method By Id
     *
     * @param int $paymentMethodId
     * @param string $customerToken
     * @return array
     */
    public function getSavedPaymentMethodById($paymentMethodId = 0, $customerToken = '')
    {
        $paymentMethod = [
            "MethodName" => "",
            "MethodID" => $paymentMethodId
        ];

        try {
            $storeId = $this->_ebizConfigFactory->create()->getStoreId() ?? "0";
            $securityToken = $this->tranApiFactory->create()->getUeSecurityToken($storeId);
            $paymentMethodParams = [
                "securityToken" => $securityToken,
                "customerToken" => $customerToken,
                "paymentMethodId" => $paymentMethodId
            ];
            /** @var  $paymentMethodObj */
            $paymentMethodObj = $this->tranApiFactory->create()->getClient($storeId)
                ->GetCustomerPaymentMethodProfile($paymentMethodParams);

            if (is_object($paymentMethodObj->GetCustomerPaymentMethodProfileResult)) {
                $paymentMethod = (array)$paymentMethodObj->GetCustomerPaymentMethodProfileResult;
            }
        } catch (Exception $exception) {
            $this->_ebizchargeLogger->addCritical(__("Error occurred during fetching Payment Method Error:" .
                $exception->getMessage()));

        }
        return $paymentMethod;
    }

    /**
     * Payment Month Expiry
     *
     * @param mixed $expiryYear
     * @param mixed $expiryMonth
     * @return string
     */
    public function preparePaymentMethodExpiry($expiryYear, $expiryMonth): string
    {
        if ((int)$expiryMonth < 10 && strlen($expiryMonth) < 2) {
            $expiryMonth = '0' . $expiryMonth;
        }
        $expiryYear = trim($expiryYear);
        $paymentMethodExpiry = $expiryYear . $expiryMonth;
        return $paymentMethodExpiry;
    }

    /**
     * Prepare Payment Method Name Param
     *
     * @param array $paymentMethod
     * @param bool $isAddNew
     * @return void
     */
    public function preparePaymentMethodNameParamJSON($paymentMethod = [], $isAddNew = false)
    {
        /** @var  $methodNameStr */
        $methodNameStr = "";

        if (!$isAddNew) {
            /** @var  $paymentMethodName */
            $paymentMethodName = isset($paymentMethod["method_name"]) ? $paymentMethod["method_name"] : "";

            $methodNameStr = $paymentMethodName;
            $paymentMethodNameJson = str_replace("'", "\"", $paymentMethodName ?? "");
            $paymentMethodNameObj = json_decode($paymentMethodNameJson);

            $ccHolder = isset($paymentMethod["cc_holder"]) ? $paymentMethod["cc_holder"] : "";
            $ccOwner = $paymentMethod["cc_owner"] ?? $ccHolder;

            if (is_object($paymentMethodNameObj)) {
                $paymentMethodNameObj->b = $ccOwner;
                $methodNameStr = json_encode($paymentMethodNameObj);
            }

            if ($paymentMethodName === "") {
                $customerId = isset($paymentMethod["customer_id"]) ? $paymentMethod["customer_id"] : "";
                $customer = $this->load($customerId);
                $customerEmail = $customer->getEmail();
                $methodName = isset($paymentMethod["method_type"]) ? $paymentMethod["method_type"] : "";
                $ccNumber = $paymentMethod['cc_number'] ?? $paymentMethod['card_number'];
                $paymentMethodNameJson = [
                    "a" => $methodName . " " . substr((string)$ccNumber, -4),
                    "b" => $ccOwner,
                    "e" => $customerEmail,
                    "p" => 0
                ];
                $methodNameStr = json_encode($paymentMethodNameJson);
            }

        } else {

            $ccHolder = isset($paymentMethod["cc_holder"]) ? $paymentMethod["cc_holder"] : "";
            $ccHolder = isset($paymentMethod["cc_owner"]) ? $paymentMethod["cc_owner"] : $ccHolder;
            $customerId = isset($paymentMethod["customer_id"]) ? $paymentMethod["customer_id"] : "";

            $customer = $this->load($customerId);
            $customerEmail = isset($paymentMethod["email"]) ? $paymentMethod["email"] : "";
            $methodName = isset($paymentMethod["method_type"]) ? $paymentMethod["method_type"] : "";
            $ccNumber = isset($paymentMethod['cc_number']) ? $paymentMethod['cc_number'] : "";

            $paymentMethodNameJson = [
                "a" => $methodName . " " . substr((string)$ccNumber, -4),
                "b" => $ccHolder,
                "e" => $customerEmail,
                "p" => 0
            ];
            $methodNameStr = json_encode($paymentMethodNameJson);
        }
        return $methodNameStr;
    }

    /**
     * Prepare Params for Payment Method
     *
     * @param mixed $customerId
     * @param array $customerParams
     * @return array
     */
    public function prepareParamsForBankAccount($customerId, $customerParams = [])
    {
        if (!$customerId) {
            return [];
        }

        $customer = $this->load($customerId);
        $paymentMethodParams = $customerParams;
        $paymentMethodParams['street'] = isset($paymentMethodParams['avs_street']) ?
            [$paymentMethodParams['avs_street']] : [''];
        $paymentMethodParams['ebiz_customer_internal_id'] = $customer->getEcCustInternalId();
        $paymentMethodParams['ebiz_customer_token'] = $customer->getEcCustToken();

        /** Payment Method Params */
        $paymentMethodParams = [
            'ach_type' => isset($paymentMethodParams['cc_type_ach']) ? $paymentMethodParams['cc_type_ach'] : '',
            'ach_number' => isset($paymentMethodParams['cc_number_ach']) ? $paymentMethodParams['cc_number_ach'] : '',
            'ach_holder' => isset($customerParams['cc_owner_ach']) ? $customerParams['cc_owner_ach'] : '',
            'ach_route' => isset($customerParams['cc_routing_ach']) ? $customerParams['cc_routing_ach'] : '',
            'is_default' => isset($customerParams['is_default']) ? $customerParams['is_default'] : 0
        ];

        return $paymentMethodParams;
    }

    /**
     * Add Customer Bank Account
     *
     * @param null|mixed $customer
     * @param array $bankAccountRequestParams
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function addCustomerBankAccount($customer = null, $bankAccountRequestParams = [])
    {
        /**
         * Bank Method Response
         */
        $customerBankMethodResponse = [
            'error' => true,
            'message' => __('Un-Known error occurred during adding payment method. Please try again.'),
            'response' => [
                'is_default' => 0,
                'bank_method_id' => 0,
                'bank_method_name' => ''
            ]
        ];

        /**
         * Reset customer Request Params
         */
        $this->unsetCustomerRequestParams();
        /**
         * Add Customer params
         */
        $this->setCustomerRequestParams($bankAccountRequestParams);

        /**
         * is Customer Bank Account not allowed
         */
        if (!$this->isAddNewBankAccountsAllowed()) {
            return $customerBankMethodResponse;
        }

        try {

            /** @var  $customer */
            $customer = $this->loadByEmail($customer->getEmail());

            $customerId = $customer->getId();
            $customerEbizInternalId = $customer->getEcCustInternalId();
            $customerEbizToken = $customer->getEcCustToken();
            $customerEbizId = $customer->getEcCustId();

            if (!$customerEbizToken && !$customerEbizId || !$customerEbizInternalId) {
                $ebizCustomerSID = $customer->getEcCustId();
                $addCustomer = $this->addCustomerToEbizcharge($customer);

                if ($addCustomer['status'] === "success") {
                    /** @var $customerInternalId */
                    $bankAccountRequestParams['ebiz_customer_internal_id'] = $addCustomer["ebiz_internal_id"] ?? "";
                    /** @var $customerToken */
                    $bankAccountRequestParams['ebiz_customer_token'] = $addCustomer["ebiz_customer_token"] ?? "";
                }
            }

            $bankAccountRequestParams['ebiz_customer_internal_id'] = $customerEbizInternalId;
            /** @var $customerToken */
            $bankAccountRequestParams['ebiz_customer_token'] = $customerEbizToken;

            $achType = isset($bankAccountRequestParams['ach_type']) ? $bankAccountRequestParams['ach_type'] : '';
            $achNumber = isset($bankAccountRequestParams['ach_number']) ? $bankAccountRequestParams['ach_number'] : '';
            $achHolder = isset($bankAccountRequestParams['ach_holder']) ? $bankAccountRequestParams['ach_holder'] : '';
            $achRoute = isset($bankAccountRequestParams['ach_route']) ? $bankAccountRequestParams['ach_route'] : '';
            $isDefault = isset($bankAccountRequestParams['is_default']) ? $bankAccountRequestParams['is_default'] : 0;

            $createdAt = $this->tranApiFactory->create()->formateDateTime('', 'Y-m-d H:i:s');
            $createdAt = $this->tranApiFactory->create()->formatTZDateTime($createdAt);
            $modifiedAt = $this->tranApiFactory->create()->formateDateTime('', 'Y-m-d H:i:s');
            $modifiedAt = $this->tranApiFactory->create()->formatTZDateTime($modifiedAt);

            /** @var $bankAccountParams */
            $bankAccountParams = [
                'MethodName' => $achType . ' ' . substr((string)$achNumber, -4) . ' - ' . $achHolder,
                'Created' => $createdAt,
                'Modified' => $modifiedAt,
                'Account' => $achNumber,
                'AccountType' => $achType,
                'AccountHolderName' => $achHolder ?? '',
                'Routing' => $achRoute,
                'MethodType' => PaymentInterface::ACH
            ];

            /** if we have customer at Ebizcharge  */
            if ($customerEbizInternalId && $customerEbizToken) {
                $paymentMethodResposne = $this->tranApiFactory->create()->addCustomerPaymentMethod(
                    $customerEbizInternalId,
                    $bankAccountParams
                );

                if ($paymentMethodResposne['error'] === false) {
                    $bankMethodId = $paymentMethodResposne['method_id'];
                    $bankAccountPaymentMethodName = $bankAccountParams['MethodName'];

                    /** saving new back account */
                    $customerBankMethodResponse['error'] = false;
                    $customerBankMethodResponse['message'] = __(
                        'Success, the bank account has been added with EBizCharge Hub.'
                    );

                    $customerBankMethodResponse['response'] = [
                        'is_default' => 0,
                        'bank_method_id' => $bankMethodId,
                        'bank_method_name' => $bankAccountPaymentMethodName
                    ];

                    $this->_ebizchargeLogger->addInfo(__(
                        'Success, the bank account has been added with EBizCharge Hub.'
                    ));

                    if ($isDefault) {
                        /** setting as a default bank account */
                        $defaultBankAccount = $this->tranApiFactory->create()->setDefaultPaymentMethod(
                            $customerEbizToken,
                            $bankMethodId
                        );
                        if ($defaultBankAccount) {
                            $customerBankMethodResponse['error'] = false;
                            $customerBankMethodResponse['message'] = __(
                                'Success, the bank account has been added with EBizCharge Hub.'
                            );
                            $customerBankMethodResponse['response']['is_default'] = 1;
                            $this->_ebizchargeLogger->addInfo(__(
                                'Bank Account has been pushed as your default payment method at EBizCharge Gateway.'
                            ));
                        }
                    }
                    $this->unsetCustomerRequestParams();
                }

            } else {
                $customerBankMethodResponse['message'] = __(
                    'Error this customer has not found at EBizCharge Hub.'
                );
                $customerBankMethodResponse['response'] = [
                    'error' => true,
                    'is_default' => 0,
                    'bank_method_id' => 0,
                    'bank_method_name' => ''
                ];
                $this->_ebizchargeLogger->addCritical(__($customerBankMethodResponse['message']));
            }
            return $customerBankMethodResponse;

        } catch (SoapFault $soapFault) {
            $this->_ebizchargeLogger->addCritical(__(
                'Exception occurred during adding bank account with EBizCharge Gateway. Error:' .
                $soapFault->getMessage()
            ));
            $customerBankMethodResponse = [
                'error' => true,
                'message' => __('Exception occurred during adding bank account. Error:' .
                    $soapFault->getMessage()),
                'response' => [
                    'is_default' => 0,
                    'bank_method_id' => 0,
                    'bank_method_name' => ''
                ]
            ];
        }
        return $customerBankMethodResponse;
    }

    /**
     * Is Add New Bank Accounts Allowed
     *
     * @return false
     * @throws NoSuchEntityException
     */
    public function isAddNewBankAccountsAllowed()
    {
        $isAddNewPaymentMethodAllowed = false;
        $storeId = $this->_ebizConfigFactory->create()->getStoreId();
        $isModuleActive = $this->_ebizConfigFactory->create()->isActive($storeId);
        $isAchSaveCardsAllowed = $this->_ebizConfigFactory->create()->isAchActive($storeId);
        $isAddNewPaymentMethod = $this->_ebizConfigFactory->create()->getIsSaveBankAccounts($storeId);

        if ($isModuleActive && $isAddNewPaymentMethod && $isAchSaveCardsAllowed) {
            $isAddNewPaymentMethodAllowed = true;
        } else {
            // phpcs:ignore
            $this->_ebizchargeLogger->addError(__("Add new payment method bank accounts are not allowed, please enable save cards from admin configuration. "));
        }
        return $isAddNewPaymentMethodAllowed;
    }

    /**
     * Delete Bank Account
     *
     * @param null|mixed $customer
     * @param null|mixed $paymentMethodId
     * @return array
     */
    public function deleteCustomerBankAccount($customer = null, $paymentMethodId = null): array
    {
        $response = [
            'status' => 'failed',
            'error' => true,
            'message' => ''
        ];
        try {
            $customerToken = $customer->getEcCustToken();
            /** @var $isPaymentMethodDeletedResp */
            $paymentMethodDeletedResp = $this->tranApiFactory->create()->deleteCustomerPaymentMethod(
                $customerToken,
                $paymentMethodId
            );

            if ($paymentMethodDeletedResp == true) {
                $response = [
                    'status' => 'success',
                    'error' => false,
                    'message' => __('Success, this Bank account has been deleted successfully.')
                ];
            } else {
                $response['message'] = __('Error occurred during deleting BankAccount');
            }
            return $response;
        } catch (SoapFault $soapFault) {
            $this->_ebizchargeLogger->addCritical(__('Exception occurred during Deleting Bank Account Error:' .
                $soapFault->getMessage()));
            $response = [
                'status' => 'failed',
                'error' => true,
                'message' => __('Exception occurred during Deleting Bank Account : ' . $soapFault->getMessage())
            ];
            return $response;
        }
    }

    /**
     * Delete Customer Payment Method
     *
     * @param null|mixed $customerId
     * @param null|mixed $paymentMethodId
     * @return array
     */
    public function deleteCustomerPaymentMethod($customerId = null, $paymentMethodId = null): array
    {
        $response = [
            'status' => 'failed',
            'error' => true,
            'error_code' => 503,
            'message' => __('Error occurred during deleting Payment Method:' . $paymentMethodId)
        ];

        try {
            /** @var  $customer */
            $customer = $this->load($customerId);

            /**
             * if found Customer
             */
            if ($customer) {
                /** @var  $customerToken */
                $customerToken = $customer->getEcCustToken();

                /** @var  $recurringListings */
                $recurringListings = $this->getCustomerRecurringsByMethodId(
                    $customerId,
                    $paymentMethodId,
                    SoapApiModelInterface::RECURRING_PAYMENT_STATUS_UNSUSPENDED
                );

                if (count($recurringListings) > 0) {
                    $response = [
                        'status' => 'failed',
                        'error' => true,
                        'error_code' => 502,
                        // phpcs:ignore
                        'message' => __("Oops! error occurred, you cannot delete this payment method, Please first delete subscription against Payment Method Id: " . $paymentMethodId)
                    ];
                    return $response;
                }

                /** @var $isPaymentMethodDeletedResp */
                $paymentMethodDeletedResp = $this->tranApiFactory->create()->deleteCustomerPaymentMethod(
                    $customerToken,
                    $paymentMethodId
                );

                if ($paymentMethodDeletedResp["error"] === false) {
                    $response = [
                        'status' => 'success',
                        'error' => false,
                        'error_code' => 200,
                        'message' => $paymentMethodDeletedResp["message"]
                    ];
                } else {
                    $response['message'] = $paymentMethodDeletedResp["message"];
                }
            }

        } catch (SoapFault $soapFault) {
            $this->_ebizchargeLogger->addCritical(__('Exception occurred during Deleting Payment Method Error:' .
                $soapFault->getMessage()));
            $response['message'] = __('Exception occurred during Deleting Payment Method' .
                $soapFault->getMessage());
        }

        return $response;
    }

    /**
     * Get Recurrings By Payment Method Id
     *
     * @param mixed $customerId
     * @param mixed $paymentMethodId
     * @param mixed $status
     * @return array
     */
    public function getCustomerRecurringsByMethodId(
        $customerId = 0,
        $paymentMethodId = 0,
        $status = 0
    )
    {
        $recurringsList = [];

        try {
            $searchCriteria = $this->_searchCriteriaBuilder
                ->addFilter(
                    RecurringInterface::MAGE_CUST_ID,
                    $customerId
                )
                ->addFilter(
                    RecurringInterface::EB_REC_METHOD_ID,
                    $paymentMethodId
                )
                ->addFilter(
                    RecurringInterface::REC_STATUS,
                    $status
                );

            /** @var $resultRecurrings */
            $resultRecurrings = $this->_recurringRepositoryInterface->getList($searchCriteria->create());

            $recurringRows = $resultRecurrings->getItems();
            $recurringsList = $recurringRows;

            $this->_ebizchargeLogger->addInfo(__("Recurring Found against this Payment Method "));

            return $recurringsList;
        } catch (Exception $exception) {
            $this->_ebizchargeLogger->addCritical(__("Exception occurred during getting results Error:" .
                $exception->getMessage()));
            $recurringsList = [];
        }

        return $recurringsList;
    }

    /**
     * Is Cvv Required For Admin Side Payment Methods
     *
     * @return bool[]
     */
    public function isCvvRequiredForAdminSidePaymentMethod()
    {
        /** @var  $storeId */
        $storeId = $this->getStoreId();

        /** @var  $cardCodeRequired */
        $cardCodeRequiredResponse = [
            'saved_card' => false,
            'new_card' => false
        ];

        /** @var  $cardCodeRequiredLocal */
        $isCvvRequiredAtLocal = $this->_ebizConfigFactory->create()->getRequestCardCodeAdmin($storeId);
        $merchantTransactionData = $this->getMerchantTransactionData();

        /** @var  $isCvvRequiredAtEbiz */
        $isCvvRequiredAtEbizCharge = $merchantTransactionData['EnableCVVWarnings'] ?? '';
        $verifyCardBeforeSaveAtEbizCharge = $merchantTransactionData['VerifyCreditCardBeforeSaving'] ?? '';

        /** @var  $cvvRequiredAtEbizCharge */
        $cvvRequiredAtEbizCharge = false;

        // phpcs:ignore
        //if ((boolean)$isCvvRequiredAtEbizCharge === true && (boolean)$verifyCardBeforeSaveAtEbizCharge === true) {
        if ((boolean)$isCvvRequiredAtEbizCharge === true) {
            $cvvRequiredAtEbizCharge = true;
        }

        /** Case # 1:  if Card code required is ON at Ebiz Server and Local Server  */
        if ((boolean)$cvvRequiredAtEbizCharge === true &&
            (boolean)$isCvvRequiredAtLocal === true
        ) {
            $cardCodeRequiredResponse = [
                'saved_card' => true,
                'new_card' => true
            ];
        }

        /** Case # 2:  if Card code required is ON at Ebiz and off at Local server  */
        if ((boolean)$cvvRequiredAtEbizCharge === true &&
            (boolean)$isCvvRequiredAtLocal === false
        ) {
            $cardCodeRequiredResponse = [
                'saved_card' => false,
                'new_card' => true
            ];
        }

        /** Case # 3:  if Card code is Off at Ebiz Server and On at Local Server  */
        if ((boolean)$cvvRequiredAtEbizCharge === false &&
            (boolean)$isCvvRequiredAtLocal === true
        ) {
            $cardCodeRequiredResponse = [
                'saved_card' => true,
                'new_card' => true
            ];
        }

        /** Case # 4:  if Card code is ON at Magento and OFF at Ebizcharge Side   */
        if ((boolean)$cvvRequiredAtEbizCharge === false &&
            (boolean)$isCvvRequiredAtLocal === false
        ) {
            $cardCodeRequiredResponse = [
                'saved_card' => false,
                'new_card' => false
            ];
        }

        return $cardCodeRequiredResponse;
    }

    /**
     * Is Avs Warnings Enabled
     *
     * @return bool|mixed
     */
    public function isAvsWarningsEnabled()
    {
        /** @var  $merchantTransanctionData */
        $merchantTransanctionData = $this->getMerchantTransactionData();

        /** @var  $avsWarnings */
        $avsWarnings = false;

        if (isset($merchantTransanctionData['EnableAVSWarnings'])) {
            $avsWarnings = $merchantTransanctionData['EnableAVSWarnings'];
        }
        return $avsWarnings;
    }

    /**
     * Is Cvv Warning Enabled
     *
     * @return bool|mixed
     */
    public function isCvvWarningsEnabled()
    {
        /** @var  $merchantTransanctionData */
        $merchantTransanctionData = $this->getMerchantTransactionData();

        /** @var  $cvvWarnings */
        $cvvWarnings = false;

        if (isset($merchantTransanctionData['EnableAVSWarnings'])) {
            $cvvWarnings = $merchantTransanctionData['EnableAVSWarnings'];
        }
        return $cvvWarnings;
    }

    /**
     * Is EMV Enabled
     *
     * @return bool|mixed
     */
    public function isEmvEnabled()
    {
        /** @var  $merchantTransanctionData */
        $merchantTransanctionData = $this->getMerchantTransactionData();

        /** @var  $emvEnabled */
        $emvEnabled = false;

        if (isset($merchantTransanctionData['IsEMVEnabled'])) {
            $emvEnabled = $merchantTransanctionData['IsEMVEnabled'];
        }
        return $emvEnabled;
    }

    /**
     * Update Customer Bank Account
     *
     * @param null|mixed $customer
     * @param array $customerBankAccountParams
     * @return array
     * @throws LocalizedException
     */
    public function updateCustomerBankAccount($customer = null, $customerBankAccountParams = [])
    {

        /** @var  $updatedPaymentResponse */
        $updatedPaymentResponse = [
            'error' => true,
            'message' => '',
            'response' => []
        ];

        if (!$customer || !is_array($customerBankAccountParams)) {
            return $updatedPaymentResponse;
        }

        try {
            /** @var $customer */
            $customer = $this->loadByEmail($customer->getEmail());
            $customerToken = $customer->getEcCustToken();
            $ebizConfigFactory = $this->_ebizConfigFactory->create();
            $storeId = $customer->getStoreId() ?? $ebizConfigFactory->getStoreId();

            $achType = $customerBankAccountParams['ach_type'];
            $achNumber = $customerBankAccountParams['ach_number'];
            $achHolder = $customerBankAccountParams['ach_holder'];
            $achRoute = $customerBankAccountParams['ach_route'];
            $isDefault = $customerBankAccountParams['is_default'] ?? 0;
            $bankAccountMethodId = $customerBankAccountParams['bank_account_method_id'];

            $createdAt = $this->tranApiFactory->create()->formateDateTime('', 'Y-m-d H:i:s');
            $createdAt = $this->tranApiFactory->create()->formatTZDateTime($createdAt);
            $modifiedAt = $this->tranApiFactory->create()->formateDateTime('', 'Y-m-d H:i:s');
            $modifiedAt = $this->tranApiFactory->create()->formatTZDateTime($modifiedAt);

            /** @var $bankAccountParams */
            $bankAccountParams = [
                'MethodID' => $bankAccountMethodId,
                'MethodName' => $achType . ' ' . substr((string)$achNumber, -4) . ' - ' . $achHolder,
                'Created' => $createdAt,
                'Modified' => $modifiedAt,
                'Account' => $achNumber,
                'AccountType' => $achType,
                'AccountHolderName' => $achHolder ?? '',
                'Routing' => $achRoute,
                'MethodType' => 'ACH'
            ];

            /** @var  $client */
            $client = $this->tranApiFactory->create()->getClient($storeId);

            $updatedProfileResponse = $client->updateCustomerPaymentMethodProfile(
                [
                    'securityToken' => $this->tranApiFactory->create()->getUeSecurityToken($storeId),
                    'customerToken' => $customerToken,
                    'paymentMethodProfile' => $bankAccountParams
                ]
            );

            /** updated response then send back response */
            if ($updatedProfileResponse && $updatedProfileResponse->UpdateCustomerPaymentMethodProfileResult) {
                $updatedPaymentResponse['error'] = false;
                $updatedPaymentResponse['message'] = __('Success, Bank Account has been updated successfully');
                $updatedPaymentResponse['response'] = [
                    'bank_account_updated' => $updatedProfileResponse->UpdateCustomerPaymentMethodProfileResult
                ];

                if (isset($isDefault) && $isDefault !== 0) {
                    /** @var  $isDefaultResponse */
                    $isDefaultResponse = $this->setDefaultPaymentMethod($customerToken, $bankAccountMethodId);
                    if ($isDefaultResponse) {
                        $updatedPaymentResponse['message'] = __(
                            'Success, Bank Account has been updated successfully and has set it to default'
                        );
                    }
                }
            } else {
                $updatedPaymentResponse['message'] = __(
                    'Error occurred during updating the Bank Account, please try again'
                );
            }
            return $updatedPaymentResponse;
        } catch (SoapFault $soapFault) {
            $updatedPaymentResponse['message'] = __(
                'Error occurred during updating the Bank Account, Error:' . $soapFault->getMessage()
            );
            $this->_ebizchargeLogger->addCritical($updatedPaymentResponse['message']);
            return $updatedPaymentResponse;
        }
    }

    /**
     * Render Customer Search Listings
     *
     * @param array $searchParams
     * @return string
     * @throws LocalizedException
     */
    public function renderCustomerSearchListing($searchParams = [])
    {
        $customerListItems = $this->getCustomerListings($searchParams);
        $htmlOutput = '<ul id="suggestion-list-rows">';
        /** customer List Items */
        if (count($customerListItems) > 0) {
            foreach ($customerListItems as $customerItem) {
                $htmlOutput .= '<li data-key="' . $customerItem->getEmail() . '" data-value="' .
                    $customerItem->getId() . '" id="' . $customerItem->getId() . '">' .
                    $customerItem->getEmail() . '</li>';
            }
        } else {
            $htmlOutput .= '<li>Not Found</li>';
        }
        $htmlOutput .= '</ul>';

        return $htmlOutput;
    }

    /**
     * Get Customer Listings
     *
     * @param array $searchParams
     * @return AbstractDb|Collection
     * @throws LocalizedException
     */
    public function getCustomerListings($searchParams = [])
    {
        $keywords = isset($searchParams['keywords']) ? $searchParams['keywords'] : '';
        $limit = isset($searchParams['limit']) ? $searchParams['limit'] : 0;

        $customerListItems = $this->_customerCollection
            ->addAttributeToSelect('*')
            ->addFieldToFilter(
                [
                    ['attribute' => 'email', 'like' => '%' . $keywords . '%'],
                    ['attribute' => 'firstname', 'like' => '%' . $keywords . '%'],
                    ['attribute' => 'lastname', 'like' => '%' . $keywords . '%'],
                    ['attribute' => 'entity_id', 'like' => '%' . $keywords . '%']
                ]
            )
            ->setOrder('entity_id', "asc");

        /**
         * setting limits
         */
        if ($limit > 0) {
            $customerListItems->setPageSize($limit);
        }

        return $customerListItems;
    }

    /**
     * Print Email Template
     *
     * @param array $params
     * @return array
     */
    public function printEmailTemplate($params = [])
    {
        /** @var  $defaultEmailTemplate */
        $defaultEmailTemplates = [];
        $emailTemplateResponse = [
            'error' => true,
            'email_html' => '',
            'message' => 'Email template not found at EBizCharge Connect, please create and try again.'
        ];

        $transactionRefNum = isset($params['tid']) ? $params['tid'] : '';
        $receiptRefNum = isset($params['tid']) ? $params['rid'] : '';

        /** Transaction Ref Num */
        if ($transactionRefNum && $receiptRefNum) {
            try {
                /** @var $customerEmailReceiptTemplates */
                $customerEmailReceiptTemplates = $this->getEbizCustomerReceiptEmailTemplates();
                /** @var $defaultEmailTemplate */
                $defaultEmailTemplate = $this->_ebizConfigFactory->create()->getEmailCustomerReceiptTemplate();
                $defaultTemplate = $this->getDefaultCustomerPrintTransactionEmail();

                /** check if default email tempaltes found */
                if (count($customerEmailReceiptTemplates) > 0) {
                    foreach ($customerEmailReceiptTemplates as $customerEmailReceiptTemplate) {
                        $customerEmailReceiptTemplate = (array)$customerEmailReceiptTemplate;
                        if ($customerEmailReceiptTemplate['TemplateInternalId'] == $defaultEmailTemplate) {
                            $defaultTemplate = $customerEmailReceiptTemplate;
                        }
                    }
                }

                if ($defaultTemplate) {
                    $defaultTemplate = $defaultTemplate['TemplateHTML'];
                    $emailTemplateResponse['error'] = false;

                    /** @var  $htmlTemplate */
                    $htmlTemplate = $this->prepareEmailPrintTemplate(
                        $transactionRefNum,
                        // phpcs:ignore
                        base64_decode($defaultTemplate)
                    );

                    /** Email template response settings  */
                    $emailTemplateResponse['email_html'] = $htmlTemplate;
                    $emailTemplateResponse['message'] = __('Success, the Email template found');
                } else {
                    $emailTemplateResponse['email_html'] = '';
                    $emailTemplateResponse['message'] = __('Email template not found at EBizCharge Connect, please create and try again.');
                }
            } catch (SoapFault $soapFault) {
                $emailTemplateResponse['email_html'] = '';
                $emailTemplateResponse['message'] = __(
                    'Email template not found at EBizCharge Connect, please create and try again. Error:' . $soapFault->getMessage()
                );
            }
        }
        return $emailTemplateResponse;
    }

    /**
     * Get Ebizcharge Customer Receipt Email Templates
     *
     * @return array
     */
    public function getEbizCustomerReceiptEmailTemplates(): array
    {
        /** @var $defaultEmailTemplate */
        $defaultEmailTemplate = $this->getDefaultCustomerPrintTransactionEmail();
        $customerReceiptEmailTemplates = [];

        try {
            $storeId = $this->getStoreId();
            /** @var getting email templates $params */
            $emailTemplateParams = [
                'securityToken' => $this->tranApiFactory->create()->getUeSecurityToken($storeId),
                'templateInternalId' => '',
                'templateName' => '',

            ];
            $storeId = $this->_ebizConfigFactory->create()->getStoreId() ?? "0";

            if ($this->tranApiFactory->create()->getClient($storeId)) {
                /** @var $emailTemplatesResponse */
                $emailTemplatesResponse = $this->tranApiFactory->create()->getClient($storeId)
                    ->GetEmailTemplates($emailTemplateParams);

                /** @var  $emailTemplates */
                $emailTemplates = (array)$emailTemplatesResponse->GetEmailTemplatesResult;

                /** Get Email Templates */
                if ($emailTemplates && count($emailTemplates) > 0) {
                    $merchantEmailTemplates = $emailTemplatesResponse->GetEmailTemplatesResult->EmailTemplate;
                    if (!is_array($merchantEmailTemplates)) {
                        $merchantEmailTemplates = (array)$emailTemplates;
                    }
                    if (is_array($merchantEmailTemplates) && count($merchantEmailTemplates)) {
                        foreach ($merchantEmailTemplates as $emailTemplate) {
                            // if ($emailTemplate->TemplateTypeId == CustomerInterface::DEFAULT_EMAIL_TEMPLATE) {
                            $customerReceiptEmailTemplates[] = $emailTemplate;
                            //  }
                        }
                    }
                } else {
                    $customerReceiptEmailTemplates[] = $defaultEmailTemplate;
                }
            } else {
                $customerReceiptEmailTemplates[] = $defaultEmailTemplate;
            }

            return $customerReceiptEmailTemplates;
        } catch (SoapFault $soapFault) {
            $this->_ebizchargeLogger->addCritical(__(
                'Email template not found at EBizCharge Connect, please create and try again. Error:' . $soapFault->getMessage()
            ));
            $customerReceiptEmailTemplates[] = $defaultEmailTemplate;

            return $customerReceiptEmailTemplates;
        }
    }

    /**
     * Default Email Template TMP Based
     *
     * @return DataObject
     */
    public function getDefaultCustomerPrintTransactionEmail()
    {
        $defaultEmailTemplate = $this->_dataObjectFactory->create();

        $defaultEmailTemplate->TemplateName = "Transaction Receipt-Customer";
        $defaultEmailTemplate->TemplateInternalId = "d5c92dc4-74a9-4cc7-a359-b9f8d8d9fd25";
        $defaultEmailTemplate->TemplateSubject = "Receipt of payment";
        $defaultEmailTemplate->TemplateDescription = "Receipt of payment";
        // phpcs:ignore
        $defaultEmailTemplate->TemplateHTML = "PCFET0NUWVBFIGh0bWw+DQo8aHRtbCBsYW5nPSJlbiI+DQogICAgPGhlYWQ+DQogICAgICAgIDxtZXRhIGh0dHAtZXF1aXY9IkNvbnRlbnQtVHlwZSIgY29udGVudD0idGV4dC9odG1sOyBjaGFyc2V0PXV0Zi04IiAvPg0KICAgICAgICA8bWV0YSBuYW1lPSJ2aWV3cG9ydCIgY29udGVudD0id2lkdGg9ZGV2aWNlLXdpZHRoLCBpbml0aWFsLXNjYWxlPTEuMCIgLz4NCiAgICAgICAgPG1ldGEgaHR0cC1lcXVpdj0iWC1VQS1Db21wYXRpYmxlIiBjb250ZW50PSJJRT1lZGdlLGNocm9tZT0xIiAvPg0KICAgICAgICA8bWV0YSBuYW1lPSJmb3JtYXQtZGV0ZWN0aW9uIiBjb250ZW50PSJ0ZWxlcGhvbmU9bm8iIC8+IDwhLS0gZGlzYWJsZSBhdXRvIHRlbGVwaG9uZSBsaW5raW5nIGluIGlPUyAtLT4NCiAgICAgICAgPHRpdGxlPlBheW1lbnQgUmVjZWlwdDwvdGl0bGU+DQogICAgICAgIDxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQogICAgICAgICAgICAvKiA9PT09PT09PT09IEN1c3RvbSBGb250IEltcG9ydCA9PT09PT09PT09ICovDQogICAgICAgICAgICBAaW1wb3J0IHVybChodHRwOi8vZGFrczJrM2E0aWIyei5jbG91ZGZyb250Lm5ldC8wZ2xvYmFscy9hdmVuaXJuZXh0cHJvLXdlYmZvbnQuY3NzKTsNCiAgICAgICAgICAgIC8qIFJFU0VUIFNUWUxFUyAqLw0KICAgICAgICAgICAgYm9keSwgI2JvZHlUYWJsZSwgI2JvZHlDZWxsLCAjYm9keUNlbGx7aGVpZ2h0OjEwMCUgIWltcG9ydGFudDsgbWFyZ2luOjA7IHBhZGRpbmc6MDsgd2lkdGg6MTAwJSAhaW1wb3J0YW50O2ZvbnQtZmFtaWx5OidBdmVuaXJOZXh0TFRQcm8tUmVndWxhcicsJ0F2ZW5pciBOZXh0JywnSGVsdmV0aWNhTmV1ZScsJ0hlbHZldGljYSBOZXVlJywnQXZlbmlyJyxIZWx2ZXRpY2EsIEFyaWFsLCAiTHVjaWRhIEdyYW5kZSIsIHNhbnMtc2VyaWY7fQ0KICAgICAgICAgICAgdGFibGV7Lypib3JkZXItY29sbGFwc2U6Y29sbGFwc2U7Ki8gYm9yZGVyLXNwYWNpbmc6IDBweDsgfQ0KICAgICAgICAgICAgdGFibGVbaWQ9Ym9keVRhYmxlXSB7d2lkdGg6MTAwJSFpbXBvcnRhbnQ7bWFyZ2luOmF1dG87bWF4LXdpZHRoOjYwMHB4IWltcG9ydGFudDtjb2xvcjojN0E3QTdBO2ZvbnQtd2VpZ2h0Om5vcm1hbDt9DQogICAgICAgICAgICBpbWcsIGEgaW1ne2JvcmRlcjowOyBib3JkZXItc3R5bGU6bm9uZTtib3JkZXItY29sb3I6ICNmZmZmZmY7IG91dGxpbmU6bm9uZTsgdGV4dC1kZWNvcmF0aW9uOm5vbmU7aGVpZ2h0OmF1dG87IGxpbmUtaGVpZ2h0OjEwMCU7fQ0KICAgICAgICAgICAgYSB7dGV4dC1kZWNvcmF0aW9uOm5vbmUgIWltcG9ydGFudDsgZm9udC1mYW1pbHk6J0F2ZW5pck5leHRMVFByby1SZWd1bGFyJywnQXZlbmlyIE5leHQnLCdIZWx2ZXRpY2FOZXVlJywnSGVsdmV0aWNhIE5ldWUnLCdBdmVuaXInLEhlbHZldGljYSwgQXJpYWwsICJMdWNpZGEgR3JhbmRlIiwgc2Fucy1zZXJpZjt9DQogICAgICAgICAgICBoMSwgaDIsIGgzLCBoNCwgaDUsIGg2e2NvbG9yOiMzNjNDNDM7IGZvbnQtd2VpZ2h0OjUwMDsgZm9udC1mYW1pbHk6J0F2ZW5pck5leHRMVFByby1NZWRpdW0nLCdBdmVuaXIgTmV4dCcsJ0hlbHZldGljYU5ldWVNZWRpdW0nLCdIZWx2ZXRpY2FOZXVlLU1lZGl1bScsJ0hlbHZldGljYSBOZXVlIE1lZGl1bScsJ0hlbHZldGljYU5ldWUnLCdIZWx2ZXRpY2EgTmV1ZScsJ0F2ZW5pcicsSGVsdmV0aWNhOyBmb250LXNpemU6MjBweDsgbGluZS1oZWlnaHQ6MTI1JTsgdGV4dC1hbGlnbjpMZWZ0OyBsZXR0ZXItc3BhY2luZzpub3JtYWw7bWFyZ2luLXRvcDoxN3B4O21hcmdpbi1yaWdodDowO21hcmdpbi1ib3R0b206MTNweDttYXJnaW4tbGVmdDowO3BhZGRpbmctdG9wOjA7cGFkZGluZy1ib3R0b206MDtwYWRkaW5nLWxlZnQ6MDtwYWRkaW5nLXJpZ2h0OjA7fQ0KICAgICAgICAgICAgLyogQ0xJRU5ULVNQRUNJRklDIFNUWUxFUyAqLw0KICAgICAgICAgICAgLlJlYWRNc2dCb2R5e3dpZHRoOjEwMCU7fSAuRXh0ZXJuYWxDbGFzc3t3aWR0aDoxMDAlO30gLyogRm9yY2UgSG90bWFpbC9PdXRsb29rLmNvbSB0byBkaXNwbGF5IGVtYWlscyBhdCBmdWxsIHdpZHRoLiAqLw0KICAgICAgICAgICAgLkV4dGVybmFsQ2xhc3MsIC5FeHRlcm5hbENsYXNzIHAsIC5FeHRlcm5hbENsYXNzIHNwYW4sIC5FeHRlcm5hbENsYXNzIGZvbnQsIC5FeHRlcm5hbENsYXNzIHRkLCAuRXh0ZXJuYWxDbGFzcyBkaXZ7bGluZS1oZWlnaHQ6MTAwJTt9IC8qIEZvcmNlIEhvdG1haWwvT3V0bG9vay5jb20gdG8gZGlzcGxheSBsaW5lIGhlaWdodHMgbm9ybWFsbHkuICovDQogICAgICAgICAgICB0YWJsZSwgdGR7bXNvLXRhYmxlLWxzcGFjZTowcHQ7IG1zby10YWJsZS1yc3BhY2U6MHB0O30gLyogUmVtb3ZlIHNwYWNpbmcgYmV0d2VlbiB0YWJsZXMgaW4gT3V0bG9vayAyMDA3IGFuZCB1cC4gKi8NCiAgICAgICAgICAgICNvdXRsb29rIGF7cGFkZGluZzowO30gLyogRm9yY2UgT3V0bG9vayAyMDA3IGFuZCB1cCB0byBwcm92aWRlIGEgInZpZXcgaW4gYnJvd3NlciIgbWVzc2FnZS4gKi8NCiAgICAgICAgICAgIGltZ3stbXMtaW50ZXJwb2xhdGlvbi1tb2RlOiBiaWN1YmljO2Rpc3BsYXk6YmxvY2s7b3V0bGluZTpub25lOyB0ZXh0LWRlY29yYXRpb246bm9uZTt9IC8qIEZvcmNlIElFIHRvIHNtb290aGx5IHJlbmRlciByZXNpemVkIGltYWdlcy4gKi8NCiAgICAgICAgICAgIGJvZHksIHRhYmxlLCB0ZCwgcCwgYSwgbGksIGJsb2NrcXVvdGV7LW1zLXRleHQtc2l6ZS1hZGp1c3Q6MTAwJTsgLXdlYmtpdC10ZXh0LXNpemUtYWRqdXN0OjEwMCU7IC8qZm9udC13ZWlnaHQ6bm9ybWFsIWltcG9ydGFudDsqL30gLyogUHJldmVudCBXaW5kb3dzLSBhbmQgV2Via2l0LWJhc2VkIG1vYmlsZSBwbGF0Zm9ybXMgZnJvbSBjaGFuZ2luZyBkZWNsYXJlZCB0ZXh0IHNpemVzLiAqLw0KICAgICAgICAgICAgLkV4dGVybmFsQ2xhc3MgdGRbY2xhc3M9ImVjeGZsZXhpYmxlQ29udGFpbmVyQm94Il0gaDMge3BhZGRpbmctdG9wOiAxM3B4ICFpbXBvcnRhbnQ7fSAvKiBGb3JjZSBob3RtYWlsIHRvIHB1c2ggMi1ncmlkIHN1YiBoZWFkZXJzIGRvd24gKi8NCiAgICAgICAgICAgIC8qIC9cL1wvXC9cL1wvXC9cL1wvIFRFTVBMQVRFIFNUWUxFUyAvXC9cL1wvXC9cL1wvXC9cLyAqLw0KICAgICAgICAgICAgLyogPT09PT09PT09PSBQYWdlIFN0eWxlcyA9PT09PT09PT09ICovDQogICAgICAgICAgICBoMXtkaXNwbGF5OmJsb2NrO2ZvbnQtc2l6ZToyNnB4O2ZvbnQtc3R5bGU6bm9ybWFsO2ZvbnQtd2VpZ2h0OjUwMDtsaW5lLWhlaWdodDoxMDAlO30NCiAgICAgICAgICAgIGgye2Rpc3BsYXk6YmxvY2s7Zm9udC1zaXplOjIwcHg7Zm9udC1zdHlsZTpub3JtYWw7Zm9udC13ZWlnaHQ6NTAwO2xpbmUtaGVpZ2h0OjEyMCU7fQ0KICAgICAgICAgICAgaDN7ZGlzcGxheTpibG9jaztmb250LXNpemU6MTdweDtmb250LXN0eWxlOm5vcm1hbDtmb250LXdlaWdodDo1MDA7bGluZS1oZWlnaHQ6MTEwJTt9DQogICAgICAgICAgICBoNHtkaXNwbGF5OmJsb2NrO2ZvbnQtc2l6ZToxOHB4O2ZvbnQtc3R5bGU6aXRhbGljO2ZvbnQtd2VpZ2h0OjUwMDtsaW5lLWhlaWdodDoxMDAlO30NCiAgICAgICAgICAgIC5mbGV4aWJsZUltYWdle2hlaWdodDphdXRvO30NCiAgICAgICAgICAgIC5saW5rUmVtb3ZlQm9yZGVye2JvcmRlci1ib3R0b206MCAhaW1wb3J0YW50O30NCiAgICAgICAgICAgIHRhYmxlW2NsYXNzPWZsZXhpYmxlQ29udGFpbmVyQ2VsbERpdmlkZXJdIHtwYWRkaW5nLWJvdHRvbTowICFpbXBvcnRhbnQ7cGFkZGluZy10b3A6MCAhaW1wb3J0YW50O30NCiAgICAgICAgICAgIGJvZHksICNib2R5VGFibGV7YmFja2dyb3VuZC1jb2xvcjojRjBGMUYzO30NCiAgICAgICAgICAgICNlbWFpbEhlYWRlcntiYWNrZ3JvdW5kLWNvbG9yOiNGMEYxRjM7fQ0KICAgICAgICAgICAgI2VtYWlsQm9keXtiYWNrZ3JvdW5kLWNvbG9yOiNGRkZGRkY7IC8qYm9yZGVyLWNvbGxhcHNlOmNvbGxhcHNlOyovIGJvcmRlci1zcGFjaW5nOiAwcHg7IGJvcmRlcjoxcHggc29saWQgI0UwRTFFMjsgYm94LXNoYWRvdzogMCAwIDE1cHggI0U4RUFFQzstbW96LWJveC1zaGFkb3c6IDAgMCAxNXB4ICNFOEVBRUM7LXdlYmtpdC1ib3gtc2hhZG93OiAwIDAgMTVweCAjRThFQUVDO2JvcmRlci1yYWRpdXM6IDVweDsgLXdlYmtpdC1ib3JkZXItcmFkaXVzOiA1cHg7IC1tb3otYm9yZGVyLXJhZGl1czogNXB4fQ0KICAgICAgICAgICAgI2VtYWlsRm9vdGVye2JhY2tncm91bmQtY29sb3I6I0YwRjFGMzt9DQogICAgICAgICAgICAudGV4dENvbnRlbnQsIC50ZXh0Q29udGVudExhc3R7Y29sb3I6IzZCNzA3NTsgZm9udC1mYW1pbHk6J0F2ZW5pck5leHRMVFByby1SZWd1bGFyJywnQXZlbmlyIE5leHQnLCdIZWx2ZXRpY2FOZXVlJywnSGVsdmV0aWNhIE5ldWUnLCdBdmVuaXInLEhlbHZldGljYTsgZm9udC1zaXplOjE2cHg7IGxpbmUtaGVpZ2h0OjEyNSU7IHRleHQtYWxpZ246bGVmdDt9DQogICAgICAgICAgICAudGV4dENvbnRlbnQgYSwgLnRleHRDb250ZW50TGFzdCBhe2NvbG9yOiMyRDlBRTU7IHRleHQtZGVjb3JhdGlvbjp1bmRlcmxpbmU7fQ0KICAgICAgICAgICAgLm5lc3RlZENvbnRhaW5lcntiYWNrZ3JvdW5kLWNvbG9yOiNGNkY3Rjg7IGJvcmRlcjoxcHggc29saWQgI0YzRjNGNTt9DQogICAgICAgICAgICAuZW1haWxCdXR0b257YmFja2dyb3VuZC1jb2xvcjojMkQ5QUU1OyBib3JkZXItY29sbGFwc2U6c2VwYXJhdGU7fQ0KICAgICAgICAgICAgLmJ1dHRvbkNvbnRlbnR7Y29sb3I6I0ZGRkZGRjsgZm9udC1mYW1pbHk6J0F2ZW5pck5leHRMVFByby1SZWd1bGFyJywnQXZlbmlyIE5leHQnLCdIZWx2ZXRpY2FOZXVlJywnSGVsdmV0aWNhIE5ldWUnLCdBdmVuaXInLEhlbHZldGljYTsgZm9udC1zaXplOjE4cHg7IGZvbnQtd2VpZ2h0OmJvbGQ7IGxpbmUtaGVpZ2h0OjEwMCU7IHBhZGRpbmc6MTVweDsgdGV4dC1hbGlnbjpjZW50ZXI7fQ0KICAgICAgICAgICAgLmJ1dHRvbkNvbnRlbnQgYXtjb2xvcjojRkZGRkZGOyBkaXNwbGF5OmJsb2NrOyB0ZXh0LWRlY29yYXRpb246bm9uZSFpbXBvcnRhbnQ7IGJvcmRlcjowIWltcG9ydGFudDt9DQogICAgICAgICAgICAuZW1haWxDYWxlbmRhcntiYWNrZ3JvdW5kLWNvbG9yOiNGRkZGRkY7IGJvcmRlcjoxcHggc29saWQgI0NDQ0NDQzt9DQogICAgICAgICAgICAuZW1haWxDYWxlbmRhck1vbnRoe2JhY2tncm91bmQtY29sb3I6IzJEOUFFNTsgY29sb3I6I0ZGRkZGRjsgZm9udC1mYW1pbHk6J0F2ZW5pck5leHRMVFByby1SZWd1bGFyJywnQXZlbmlyIE5leHQnLCdIZWx2ZXRpY2FOZXVlJywnSGVsdmV0aWNhIE5ldWUnLCdBdmVuaXInLEhlbHZldGljYSwgQXJpYWwsIHNhbnMtc2VyaWY7IGZvbnQtc2l6ZToxNnB4OyBmb250LXdlaWdodDpib2xkOyBwYWRkaW5nLXRvcDoxMHB4OyBwYWRkaW5nLWJvdHRvbToxMHB4OyB0ZXh0LWFsaWduOmNlbnRlcjt9DQogICAgICAgICAgICAuZW1haWxDYWxlbmRhckRheXtjb2xvcjojMkQ5QUU1OyBmb250LWZhbWlseTonQXZlbmlyTmV4dExUUHJvLVJlZ3VsYXInLCdBdmVuaXIgTmV4dCcsJ0hlbHZldGljYU5ldWUnLCdIZWx2ZXRpY2EgTmV1ZScsJ0F2ZW5pcicsSGVsdmV0aWNhLCBBcmlhbCwgc2Fucy1zZXJpZjsgZm9udC1zaXplOjYwcHg7IGZvbnQtd2VpZ2h0OmJvbGQ7IGxpbmUtaGVpZ2h0OjEwMCU7IHBhZGRpbmctdG9wOjIwcHg7IHBhZGRpbmctYm90dG9tOjIwcHg7IHRleHQtYWxpZ246Y2VudGVyO30NCiAgICAgICAgICAgIC5pbWFnZUNvbnRlbnRUZXh0IHttYXJnaW4tdG9wOiAxMHB4O2xpbmUtaGVpZ2h0OjA7fQ0KICAgICAgICAgICAgLmltYWdlQ29udGVudFRleHQgYSB7bGluZS1oZWlnaHQ6MDt9DQogICAgICAgICAgICAjaW52aXNpYmxlSW50cm9kdWN0aW9uIHtkaXNwbGF5Om5vbmUgIWltcG9ydGFudDsgZm9udC1zaXplOjFweH0gLyogUmVtb3ZpbmcgdGhlIGludHJvZHVjdGlvbiB0ZXh0IGZyb20gdGhlIHZpZXcgKi8NCiAgICAgICAgICAgIC5pb3MtZm9vdGVyIHsgY29sb3I6ICM4RDkxOTY7IHRleHQtZGVjb3JhdGlvbjogbm9uZSAhaW1wb3J0YW50fQ0KICAgICAgICAgICAgLmlvcy1mb290ZXIgYSB7IGNvbG9yOiAjOEQ5MTk2OyB0ZXh0LWRlY29yYXRpb246IG5vbmUgIWltcG9ydGFudDsgfQ0KICAgICAgICAgICAgLypGUkFNRVdPUksgSEFDS1MgJmFtcDsgT1ZFUlJJREVTICovDQogICAgICAgICAgICBzcGFuW2NsYXNzPWlvcy1jb2xvci1oYWNrXSBhIHtjb2xvcjojMjc1MTAwIWltcG9ydGFudDt0ZXh0LWRlY29yYXRpb246bm9uZSFpbXBvcnRhbnQ7fSAvKiBSZW1vdmUgYWxsIGxpbmsgY29sb3JzIGluIElPUyAoYmVsb3cgYXJlIGR1cGxpY2F0ZXMgYmFzZWQgb24gdGhlIGNvbG9yIHByZWZlcmVuY2UpICovDQogICAgICAgICAgICBzcGFuW2NsYXNzPWlvcy1jb2xvci1oYWNrMl0gYSB7Y29sb3I6IzJEOUFFNSFpbXBvcnRhbnQ7dGV4dC1kZWNvcmF0aW9uOm5vbmUhaW1wb3J0YW50O30NCiAgICAgICAgICAgIHNwYW5bY2xhc3M9aW9zLWNvbG9yLWhhY2szXSBhIHtjb2xvcjojNkI3MDc1IWltcG9ydGFudDt0ZXh0LWRlY29yYXRpb246bm9uZSFpbXBvcnRhbnQ7fQ0KICAgICAgICAgICAgLmFbaHJlZl49InRlbCJdLCBhW2hyZWZePSJzbXMiXSB7dGV4dC1kZWNvcmF0aW9uOm5vbmUhaW1wb3J0YW50O2NvbG9yOiM2MDYwNjAhaW1wb3J0YW50O3BvaW50ZXItZXZlbnRzOm5vbmUhaW1wb3J0YW50O2N1cnNvcjpkZWZhdWx0IWltcG9ydGFudDt9DQogICAgICAgICAgICAubW9iaWxlX2xpbmsgYVtocmVmXj0idGVsIl0sIC5tb2JpbGVfbGluayBhW2hyZWZePSJzbXMiXSB7dGV4dC1kZWNvcmF0aW9uOm5vbmUhaW1wb3J0YW50O2NvbG9yOiM2MDYwNjAhaW1wb3J0YW50O3BvaW50ZXItZXZlbnRzOmF1dG8haW1wb3J0YW50O2N1cnNvcjpkZWZhdWx0IWltcG9ydGFudDt9DQogICAgICAgICAgICAvKiBNT0JJTEUgU1RZTEVTICovDQogICAgICAgICAgICBAbWVkaWEgb25seSBzY3JlZW4gYW5kIChtYXgtd2lkdGg6IDYxNXB4KXsNCiAgICAgICAgICAgIC8qLy8vLy8vIENMSUVOVC1TUEVDSUZJQyBTVFlMRVMgLy8vLy8vKi8NCiAgICAgICAgICAgIGJvZHl7d2lkdGg6MTAwJSAhaW1wb3J0YW50OyBtaW4td2lkdGg6MTAwJSAhaW1wb3J0YW50O30gLyogRm9yY2UgaU9TIE1haWwgdG8gcmVuZGVyIHRoZSBlbWFpbCBhdCBmdWxsIHdpZHRoLiAqLw0KICAgICAgICAgICAgLyogRlJBTUVXT1JLIFNUWUxFUyAqLw0KICAgICAgICAgICAgdGFibGVbaWQ9ImVtYWlsSGVhZGVyIl0sIHRhYmxlW2lkPSJlbWFpbEJvZHkiXSwgdGFibGVbaWQ9ImVtYWlsRm9vdGVyIl0ge3dpZHRoOjk1JSAhaW1wb3J0YW50O30NCiAgICAgICAgICAgIHRhYmxlW2NsYXNzPSJmbGV4aWJsZUNvbnRhaW5lciJdIHt3aWR0aDoxMDAlICFpbXBvcnRhbnQ7fQ0KICAgICAgICAgICAgdGRbY2xhc3M9ImZsZXhpYmxlQ29udGFpbmVyQm94Il0sIHRkW2NsYXNzPSJmbGV4aWJsZUNvbnRhaW5lckJveCJdIHRhYmxlIHsvKmRpc3BsYXk6IGJsb2NrOyovd2lkdGg6IDEwMCU7dGV4dC1hbGlnbjogbGVmdDt9DQogICAgICAgICAgICB0ZFtjbGFzcz0iaW1hZ2VDb250ZW50Il0gaW1nIHtoZWlnaHQ6YXV0byAhaW1wb3J0YW50OyB3aWR0aDoxMDAlICFpbXBvcnRhbnQ7IG1heC13aWR0aDoxMDAlICFpbXBvcnRhbnQ7fQ0KICAgICAgICAgICAgaW1nW2NsYXNzPSJmbGV4aWJsZUltYWdlIl17aGVpZ2h0OmF1dG8gIWltcG9ydGFudDsgd2lkdGg6MTAwJSAhaW1wb3J0YW50O21heC13aWR0aDoxMDAlICFpbXBvcnRhbnQ7fQ0KICAgICAgICAgICAgaW1nW2NsYXNzPSJmbGV4aWJsZUltYWdlU21hbGwiXXtoZWlnaHQ6YXV0byAhaW1wb3J0YW50OyB3aWR0aDphdXRvICFpbXBvcnRhbnQ7fQ0KICAgICAgICAgICAgLyoNCiAgICAgICAgICAgIENyZWF0ZSB0b3Agc3BhY2UgZm9yIGV2ZXJ5IHNlY29uZCBlbGVtZW50IGluIGEgYmxvY2sNCiAgICAgICAgICAgICovDQogICAgICAgICAgICB0YWJsZVtjbGFzcz0iZmxleGlibGVDb250YWluZXJCb3hOZXh0Il17LypwYWRkaW5nLXRvcDogMzBweCAhaW1wb3J0YW50OyovfQ0KICAgICAgICAgICAgLyoNCiAgICAgICAgICAgIE1ha2UgYnV0dG9ucyBpbiB0aGUgZW1haWwgc3BhbiB0aGUNCiAgICAgICAgICAgIGZ1bGwgd2lkdGggb2YgdGhlaXIgY29udGFpbmVyLCBhbGxvd2luZw0KICAgICAgICAgICAgZm9yIGxlZnQtIG9yIHJpZ2h0LWhhbmRlZCBlYXNlIG9mIHVzZS4NCiAgICAgICAgICAgICovDQogICAgICAgICAgICB0YWJsZVtjbGFzcz0iZW1haWxCdXR0b24iXXt3aWR0aDoxMDAlICFpbXBvcnRhbnQ7fQ0KICAgICAgICAgICAgdGRbY2xhc3M9ImJ1dHRvbkNvbnRlbnQiXXtwYWRkaW5nOjAgIWltcG9ydGFudDt9DQogICAgICAgICAgICB0ZFtjbGFzcz0iYnV0dG9uQ29udGVudCJdIGF7cGFkZGluZzoxNXB4ICFpbXBvcnRhbnQ7fQ0KICAgICAgICAgICAgLyogRlVMTC1XSURUSCBUQUJMRVMgKi8NCiAgICAgICAgICAgIHRhYmxlW2NsYXNzPSJyZXNwb25zaXZlLXRhYmxlIl17DQogICAgICAgICAgICB3aWR0aDoxMDAlIWltcG9ydGFudDsNCiAgICAgICAgICAgIH0NCiAgICAgICAgICAgIC8qIFVUSUxJVFkgQ0xBU1NFUyBGT1IgQURKVVNUSU5HIFBBRERJTkcgT04gTU9CSUxFICovDQogICAgICAgICAgICB0ZFtjbGFzcz0icGFkZGluZyJdew0KICAgICAgICAgICAgcGFkZGluZzogMTBweCAwcHggMTBweCAwcHggIWltcG9ydGFudDsNCiAgICAgICAgICAgIHRleHQtYWxpZ246IGNlbnRlcjsNCiAgICAgICAgICAgIH0NCiAgICAgICAgICAgIC8qIEFESlVTVCBCVVRUT05TIE9OIE1PQklMRSAqLw0KICAgICAgICAgICAgdGRbY2xhc3M9Im1vYmlsZS13cmFwcGVyIl17DQogICAgICAgICAgICBwYWRkaW5nOiAxMHB4IDUlIDE1cHggNSUgIWltcG9ydGFudDsNCiAgICAgICAgICAgIH0NCiAgICAgICAgICAgIHRhYmxlW2NsYXNzPSJtb2JpbGUtYnV0dG9uLWNvbnRhaW5lciJdew0KICAgICAgICAgICAgbWFyZ2luOjAgYXV0bzsNCiAgICAgICAgICAgIHdpZHRoOjEwMCUgIWltcG9ydGFudDsNCiAgICAgICAgICAgIH0NCiAgICAgICAgICAgIGFbY2xhc3M9Im1vYmlsZS1idXR0b24iXXsNCiAgICAgICAgICAgIHdpZHRoOjgwJSAhaW1wb3J0YW50Ow0KICAgICAgICAgICAgLypwYWRkaW5nOiAxNXB4ICFpbXBvcnRhbnQ7Ki8gLypOb3Qgc3VyZSB3aGF0IHRoZXNlIGRvKi8NCiAgICAgICAgICAgIC8qYm9yZGVyOiAwICFpbXBvcnRhbnQ7Ki8NCiAgICAgICAgICAgIGZvbnQtc2l6ZTogMTZweCAhaW1wb3J0YW50Ow0KICAgICAgICAgICAgfQ0KICAgICAgICAgICAgfQ0KICAgICAgICAgICAgLyogIENPTkRJVElPTlMgRk9SIEFORFJPSUQgREVWSUNFUyBPTkxZDQogICAgICAgICAgICAqICAgaHR0cDovL2RldmVsb3Blci5hbmRyb2lkLmNvbS9ndWlkZS93ZWJhcHBzL3RhcmdldGluZy5odG1sDQogICAgICAgICAgICAqICAgaHR0cDovL3B1Z2V0d29ya3MuY29tLzIwMTEvMDQvY3NzLW1lZGlhLXF1ZXJpZXMtZm9yLXRhcmdldGluZy1kaWZmZXJlbnQtbW9iaWxlLWRldmljZXMvIDsNCiAgICAgICAgICAgID09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09Ki8NCiAgICAgICAgICAgIEBtZWRpYSBvbmx5IHNjcmVlbiBhbmQgKC13ZWJraXQtZGV2aWNlLXBpeGVsLXJhdGlvOi43NSl7DQogICAgICAgICAgICAvKiBQdXQgQ1NTIGZvciBsb3cgZGVuc2l0eSAobGRwaSkgQW5kcm9pZCBsYXlvdXRzIGluIGhlcmUgKi8NCiAgICAgICAgICAgIH0NCiAgICAgICAgICAgIEBtZWRpYSBvbmx5IHNjcmVlbiBhbmQgKC13ZWJraXQtZGV2aWNlLXBpeGVsLXJhdGlvOjEpew0KICAgICAgICAgICAgLyogUHV0IENTUyBmb3IgbWVkaXVtIGRlbnNpdHkgKG1kcGkpIEFuZHJvaWQgbGF5b3V0cyBpbiBoZXJlICovDQogICAgICAgICAgICB9DQogICAgICAgICAgICBAbWVkaWEgb25seSBzY3JlZW4gYW5kICgtd2Via2l0LWRldmljZS1waXhlbC1yYXRpbzoxLjUpew0KICAgICAgICAgICAgLyogUHV0IENTUyBmb3IgaGlnaCBkZW5zaXR5IChoZHBpKSBBbmRyb2lkIGxheW91dHMgaW4gaGVyZSAqLw0KICAgICAgICAgICAgfQ0KICAgICAgICAgICAgLyogZW5kIEFuZHJvaWQgdGFyZ2V0aW5nICovDQogICAgICAgICAgICAvKiBDT05ESVRJT05TIEZPUiBJT1MgREVWSUNFUyBPTkxZDQogICAgICAgICAgICA9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PSovDQogICAgICAgICAgICBAbWVkaWEgb25seSBzY3JlZW4gYW5kIChtaW4tZGV2aWNlLXdpZHRoIDogMzIwcHgpIGFuZCAobWF4LWRldmljZS13aWR0aDo1NjhweCkgew0KICAgICAgICAgICAgfQ0KICAgICAgICAgICAgLyogZW5kIElPUyB0YXJnZXRpbmcgKi8NCiAgICAgICAgPC9zdHlsZT4NCiAgICAgICAgPCEtLQ0KICAgICAgICAgICAgT3V0bG9vayBDb25kaXRpb25hbCBDU1MNCg0KICAgICAgICAgICAgVGhlc2UgdHdvIHN0eWxlIGJsb2NrcyB0YXJnZXQgT3V0bG9vayAyMDA3ICYgMjAxMCBzcGVjaWZpY2FsbHksIGZvcmNpbmcNCiAgICAgICAgICAgIGNvbHVtbnMgaW50byBhIHNpbmdsZSB2ZXJ0aWNhbCBzdGFjayBhcyBvbiBtb2JpbGUgY2xpZW50cy4gVGhpcyBpcw0KICAgICAgICAgICAgcHJpbWFyaWx5IGRvbmUgdG8gYXZvaWQgdGhlICdwYWdlIGJyZWFrIGJ1ZycgYW5kIGlzIG9wdGlvbmFsLg0KDQogICAgICAgICAgICBNb3JlIGluZm9ybWF0aW9uIGhlcmU6DQogICAgICAgICAgICBodHRwOi8vdGVtcGxhdGVzLm1haWxjaGltcC5jb20vZGV2ZWxvcG1lbnQvY3NzL291dGxvb2stY29uZGl0aW9uYWwtY3NzDQogICAgICAgIC0tPg0KICAgICAgICA8IS0tW2lmIG1zbyAxMl0+DQogICAgICAgICAgICA8c3R5bGUgdHlwZT0idGV4dC9jc3MiPg0KICAgICAgICAgICAgICAgIC5mbGV4aWJsZUNvbnRhaW5lcntkaXNwbGF5OmJsb2NrICFpbXBvcnRhbnQ7IHdpZHRoOjEwMCUgIWltcG9ydGFudDt9DQogICAgICAgICAgICA8L3N0eWxlPg0KICAgICAgICA8IVtlbmRpZl0tLT4NCiAgICAgICAgPCEtLVtpZiBtc28gMTRdPg0KICAgICAgICAgICAgPHN0eWxlIHR5cGU9InRleHQvY3NzIj4NCiAgICAgICAgICAgICAgICAuZmxleGlibGVDb250YWluZXJ7ZGlzcGxheTpibG9jayAhaW1wb3J0YW50OyB3aWR0aDoxMDAlICFpbXBvcnRhbnQ7fQ0KICAgICAgICAgICAgPC9zdHlsZT4NCiAgICAgICAgPCFbZW5kaWZdLS0+DQogICAgPC9oZWFkPg0KICAgIDxib2R5IHRvcG1hcmdpbj0iMCIgbGVmdG1hcmdpbj0iMCIgc3R5bGU9Inpvb206IDEwMCU7IGJhY2tncm91bmQtY29sb3I6ICNmMGYxZjM7IiBjb250ZW50ZWRpdGFibGU9InRydWUiIG1hcmdpbndpZHRoPSIwIiBtYXJnaW5oZWlnaHQ9IjAiIG9mZnNldD0iMCI+DQogICAgICAgIDxjZW50ZXIgc3R5bGU9ImJhY2tncm91bmQtY29sb3I6I0YwRjFGMzsiPg0KICAgICAgICA8L2NlbnRlcj4NCiAgICAgICAgPHRhYmxlIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGlkPSJib2R5VGFibGUiIHN0eWxlPSJ0YWJsZS1sYXlvdXQ6IGZpeGVkO21heC13aWR0aDoxMDAlICFpbXBvcnRhbnQ7d2lkdGg6IDEwMCUgIWltcG9ydGFudDttaW4td2lkdGg6IDEwMCUgIWltcG9ydGFudDsiIGJvcmRlcj0iMCIgY2VsbHNwYWNpbmc9IjAiIGNlbGxwYWRkaW5nPSIwIj4NCiAgICAgICAgICAgIDx0Ym9keT4NCiAgICAgICAgICAgICAgICA8dHI+DQogICAgICAgICAgICAgICAgICAgIDx0ZCBhbGlnbj0iY2VudGVyIiBpZD0iYm9keUNlbGwiIHZhbGlnbj0idG9wIj4NCiAgICAgICAgICAgICAgICAgICAgPCEtLSAvLyBFTUFJTCBIRUFERVIgLS0+DQogICAgICAgICAgICAgICAgICAgIDwhLS0gUFJFSEVBREVSIFRFWFQgLS0+DQogICAgICAgICAgICAgICAgICAgIDx0YWJsZSB3aWR0aD0iNjAwIiBpZD0iZW1haWxIZWFkZXIiIHN0eWxlPSJiYWNrZ3JvdW5kLWNvbG9yOiAjZjBmMWYzOyIgYm9yZGVyPSIwIiBjZWxsc3BhY2luZz0iMCIgY2VsbHBhZGRpbmc9IjAiPg0KICAgICAgICAgICAgICAgICAgICAgICAgPCEtLSBIRUFERVIgUk9XIC8vIC0tPg0KICAgICAgICAgICAgICAgICAgICAgICAgPHRib2R5Pg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0cj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRkIGFsaWduPSJjZW50ZXIiIHZhbGlnbj0idG9wIj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPCEtLSBDRU5URVJJTkcgVEFCTEUgLy8gLS0+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0YWJsZSB3aWR0aD0iMTAwJSIgYm9yZGVyPSIwIiBjZWxsc3BhY2luZz0iMCIgY2VsbHBhZGRpbmc9IjAiPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRib2R5Pg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0cj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRkIGFsaWduPSJjZW50ZXIiIHZhbGlnbj0idG9wIj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPCEtLSBGTEVYSUJMRSBDT05UQUlORVIgLy8gLS0+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0YWJsZSB3aWR0aD0iNjAwIiBjbGFzcz0iZmxleGlibGVDb250YWluZXIiIGJvcmRlcj0iMCIgY2VsbHNwYWNpbmc9IjAiIGNlbGxwYWRkaW5nPSIwIj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0Ym9keT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dHI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0ZCBjbGFzcz0iZmxleGlibGVDb250YWluZXJDZWxsIiB2YWxpZ249InRvcCIgc3R5bGU9IndpZHRoOiA2MDBweDsiPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8IS0tIENPTlRFTlQgVEFCTEUgLy8gLS0+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0YWJsZSB3aWR0aD0iMTAwJSIgYWxpZ249ImNlbnRlciIgYm9yZGVyPSIwIiBjZWxsc3BhY2luZz0iMCIgY2VsbHBhZGRpbmc9IjAiPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRib2R5Pg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0cj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRkIGFsaWduPSJjZW50ZXIiIGNsYXNzPSJmbGV4aWJsZUNvbnRhaW5lckJveCIgaWQ9ImludmlzaWJsZUludHJvZHVjdGlvbiIgdmFsaWduPSJtaWRkbGUiIHN0eWxlPSJkaXNwbGF5Om5vbmUgIWltcG9ydGFudDsiPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGFibGUgd2lkdGg9IjEwMCUiIHN0eWxlPSJtYXgtd2lkdGg6MTAwJTsiIGJvcmRlcj0iMCIgY2VsbHNwYWNpbmc9IjAiIGNlbGxwYWRkaW5nPSIwIj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0Ym9keT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dHI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0ZCBhbGlnbj0iY2VudGVyIiBjbGFzcz0idGV4dENvbnRlbnQiPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8ZGl2IHN0eWxlPSJmb250LWZhbWlseTonQXZlbmlyTmV4dExUUHJvLVJlZ3VsYXInLCdBdmVuaXIgTmV4dCcsJ0hlbHZldGljYU5ldWUnLCdIZWx2ZXRpY2EgTmV1ZScsJ0F2ZW5pcicsSGVsdmV0aWNhLEFyaWFsLHNhbnMtc2VyaWY7IGZvbnQtc2l6ZToxcHg7Y29sb3I6I0YwRjFGMzt0ZXh0LWFsaWduOmNlbnRlcjtsaW5lLWhlaWdodDoxMjAlOyBkaXNwbGF5OiBibG9jazsiPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBUaGlzIGlzIHlvdXIgcmVjZWlwdC4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC9kaXY+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdGQ+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90cj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdGJvZHk+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdGFibGU+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdGQ+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90cj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdGJvZHk+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdGFibGU+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdGQ+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90cj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdGJvZHk+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdGFibGU+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwhLS0gLy8gRkxFWElCTEUgQ09OVEFJTkVSIC0tPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RkPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdHI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3Rib2R5Pg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RhYmxlPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8IS0tIC8vIENFTlRFUklORyBUQUJMRSAtLT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90ZD4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RyPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwhLS0gLy8gRU5EIC0tPg0KICAgICAgICAgICAgICAgICAgICAgICAgPC90Ym9keT4NCiAgICAgICAgICAgICAgICAgICAgPC90YWJsZT4NCiAgICAgICAgICAgICAgICAgICAgPCEtLSAvLyBFTkQgLS0+DQogICAgICAgICAgICAgICAgICAgIDwhLS0gRU1BSUwgSEVBREVSIFcgTE9HTyAvLyAtLT4NCiAgICAgICAgICAgICAgICAgICAgPCEtLQ0KICAgICAgICAgICAgICAgICAgICAgICAgICAgIFRoZSB0YWJsZSAiZW1haWxCb2R5IiBpcyB0aGUgZW1haWwncyBjb250YWluZXIuDQogICAgICAgICAgICAgICAgICAgICAgICAgICAgSXRzIHdpZHRoIGNhbiBiZSBzZXQgdG8gMTAwJSBmb3IgYSBjb2xvciBiYW5kDQogICAgICAgICAgICAgICAgICAgICAgICAgICAgdGhhdCBzcGFucyB0aGUgd2lkdGggb2YgdGhlIHBhZ2UuDQogICAgICAgICAgICAgICAgICAgICAgICAtLT4NCiAgICAgICAgICAgICAgICAgICAgPHRhYmxlIHdpZHRoPSI2MDAiIGlkPSJlbWFpbEhlYWRlciIgc3R5bGU9ImJhY2tncm91bmQtY29sb3I6ICNmMGYxZjM7IiBib3JkZXI9IjAiIGNlbGxzcGFjaW5nPSIwIiBjZWxscGFkZGluZz0iMCI+DQogICAgICAgICAgICAgICAgICAgICAgICA8IS0tIEhFQURFUiBST1cgLy8gLS0+DQogICAgICAgICAgICAgICAgICAgICAgICA8dGJvZHk+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRyPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGQgYWxpZ249ImNlbnRlciIgdmFsaWduPSJ0b3AiPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8IS0tIENFTlRFUklORyBUQUJMRSAvLyAtLT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRhYmxlIHdpZHRoPSIxMDAlIiBib3JkZXI9IjAiIGNlbGxzcGFjaW5nPSIwIiBjZWxscGFkZGluZz0iMCI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGJvZHk+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRyPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGQgYWxpZ249ImNlbnRlciIgdmFsaWduPSJ0b3AiPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8IS0tIEZMRVhJQkxFIENPTlRBSU5FUiAvLyAtLT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRhYmxlIHdpZHRoPSI2MDAiIGNsYXNzPSJmbGV4aWJsZUNvbnRhaW5lciIgYm9yZGVyPSIwIiBjZWxsc3BhY2luZz0iMCIgY2VsbHBhZGRpbmc9IjAiPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRib2R5Pg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0cj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRkIGNsYXNzPSJmbGV4aWJsZUNvbnRhaW5lckNlbGwiIHZhbGlnbj0idG9wIiBzdHlsZT0id2lkdGg6IDYwMHB4OyI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwhLS0gQ09OVEVOVCBUQUJMRSAvLyAtLT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRhYmxlIHdpZHRoPSIxMDAlIiBhbGlnbj0iY2VudGVyIiBib3JkZXI9IjAiIGNlbGxzcGFjaW5nPSIwIiBjZWxscGFkZGluZz0iMCI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGJvZHk+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRyPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGQgYWxpZ249ImNlbnRlciIgdmFsaWduPSJ0b3AiIHN0eWxlPSJkaXNwbGF5OmJsb2NrOyB0ZXh0LWFsaWduOmNlbnRlcjsiPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGFibGUgd2lkdGg9IjEwMCUiIHN0eWxlPSJtYXgtd2lkdGg6MTAwJTsiIGJvcmRlcj0iMCIgY2VsbHNwYWNpbmc9IjAiIGNlbGxwYWRkaW5nPSIwIj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0Ym9keT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dHI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0ZCBhbGlnbj0iY2VudGVyIj48YnIgLz4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90ZD4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RyPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90Ym9keT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90YWJsZT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90ZD4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RyPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90Ym9keT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90YWJsZT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90ZD4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RyPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90Ym9keT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90YWJsZT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPCEtLSAvLyBGTEVYSUJMRSBDT05UQUlORVIgLS0+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdGQ+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90cj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdGJvZHk+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdGFibGU+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwhLS0gLy8gQ0VOVEVSSU5HIFRBQkxFIC0tPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RkPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdHI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgPCEtLSAvLyBFTkQgLS0+DQogICAgICAgICAgICAgICAgICAgICAgICA8L3Rib2R5Pg0KICAgICAgICAgICAgICAgICAgICA8L3RhYmxlPg0KICAgICAgICAgICAgICAgICAgICA8IS0tIC8vIEVORCAtLT4NCiAgICAgICAgICAgICAgICAgICAgPCEtLSBTVEFSVCBXSElURSBDT05UQUlORVIgU0VDVElPTiAtLT4NCiAgICAgICAgICAgICAgICAgICAgPCEtLSBOT1RFOiBSRU1PVkUgbWFyZ2luLWJvdHRvbTogMjBweDsgRlJPTSBTVFlMRVMgSUYgVEhFUkUncyBPTkxZIE9ORSBDT05UQUlORVIgLS0+DQogICAgICAgICAgICAgICAgICAgIDx0YWJsZSB3aWR0aD0iNjAwIiBpZD0iZW1haWxCb2R5IiBzdHlsZT0ibWFyZ2luLWJvdHRvbTogMjBweDsgYm9yZGVyLXJhZGl1czogNXB4OyBiYWNrZ3JvdW5kLWNvbG9yOiAjZmZmZmZmOyIgYm9yZGVyPSIwIiBjZWxsc3BhY2luZz0iMCIgY2VsbHBhZGRpbmc9IjAiPg0KICAgICAgICAgICAgICAgICAgICAgICAgPCEtLSBNT0RVTEUgUk9XIC0gQkxBQ0sgQkFSLS0+DQogICAgICAgICAgICAgICAgICAgICAgICA8dGJvZHk+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRyPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGQgYWxpZ249ImNlbnRlciIgdmFsaWduPSJ0b3AiPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGFibGUgd2lkdGg9IjEwMCUiIHN0eWxlPSJjb2xvcjogI2ZmZmZmZjsgYm9yZGVyLXJhZGl1czogNHB4IDRweCAwcHggMHB4OyBiYWNrZ3JvdW5kLWNvbG9yOiAjOTE5ZWFiOyIgYm9yZGVyPSIwIiBjZWxsc3BhY2luZz0iMCIgY2VsbHBhZGRpbmc9IjAiPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRib2R5Pg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0cj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRkIGFsaWduPSJjZW50ZXIiIHZhbGlnbj0idG9wIj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRhYmxlIHdpZHRoPSI2MDAiIGNsYXNzPSJmbGV4aWJsZUNvbnRhaW5lciIgYm9yZGVyPSIwIiBjZWxsc3BhY2luZz0iMCIgY2VsbHBhZGRpbmc9IjAiPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRib2R5Pg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0cj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRkIGFsaWduPSJjZW50ZXIiIGNsYXNzPSJmbGV4aWJsZUNvbnRhaW5lckNlbGwiIHZhbGlnbj0idG9wIiBzdHlsZT0id2lkdGg6IDYwMHB4OyI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0YWJsZSB3aWR0aD0iMTAwJSIgYm9yZGVyPSIwIiBjZWxsc3BhY2luZz0iMCIgY2VsbHBhZGRpbmc9IjIiPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRib2R5Pg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0cj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRkIGFsaWduPSJjZW50ZXIiIGNsYXNzPSJ0ZXh0Q29udGVudCIgdmFsaWduPSJ0b3AiIHN0eWxlPSJwYWRkaW5nLWJvdHRvbToycHg7IGZvbnQtc2l6ZToxcHg7Ij4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90ZD4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RyPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90Ym9keT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90YWJsZT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90ZD4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RyPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90Ym9keT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90YWJsZT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90ZD4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RyPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90Ym9keT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90YWJsZT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90ZD4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RyPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwhLS0gLy8gTU9EVUxFIFJPVyAtIEJMQUNLIEJBUiAtLT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8IS0tIE1PRFVMRSBST1cgLy8gLS0+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgPCEtLQ0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBUbyBtb3ZlIG9yIGR1cGxpY2F0ZSBhbnkgb2YgdGhlIGRlc2lnbiBwYXR0ZXJucw0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpbiB0aGlzIGVtYWlsLCBzaW1wbHkgbW92ZSBvciBjb3B5IHRoZSBlbnRpcmUNCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgTU9EVUxFIFJPVyBzZWN0aW9uIGZvciBlYWNoIGNvbnRlbnQgYmxvY2suDQogICAgICAgICAgICAgICAgICAgICAgICAgICAgLS0+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRyPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGQgYWxpZ249ImNlbnRlciIgdmFsaWduPSJ0b3AiPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8IS0tIENFTlRFUklORyBUQUJMRSAvLyAtLT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPCEtLQ0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFRoZSBjZW50ZXJpbmcgdGFibGUga2VlcHMgdGhlIGNvbnRlbnQNCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB0YWJsZXMgY2VudGVyZWQgaW4gdGhlIGVtYWlsQm9keSB0YWJsZSwNCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpbiBjYXNlIGl0cyB3aWR0aCBpcyBzZXQgdG8gMTAwJS4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIC0tPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGFibGUgd2lkdGg9IjEwMCUiIHN0eWxlPSJjb2xvcjojMzYzYzQzOyBib3JkZXItcmFkaXVzOiA0cHggNHB4IDAgMDsiIGJvcmRlcj0iMCIgY2VsbHNwYWNpbmc9IjAiIGNlbGxwYWRkaW5nPSIwIj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0Ym9keT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dHI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0ZCBhbGlnbj0iY2VudGVyIiB2YWxpZ249InRvcCI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwhLS0gRkxFWElCTEUgQ09OVEFJTkVSIC8vIC0tPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8IS0tDQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgVGhlIGZsZXhpYmxlIGNvbnRhaW5lciBoYXMgYSBzZXQgd2lkdGgNCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB0aGF0IGdldHMgb3ZlcnJpZGRlbiBieSB0aGUgbWVkaWEgcXVlcnkuDQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgTW9zdCBjb250ZW50IHRhYmxlcyB3aXRoaW4gY2FuIHRoZW4gYmUNCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBnaXZlbiAxMDAlIHdpZHRocy4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIC0tPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGFibGUgd2lkdGg9IjYwMCIgY2xhc3M9ImZsZXhpYmxlQ29udGFpbmVyIiBib3JkZXI9IjAiIGNlbGxzcGFjaW5nPSIwIiBjZWxscGFkZGluZz0iMCI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGJvZHk+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRyPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGQgYWxpZ249ImNlbnRlciIgY2xhc3M9ImZsZXhpYmxlQ29udGFpbmVyQ2VsbCIgdmFsaWduPSJ0b3AiIHN0eWxlPSJ3aWR0aDogNjAwcHg7Ij4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPCEtLSBDT05URU5UIFRBQkxFIC8vIC0tPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8IS0tDQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgVGhlIGNvbnRlbnQgdGFibGUgaXMgdGhlIGZpcnN0IGVsZW1lbnQNCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB0aGF0J3MgZW50aXJlbHkgc2VwYXJhdGUgZnJvbSB0aGUgc3RydWN0dXJhbA0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGZyYW1ld29yayBvZiB0aGUgZW1haWwuDQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAtLT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRhYmxlIHdpZHRoPSIxMDAlIiBzdHlsZT0icGFkZGluZy10b3A6MTVweDsiIGJvcmRlcj0iMCIgY2VsbHNwYWNpbmc9IjAiIGNlbGxwYWRkaW5nPSIzMCI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGJvZHk+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRyPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGQgYWxpZ249ImNlbnRlciIgY2xhc3M9InRleHRDb250ZW50IiB2YWxpZ249InRvcCI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxoMSBzdHlsZT0iY29sb3I6IzAwMjA2MDtsaW5lLWhlaWdodDoxMjUlO2ZvbnQtZmFtaWx5OidBdmVuaXJOZXh0TFRQcm8tTWVkaXVtJywnQXZlbmlyIE5leHQnLCdIZWx2ZXRpY2FOZXVlTWVkaXVtJywnSGVsdmV0aWNhTmV1ZS1NZWRpdW0nLCdIZWx2ZXRpY2EgTmV1ZSBNZWRpdW0nLCdIZWx2ZXRpY2FOZXVlJywnSGVsdmV0aWNhIE5ldWUnLCdBdmVuaXInLEhlbHZldGljYSxBcmlhbCxzYW5zLXNlcmlmO2ZvbnQtc2l6ZTozMHB4O2ZvbnQtd2VpZ2h0OjUwMDttYXJnaW4tdG9wOjA7bWFyZ2luLWJvdHRvbToyMHB4O3RleHQtYWxpZ246Y2VudGVyOyI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTZweDsiPlJlY2VpcHQgZnJvbSB7TWVyY2hhbnROYW1lfTwvc3Bhbj48c3BhbiBzdHlsZT0iZm9udC1zaXplOiBtZWRpdW07Ij48L3NwYW4+PC9oMT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGRpdiBzdHlsZT0idGV4dC1hbGlnbjpjZW50ZXI7Zm9udC1mYW1pbHk6J0F2ZW5pck5leHRMVFByby1SZWd1bGFyJywnQXZlbmlyIE5leHQnLCdIZWx2ZXRpY2FOZXVlJywnSGVsdmV0aWNhIE5ldWUnLCdBdmVuaXInLEhlbHZldGljYSxBcmlhbCxzYW5zLXNlcmlmO2ZvbnQtc2l6ZToyMnB4O21hcmdpbi1ib3R0b206MDtjb2xvcjojOTE5ZWFiO2xpbmUtaGVpZ2h0OjEzNSU7Ij48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAzNnB4OyI+JHtBbW91bnR9PC9zcGFuPjwvZGl2Pg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8ZGl2IHN0eWxlPSJ0ZXh0LWFsaWduOmNlbnRlcjtmb250LWZhbWlseTonQXZlbmlyTmV4dExUUHJvLVJlZ3VsYXInLCdBdmVuaXIgTmV4dCcsJ0hlbHZldGljYU5ldWUnLCdIZWx2ZXRpY2EgTmV1ZScsJ0F2ZW5pcicsSGVsdmV0aWNhLEFyaWFsLHNhbnMtc2VyaWY7Zm9udC1zaXplOjIycHg7bWFyZ2luLWJvdHRvbTowO2NvbG9yOiM5MTllYWI7bGluZS1oZWlnaHQ6MTM1JTsiPjxzcGFuIHN0eWxlPSJ0ZXh0LWFsaWduOiBjZW50ZXI7IGJhY2tncm91bmQtY29sb3I6ICNmZmZmZmY7IGZvbnQtZmFtaWx5OiBBdmVuaXJOZXh0TFRQcm8tUmVndWxhciwgJ0F2ZW5pciBOZXh0JywgSGVsdmV0aWNhTmV1ZSwgJ0hlbHZldGljYSBOZXVlJywgQXZlbmlyLCBIZWx2ZXRpY2EsIEFyaWFsLCBzYW5zLXNlcmlmOyBmb250LXNpemU6IDE2cHg7IGNvbG9yOiAjOTE5ZWFiOyI+b24ge0RhdGV9PC9zcGFuPjwvZGl2Pg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RkPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdHI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3Rib2R5Pg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RhYmxlPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8IS0tIC8vIENPTlRFTlQgVEFCTEUgLS0+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdGQ+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90cj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdGJvZHk+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdGFibGU+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwhLS0gLy8gRkxFWElCTEUgQ09OVEFJTkVSIC0tPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RkPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdHI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3Rib2R5Pg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RhYmxlPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8IS0tIC8vIENFTlRFUklORyBUQUJMRSAtLT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90ZD4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RyPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwhLS0gLy8gTU9EVUxFIFJPVyAtLT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8IS0tIE1PRFVMRSBST1cgLy8gLS0+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRyPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGQgYWxpZ249ImNlbnRlciIgdmFsaWduPSJ0b3AiPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8IS0tIENFTlRFUklORyBUQUJMRSAvLyAtLT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRhYmxlIHdpZHRoPSIxMDAlIiBib3JkZXI9IjAiIGNlbGxzcGFjaW5nPSIwIiBjZWxscGFkZGluZz0iMCI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGJvZHk+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRyPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGQgYWxpZ249ImNlbnRlciIgdmFsaWduPSJ0b3AiPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8IS0tIEZMRVhJQkxFIENPTlRBSU5FUiAvLyAtLT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRhYmxlIHdpZHRoPSI2MDAiIGNsYXNzPSJmbGV4aWJsZUNvbnRhaW5lciIgYm9yZGVyPSIwIiBjZWxsc3BhY2luZz0iMCIgY2VsbHBhZGRpbmc9IjAiPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRib2R5Pg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0cj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRkIGFsaWduPSJjZW50ZXIiIGNsYXNzPSJmbGV4aWJsZUNvbnRhaW5lckNlbGwiIHZhbGlnbj0idG9wIiBzdHlsZT0id2lkdGg6IDYwMHB4OyI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0YWJsZSB3aWR0aD0iMTAwJSIgYm9yZGVyPSIwIiBjZWxsc3BhY2luZz0iMCIgY2VsbHBhZGRpbmc9IjMwIj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0Ym9keT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dHI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0ZCBhbGlnbj0iY2VudGVyIiB2YWxpZ249InRvcCIgc3R5bGU9InBhZGRpbmctYm90dG9tOjBweDtwYWRkaW5nLXRvcDoyMHB4OyI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0YWJsZSB3aWR0aD0iMTAwJSIgc3R5bGU9IndpZHRoOiA1MzRweDsgaGVpZ2h0OiAzMDdweDsgbGVmdDogNzU2LjVweDsiIGJvcmRlcj0iMCIgY2VsbHNwYWNpbmc9IjAiIGNlbGxwYWRkaW5nPSIwIj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0Ym9keT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dHI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0ZCBjbGFzcz0idGV4dENvbnRlbnQiIHZhbGlnbj0idG9wIj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGRpdiBzdHlsZT0idGV4dC1hbGlnbjogbGVmdDsgZm9udC1mYW1pbHk6IEF2ZW5pck5leHRMVFByby1SZWd1bGFyLCAnQXZlbmlyIE5leHQnLCBIZWx2ZXRpY2FOZXVlLCAnSGVsdmV0aWNhIE5ldWUnLCBBdmVuaXIsIEhlbHZldGljYSwgQXJpYWwsIHNhbnMtc2VyaWY7IGZvbnQtc2l6ZTogMTZweDsgbWFyZ2luLWJvdHRvbTogMHB4OyBtYXJnaW4tdG9wOiAzcHg7IGNvbG9yOiAjNmI3MDc1OyBsaW5lLWhlaWdodDogMTM1JTsiPjxzdHJvbmc+VHJhbnNhY3Rpb24gRGV0YWlsczwvc3Ryb25nPjwvZGl2Pg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8cCBzdHlsZT0idGV4dC1hbGlnbjogY2VudGVyOyBmb250LWZhbWlseTogQXZlbmlyTmV4dExUUHJvLVJlZ3VsYXIsICdBdmVuaXIgTmV4dCcsIEhlbHZldGljYU5ldWUsICdIZWx2ZXRpY2EgTmV1ZScsIEF2ZW5pciwgSGVsdmV0aWNhLCBBcmlhbCwgc2Fucy1zZXJpZjsgZm9udC1zaXplOiAxNnB4OyBtYXJnaW4tYm90dG9tOiAwcHg7IG1hcmdpbi10b3A6IDNweDsgY29sb3I6ICM2YjcwNzU7IGxpbmUtaGVpZ2h0OiAxMzUlOyI+PHN0cm9uZz4mbmJzcDs8L3N0cm9uZz48L3A+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxkaXYgc3R5bGU9InRleHQtYWxpZ246IGxlZnQ7IGZvbnQtZmFtaWx5OiBBdmVuaXJOZXh0TFRQcm8tUmVndWxhciwgJ0F2ZW5pciBOZXh0JywgSGVsdmV0aWNhTmV1ZSwgJ0hlbHZldGljYSBOZXVlJywgQXZlbmlyLCBIZWx2ZXRpY2EsIEFyaWFsLCBzYW5zLXNlcmlmOyBmb250LXNpemU6IDE2cHg7IG1hcmdpbi1ib3R0b206IDBweDsgbWFyZ2luLXRvcDogM3B4OyBjb2xvcjogIzZiNzA3NTsgbGluZS1oZWlnaHQ6IDEzNSU7Ij48c3BhbiBzdHlsZT0iZm9udDogNDAwIDE2cHggLyAyMS4zM3B4IEF2ZW5pck5leHRMVFByby1SZWd1bGFyLCAnQXZlbmlyIE5leHQnLCBIZWx2ZXRpY2FOZXVlLCAnSGVsdmV0aWNhIE5ldWUnLCBBdmVuaXIsIEhlbHZldGljYSwgQXJpYWwsIHNhbnMtc2VyaWY7IHRleHQtYWxpZ246IGxlZnQ7IHRleHQtdHJhbnNmb3JtOiBub25lOyB0ZXh0LWluZGVudDogMHB4OyBsZXR0ZXItc3BhY2luZzogbm9ybWFsOyB0ZXh0LWRlY29yYXRpb246IG5vbmU7IHdvcmQtc3BhY2luZzogMHB4OyB3aGl0ZS1zcGFjZTogbm9ybWFsOyBvcnBoYW5zOiAyOyBmbG9hdDogbm9uZTsgLXdlYmtpdC10ZXh0LXN0cm9rZS13aWR0aDogMHB4OyBiYWNrZ3JvdW5kLWNvbG9yOiAjZmZmZmZmOyBkaXNwbGF5OiBpbmxpbmUgIWltcG9ydGFudDsgY29sb3I6ICM2YjcwNzU7Ij5UcmFuc2FjdGlvbiBUeXBlOntUcmFuc2FjdGlvblR5cGV9PC9zcGFuPjwvZGl2Pg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8ZGl2IHN0eWxlPSJ0ZXh0LWFsaWduOiBsZWZ0OyBmb250LWZhbWlseTogQXZlbmlyTmV4dExUUHJvLVJlZ3VsYXIsICdBdmVuaXIgTmV4dCcsIEhlbHZldGljYU5ldWUsICdIZWx2ZXRpY2EgTmV1ZScsIEF2ZW5pciwgSGVsdmV0aWNhLCBBcmlhbCwgc2Fucy1zZXJpZjsgZm9udC1zaXplOiAxNnB4OyBtYXJnaW4tYm90dG9tOiAwcHg7IG1hcmdpbi10b3A6IDNweDsgY29sb3I6ICM2YjcwNzU7IGxpbmUtaGVpZ2h0OiAxMzUlOyI+PHNwYW4gc3R5bGU9ImZvbnQ6IDQwMCAxNnB4IC8gMjEuMzNweCBBdmVuaXJOZXh0TFRQcm8tUmVndWxhciwgJ0F2ZW5pciBOZXh0JywgSGVsdmV0aWNhTmV1ZSwgJ0hlbHZldGljYSBOZXVlJywgQXZlbmlyLCBIZWx2ZXRpY2EsIEFyaWFsLCBzYW5zLXNlcmlmOyB0ZXh0LWFsaWduOiBsZWZ0OyB0ZXh0LXRyYW5zZm9ybTogbm9uZTsgdGV4dC1pbmRlbnQ6IDBweDsgbGV0dGVyLXNwYWNpbmc6IG5vcm1hbDsgdGV4dC1kZWNvcmF0aW9uOiBub25lOyB3b3JkLXNwYWNpbmc6IDBweDsgd2hpdGUtc3BhY2U6IG5vcm1hbDsgb3JwaGFuczogMjsgZmxvYXQ6IG5vbmU7IC13ZWJraXQtdGV4dC1zdHJva2Utd2lkdGg6IDBweDsgYmFja2dyb3VuZC1jb2xvcjogdHJhbnNwYXJlbnQ7IGRpc3BsYXk6IGlubGluZSAhaW1wb3J0YW50OyBjb2xvcjogIzZiNzA3NTsiPlRyYW5zYWN0aW9uIFJlc3VsdDp7VHJhbnNhY3Rpb25SZXN1bHR9PC9zcGFuPjwvZGl2Pg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8ZGl2IHN0eWxlPSJ0ZXh0LWFsaWduOiBsZWZ0OyBmb250LWZhbWlseTogQXZlbmlyTmV4dExUUHJvLVJlZ3VsYXIsICdBdmVuaXIgTmV4dCcsIEhlbHZldGljYU5ldWUsICdIZWx2ZXRpY2EgTmV1ZScsIEF2ZW5pciwgSGVsdmV0aWNhLCBBcmlhbCwgc2Fucy1zZXJpZjsgZm9udC1zaXplOiAxNnB4OyBtYXJnaW4tYm90dG9tOiAwcHg7IG1hcmdpbi10b3A6IDNweDsgY29sb3I6ICM2YjcwNzU7IGxpbmUtaGVpZ2h0OiAxMzUlOyI+RGVzY3JpcHRpb246IHtEZXNjcmlwdGlvbn0mbmJzcDs8L2Rpdj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGRpdiBzdHlsZT0idGV4dC1hbGlnbjogbGVmdDsgZm9udC1mYW1pbHk6IEF2ZW5pck5leHRMVFByby1SZWd1bGFyLCAnQXZlbmlyIE5leHQnLCBIZWx2ZXRpY2FOZXVlLCAnSGVsdmV0aWNhIE5ldWUnLCBBdmVuaXIsIEhlbHZldGljYSwgQXJpYWwsIHNhbnMtc2VyaWY7IGZvbnQtc2l6ZTogMTZweDsgbWFyZ2luLWJvdHRvbTogMHB4OyBtYXJnaW4tdG9wOiAzcHg7IGNvbG9yOiAjNmI3MDc1OyBsaW5lLWhlaWdodDogMTM1JTsiPkFtb3VudDogJHtBbW91bnR9PC9kaXY+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxwIHN0eWxlPSJ0ZXh0LWFsaWduOiBsZWZ0OyBmb250LWZhbWlseTogQXZlbmlyTmV4dExUUHJvLVJlZ3VsYXIsICdBdmVuaXIgTmV4dCcsIEhlbHZldGljYU5ldWUsICdIZWx2ZXRpY2EgTmV1ZScsIEF2ZW5pciwgSGVsdmV0aWNhLCBBcmlhbCwgc2Fucy1zZXJpZjsgZm9udC1zaXplOiAxNnB4OyBtYXJnaW4tYm90dG9tOiAwcHg7IG1hcmdpbi10b3A6IDNweDsgY29sb3I6ICM2YjcwNzU7IGxpbmUtaGVpZ2h0OiAxMzUlOyI+UGF5bWVudCBNZXRob2QgVHlwZToge1BheW1lbnRNZXRob2RUeXBlfTwvcD4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGRpdiBzdHlsZT0idGV4dC1hbGlnbjogbGVmdDsgZm9udC1mYW1pbHk6IEF2ZW5pck5leHRMVFByby1SZWd1bGFyLCAnQXZlbmlyIE5leHQnLCBIZWx2ZXRpY2FOZXVlLCAnSGVsdmV0aWNhIE5ldWUnLCBBdmVuaXIsIEhlbHZldGljYSwgQXJpYWwsIHNhbnMtc2VyaWY7IGZvbnQtc2l6ZTogMTZweDsgbWFyZ2luLWJvdHRvbTogMHB4OyBtYXJnaW4tdG9wOiAzcHg7IGNvbG9yOiAjNmI3MDc1OyBsaW5lLWhlaWdodDogMTM1JTsiPkxhc3QgNCBEaWdpdHM6IHtMYXN0NERpZ2l0c308L2Rpdj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHAgc3R5bGU9InRleHQtYWxpZ246IGxlZnQ7IGZvbnQtZmFtaWx5OiBBdmVuaXJOZXh0TFRQcm8tUmVndWxhciwgJ0F2ZW5pciBOZXh0JywgSGVsdmV0aWNhTmV1ZSwgJ0hlbHZldGljYSBOZXVlJywgQXZlbmlyLCBIZWx2ZXRpY2EsIEFyaWFsLCBzYW5zLXNlcmlmOyBmb250LXNpemU6IDE2cHg7IG1hcmdpbi1ib3R0b206IDBweDsgbWFyZ2luLXRvcDogM3B4OyBjb2xvcjogIzZiNzA3NTsgbGluZS1oZWlnaHQ6IDEzNSU7Ij5DYXJkaG9sZGVyOiB7QWNjb3VudEhvbGRlck5hbWV9PC9wPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8cCBzdHlsZT0idGV4dC1hbGlnbjogbGVmdDsgZm9udC1mYW1pbHk6IEF2ZW5pck5leHRMVFByby1SZWd1bGFyLCAnQXZlbmlyIE5leHQnLCBIZWx2ZXRpY2FOZXVlLCAnSGVsdmV0aWNhIE5ldWUnLCBBdmVuaXIsIEhlbHZldGljYSwgQXJpYWwsIHNhbnMtc2VyaWY7IGZvbnQtc2l6ZTogMTZweDsgbWFyZ2luLWJvdHRvbTogMHB4OyBtYXJnaW4tdG9wOiAzcHg7IGNvbG9yOiAjNmI3MDc1OyBsaW5lLWhlaWdodDogMTM1JTsiPkF1dGggQ29kZToge0F1dGhDb2RlfTwvcD4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGRpdiBzdHlsZT0idGV4dC1hbGlnbjogbGVmdDsgZm9udC1mYW1pbHk6IEF2ZW5pck5leHRMVFByby1SZWd1bGFyLCAnQXZlbmlyIE5leHQnLCBIZWx2ZXRpY2FOZXVlLCAnSGVsdmV0aWNhIE5ldWUnLCBBdmVuaXIsIEhlbHZldGljYSwgQXJpYWwsIHNhbnMtc2VyaWY7IGZvbnQtc2l6ZTogMTZweDsgbWFyZ2luLWJvdHRvbTogMHB4OyBtYXJnaW4tdG9wOiAzcHg7IGNvbG9yOiAjNmI3MDc1OyBsaW5lLWhlaWdodDogMTM1JTsiPlRyYW5zYWN0aW9uIFJlZmVyZW5jZSAjOiB7UmVmTnVtfTwvZGl2Pg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8ZGl2IHN0eWxlPSJ0ZXh0LWFsaWduOiBsZWZ0OyBmb250LWZhbWlseTogQXZlbmlyTmV4dExUUHJvLVJlZ3VsYXIsICdBdmVuaXIgTmV4dCcsIEhlbHZldGljYU5ldWUsICdIZWx2ZXRpY2EgTmV1ZScsIEF2ZW5pciwgSGVsdmV0aWNhLCBBcmlhbCwgc2Fucy1zZXJpZjsgZm9udC1zaXplOiAxNnB4OyBtYXJnaW4tYm90dG9tOiAwcHg7IG1hcmdpbi10b3A6IDNweDsgY29sb3I6ICM2YjcwNzU7IGxpbmUtaGVpZ2h0OiAxMzUlOyI+Jm5ic3A7PC9kaXY+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxkaXYgc3R5bGU9InRleHQtYWxpZ246IGNlbnRlcjsgZm9udC1mYW1pbHk6IEF2ZW5pck5leHRMVFByby1SZWd1bGFyLCAnQXZlbmlyIE5leHQnLCBIZWx2ZXRpY2FOZXVlLCAnSGVsdmV0aWNhIE5ldWUnLCBBdmVuaXIsIEhlbHZldGljYSwgQXJpYWwsIHNhbnMtc2VyaWY7IGZvbnQtc2l6ZTogMTZweDsgbWFyZ2luLWJvdHRvbTogMHB4OyBtYXJnaW4tdG9wOiAzcHg7IGNvbG9yOiAjNmI3MDc1OyBsaW5lLWhlaWdodDogMTM1JTsiPiZuYnNwOzwvZGl2Pg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8cCBzdHlsZT0idGV4dC1hbGlnbjogbGVmdDsgZm9udC1mYW1pbHk6IEF2ZW5pck5leHRMVFByby1SZWd1bGFyLCAnQXZlbmlyIE5leHQnLCBIZWx2ZXRpY2FOZXVlLCAnSGVsdmV0aWNhIE5ldWUnLCBBdmVuaXIsIEhlbHZldGljYSwgQXJpYWwsIHNhbnMtc2VyaWY7IGZvbnQtc2l6ZTogMTZweDsgbWFyZ2luLWJvdHRvbTogMHB4OyBtYXJnaW4tdG9wOiAzcHg7IGNvbG9yOiAjNmI3MDc1OyBsaW5lLWhlaWdodDogMTM1JTsiPjxzcGFuIHN0eWxlPSJmb250LWZhbWlseTogQXZlbmlyTmV4dExUUHJvLVJlZ3VsYXIsICdBdmVuaXIgTmV4dCcsIEhlbHZldGljYU5ldWUsICdIZWx2ZXRpY2EgTmV1ZScsIEF2ZW5pciwgSGVsdmV0aWNhOyI+e1JlY2VpcHROb3RlfTwvc3Bhbj48L3A+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxwIHN0eWxlPSJ0ZXh0LWFsaWduOiBsZWZ0OyBmb250LWZhbWlseTogQXZlbmlyTmV4dExUUHJvLVJlZ3VsYXIsICdBdmVuaXIgTmV4dCcsIEhlbHZldGljYU5ldWUsICdIZWx2ZXRpY2EgTmV1ZScsIEF2ZW5pciwgSGVsdmV0aWNhLCBBcmlhbCwgc2Fucy1zZXJpZjsgZm9udC1zaXplOiAxNnB4OyBtYXJnaW4tYm90dG9tOiAwcHg7IG1hcmdpbi10b3A6IDNweDsgY29sb3I6ICM2YjcwNzU7IGxpbmUtaGVpZ2h0OiAxMzUlOyI+PHNwYW4gc3R5bGU9ImZvbnQtZmFtaWx5OiBBdmVuaXJOZXh0TFRQcm8tUmVndWxhciwgJ0F2ZW5pciBOZXh0JywgSGVsdmV0aWNhTmV1ZSwgJ0hlbHZldGljYSBOZXVlJywgQXZlbmlyLCBIZWx2ZXRpY2E7Ij48L3NwYW4+PC9wPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RkPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdHI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3Rib2R5Pg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RhYmxlPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RkPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdHI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3Rib2R5Pg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RhYmxlPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RkPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdHI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3Rib2R5Pg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RhYmxlPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RkPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdHI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3Rib2R5Pg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RhYmxlPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RkPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdHI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRyPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGQgYWxpZ249ImNlbnRlciIgdmFsaWduPSJ0b3AiPiZuYnNwOzwvdGQ+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90cj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dHI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0ZCBhbGlnbj0iY2VudGVyIiB2YWxpZ249InRvcCI+Jm5ic3A7PC90ZD4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RyPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0cj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRkIGFsaWduPSJjZW50ZXIiIHZhbGlnbj0idG9wIj4mbmJzcDs8L3RkPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdHI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRyPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGQgYWxpZ249ImNlbnRlciIgdmFsaWduPSJ0b3AiPiZuYnNwOzwvdGQ+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90cj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dHI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0ZCBhbGlnbj0iY2VudGVyIiB2YWxpZ249InRvcCI+Jm5ic3A7PC90ZD4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RyPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0cj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRkIGFsaWduPSJjZW50ZXIiIHZhbGlnbj0idG9wIj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRhYmxlIHdpZHRoPSIxMDAlIiBib3JkZXI9IjAiIGNlbGxzcGFjaW5nPSIwIiBjZWxscGFkZGluZz0iMCI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGJvZHk+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRyPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGQgYWxpZ249ImNlbnRlciIgdmFsaWduPSJ0b3AiPjxiciAvPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RkPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdHI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3Rib2R5Pg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RhYmxlPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8IS0tIC8vIENFTlRFUklORyBUQUJMRSAtLT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGJyIC8+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdGQ+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90cj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8IS0tIC8vIEVORCAtLT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8IS0tIE1PRFVMRSBST1cgLy8gLS0+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRyPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGQgYWxpZ249ImNlbnRlciIgdmFsaWduPSJ0b3AiPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8IS0tIENFTlRFUklORyBUQUJMRSAvLyAtLT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRhYmxlIHdpZHRoPSIxMDAlIiBzdHlsZT0id2lkdGg6IDYwM3B4OyBoZWlnaHQ6IDgwcHg7IiBib3JkZXI9IjAiIGNlbGxzcGFjaW5nPSIwIiBjZWxscGFkZGluZz0iMCI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGJvZHk+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRyPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGQgYWxpZ249ImNlbnRlciIgdmFsaWduPSJ0b3AiPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8IS0tIEZMRVhJQkxFIENPTlRBSU5FUiAvLyAtLT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRhYmxlIHdpZHRoPSI2MDAiIGNsYXNzPSJmbGV4aWJsZUNvbnRhaW5lciIgYm9yZGVyPSIwIiBjZWxsc3BhY2luZz0iMCIgY2VsbHBhZGRpbmc9IjAiPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRib2R5Pg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0cj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRkIGFsaWduPSJjZW50ZXIiIGNsYXNzPSJmbGV4aWJsZUNvbnRhaW5lckNlbGwiIHZhbGlnbj0idG9wIiBzdHlsZT0id2lkdGg6IDYwMHB4OyI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0YWJsZSB3aWR0aD0iMTAwJSIgc3R5bGU9IndpZHRoOiA1OTVweDsgaGVpZ2h0OiA3NXB4OyIgYm9yZGVyPSIwIiBjZWxsc3BhY2luZz0iMCIgY2VsbHBhZGRpbmc9IjMwIj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0Ym9keT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dHI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0ZCBhbGlnbj0iY2VudGVyIiB2YWxpZ249InRvcCIgc3R5bGU9InBhZGRpbmctYm90dG9tOjQwcHg7Ij4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPCEtLSBDT05URU5UIFRBQkxFIC8vIC0tPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGFibGUgd2lkdGg9IjEwMCUiIGJvcmRlcj0iMCIgY2VsbHNwYWNpbmc9IjAiIGNlbGxwYWRkaW5nPSIwIj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0Ym9keT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dHI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0ZCBjbGFzcz0idGV4dENvbnRlbnQiIHZhbGlnbj0idG9wIj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGRpdiBzdHlsZT0idGV4dC1hbGlnbjogY2VudGVyOyBmb250LWZhbWlseTogQXZlbmlyTmV4dExUUHJvLVJlZ3VsYXIsICdBdmVuaXIgTmV4dCcsIEhlbHZldGljYU5ldWUsICdIZWx2ZXRpY2EgTmV1ZScsIEF2ZW5pciwgSGVsdmV0aWNhLCBBcmlhbCwgc2Fucy1zZXJpZjsgZm9udC1zaXplOiAxNnB4OyBtYXJnaW4tYm90dG9tOiAwcHg7IG1hcmdpbi10b3A6IDNweDsgY29sb3I6ICM2YjcwNzU7IGxpbmUtaGVpZ2h0OiAxMjUlOyI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTRweDsiPkN1c3RvbWVyIENvcHk8L3NwYW4+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0YWJsZSB3aWR0aD0iMTAwJSIgYm9yZGVyPSIwIiBjZWxsc3BhY2luZz0iMCIgY2VsbHBhZGRpbmc9IjAiPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRib2R5Pg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90Ym9keT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90YWJsZT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPCEtLSAvLyBMT0dPIC0tPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L2Rpdj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90ZD4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RyPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90Ym9keT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90YWJsZT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPCEtLSAvLyBDT05URU5UIFRBQkxFIC0tPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RkPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdHI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3Rib2R5Pg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RhYmxlPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RkPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdHI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3Rib2R5Pg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RhYmxlPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8IS0tIC8vIEZMRVhJQkxFIENPTlRBSU5FUiAtLT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90ZD4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RyPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90Ym9keT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90YWJsZT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPCEtLSAvLyBDRU5URVJJTkcgVEFCTEUgLS0+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdGQ+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90cj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8IS0tIC8vIE1PRFVMRSBST1cgLS0+DQogICAgICAgICAgICAgICAgICAgICAgICA8L3Rib2R5Pg0KICAgICAgICAgICAgICAgICAgICA8L3RhYmxlPg0KICAgICAgICAgICAgICAgICAgICA8IS0tIEVORCBXSElURSBDT05UQUlORVIgU0VDVElPTiAtLT4NCiAgICAgICAgICAgICAgICAgICAgPCEtLSBFTUFJTCBGT09URVIgLy8gLS0+DQogICAgICAgICAgICAgICAgICAgIDwhLS0NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICBUaGUgdGFibGUgImVtYWlsQm9keSIgaXMgdGhlIGVtYWlsJ3MgY29udGFpbmVyLg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgIEl0cyB3aWR0aCBjYW4gYmUgc2V0IHRvIDEwMCUgZm9yIGEgY29sb3IgYmFuZA0KICAgICAgICAgICAgICAgICAgICAgICAgICAgIHRoYXQgc3BhbnMgdGhlIHdpZHRoIG9mIHRoZSBwYWdlLg0KICAgICAgICAgICAgICAgICAgICAgICAgLS0+DQogICAgICAgICAgICAgICAgICAgIDx0YWJsZSB3aWR0aD0iNjAwIiBpZD0iZW1haWxGb290ZXIiIHN0eWxlPSJiYWNrZ3JvdW5kLWNvbG9yOiAjZjBmMWYzOyIgYm9yZGVyPSIwIiBjZWxsc3BhY2luZz0iMCIgY2VsbHBhZGRpbmc9IjAiPg0KICAgICAgICAgICAgICAgICAgICAgICAgPCEtLSBGT09URVIgUk9XIC8vIC0tPg0KICAgICAgICAgICAgICAgICAgICAgICAgPCEtLQ0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBUbyBtb3ZlIG9yIGR1cGxpY2F0ZSBhbnkgb2YgdGhlIGRlc2lnbiBwYXR0ZXJucw0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpbiB0aGlzIGVtYWlsLCBzaW1wbHkgbW92ZSBvciBjb3B5IHRoZSBlbnRpcmUNCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgTU9EVUxFIFJPVyBzZWN0aW9uIGZvciBlYWNoIGNvbnRlbnQgYmxvY2suDQogICAgICAgICAgICAgICAgICAgICAgICAgICAgLS0+DQogICAgICAgICAgICAgICAgICAgICAgICA8dGJvZHk+DQogICAgICAgICAgICAgICAgICAgICAgICA8L3Rib2R5Pg0KICAgICAgICAgICAgICAgICAgICA8L3RhYmxlPg0KICAgICAgICAgICAgICAgICAgICA8IS0tIC8vIEVORCAtLT4NCiAgICAgICAgICAgICAgICAgICAgPHRhYmxlIHdpZHRoPSI2MDAiIGlkPSJlbWFpbEZvb3RlciIgc3R5bGU9ImJhY2tncm91bmQtY29sb3I6ICNmMGYxZjM7IiBib3JkZXI9IjAiIGNlbGxzcGFjaW5nPSIwIiBjZWxscGFkZGluZz0iMCI+DQogICAgICAgICAgICAgICAgICAgICAgICA8IS0tIEZPT1RFUiBST1cgLy8gLS0+DQogICAgICAgICAgICAgICAgICAgICAgICA8IS0tDQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFRvIG1vdmUgb3IgZHVwbGljYXRlIGFueSBvZiB0aGUgZGVzaWduIHBhdHRlcm5zDQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGluIHRoaXMgZW1haWwsIHNpbXBseSBtb3ZlIG9yIGNvcHkgdGhlIGVudGlyZQ0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBNT0RVTEUgUk9XIHNlY3Rpb24gZm9yIGVhY2ggY29udGVudCBibG9jay4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAtLT4NCiAgICAgICAgICAgICAgICAgICAgICAgIDx0Ym9keT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dHI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0ZCBhbGlnbj0iY2VudGVyIiB2YWxpZ249InRvcCI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwhLS0gQ0VOVEVSSU5HIFRBQkxFIC8vIC0tPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGFibGUgd2lkdGg9IjEwMCUiIGJvcmRlcj0iMCIgY2VsbHNwYWNpbmc9IjAiIGNlbGxwYWRkaW5nPSIwIj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0Ym9keT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dHI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0ZCBhbGlnbj0iY2VudGVyIiB2YWxpZ249InRvcCI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwhLS0gRkxFWElCTEUgQ09OVEFJTkVSIC8vIC0tPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGFibGUgd2lkdGg9IjYwMCIgY2xhc3M9ImZsZXhpYmxlQ29udGFpbmVyIiBib3JkZXI9IjAiIGNlbGxzcGFjaW5nPSIwIiBjZWxscGFkZGluZz0iMCI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGJvZHk+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRyPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGQgYWxpZ249ImNlbnRlciIgY2xhc3M9ImZsZXhpYmxlQ29udGFpbmVyQ2VsbCIgdmFsaWduPSJ0b3AiIHN0eWxlPSJ3aWR0aDogNjAwcHg7Ij4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRhYmxlIHdpZHRoPSIxMDAlIiBib3JkZXI9IjAiIGNlbGxzcGFjaW5nPSIwIiBjZWxscGFkZGluZz0iMCI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGJvZHk+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRyPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGQgdmFsaWduPSJ0b3AiIHN0eWxlPSJmb250LWZhbWlseTogQXZlbmlyTmV4dExUUHJvLVJlZ3VsYXIsICdBdmVuaXIgTmV4dCcsIEhlbHZldGljYU5ldWUsICdIZWx2ZXRpY2EgTmV1ZScsIEF2ZW5pciwgSGVsdmV0aWNhLCBBcmlhbCwgc2Fucy1zZXJpZjsgZm9udC1zaXplOiAxM3B4OyBjb2xvcjogIzZiNzA3NTsgdGV4dC1hbGlnbjogY2VudGVyOyBsaW5lLWhlaWdodDogMTIwJTsgcGFkZGluZy1ib3R0b206IDMwcHg7IGJhY2tncm91bmQtY29sb3I6ICNmMGYxZjM7Ij4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHA+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxhIGhyZWY9Imh0dHA6Ly93d3cuZWJpemNoYXJnZS5jb20vIiB0YXJnZXQ9Il9ibGFuayI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxpbWcgd2lkdGg9IjI4IiBoZWlnaHQ9IjI4IiBzdHlsZT0iZm9udC1mYW1pbHk6IEhlbHZldGljYSwgQXJpYWwsIHNhbnMtc2VyaWY7IGNvbG9yOiAjNjY2NjY2OyBmb250LXNpemU6IDE2cHg7IGJvcmRlci13aWR0aDogMHB4OyBib3JkZXItc3R5bGU6IHNvbGlkOyBkaXNwbGF5OiBpbmxpbmUtYmxvY2sgIWltcG9ydGFudDsgd2lkdGg6IDIxcHg7IGhlaWdodDogMjBweDsiIGFsdD0iRUJpekNoYXJnZUxvZ28iIHNyYz0iaHR0cHM6Ly9pLmliYi5jby9IcnJQc20yLzQtQy1TeW1ib2wtRUJpei1DaGFyZ2UucG5nIiAvPjwvYT48L3A+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxwPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDEycHg7Ij5Qb3dlcmVkIGJ5IEVCaXpDaGFyZ2U8L3NwYW4+PC9wPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RkPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdHI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3Rib2R5Pg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RhYmxlPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RkPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdHI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3Rib2R5Pg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RhYmxlPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8IS0tIC8vIEZMRVhJQkxFIENPTlRBSU5FUiAtLT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90ZD4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RyPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90Ym9keT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90YWJsZT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPCEtLSAvLyBDRU5URVJJTkcgVEFCTEUgLS0+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdGQ+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90cj4NCiAgICAgICAgICAgICAgICAgICAgICAgIDwvdGJvZHk+DQogICAgICAgICAgICAgICAgICAgIDwvdGFibGU+DQogICAgICAgICAgICAgICAgICAgIDwhLS0gLy8gRU5EIC0tPg0KICAgICAgICAgICAgICAgICAgICA8L3RkPg0KICAgICAgICAgICAgICAgIDwvdHI+DQogICAgICAgICAgICA8L3Rib2R5Pg0KICAgICAgICA8L3RhYmxlPg0KICAgICAgICA8ZGl2IGl0ZW10eXBlPSJodHRwOi8vc2NoZW1hLm9yZy9FbWFpbE1lc3NhZ2UiPg0KICAgICAgICA8ZGl2IGl0ZW10eXBlPSJodHRwOi8vc2NoZW1hLm9yZy9Pcmdhbml6YXRpb24iIGl0ZW1wcm9wPSJwdWJsaXNoZXIiPg0KICAgICAgICA8bWV0YSBjb250ZW50PSJXZWJmbG93IiBpdGVtcHJvcD0ibmFtZSIgLz4NCiAgICAgICAgPGxpbmsgaHJlZj0iaHR0cDovL3d3dy5lYml6Y2hhcmdlLmNvbS8iIGl0ZW1wcm9wPSJ1cmwiIC8+DQogICAgICAgIDxsaW5rIGhyZWY9Imh0dHBzOi8vcGx1cy5nb29nbGUuY29tL2IvMTE4MjgyMzYxNzM4MDg2OTg1MzY4IiBpdGVtcHJvcD0idXJsL2dvb2dsZVBsdXMiIC8+DQogICAgICAgIDwvZGl2Pg0KICAgICAgICA8ZGl2IGl0ZW10eXBlPSJodHRwOi8vc2NoZW1hLm9yZy9PZmZlciIgaXRlbXByb3A9ImFib3V0Ij4NCiAgICAgICAgPGxpbmsgaHJlZj0iaHR0cHM6Ly9kM2U1NHYxMDNqOHFiYi5jbG91ZGZyb250Lm5ldC9nZW4vaW1nL21hcmtldGluZy93ZWJmbG93LWxvZ28uc3ZnIiBpdGVtcHJvcD0iaW1hZ2UiIC8+DQogICAgICAgIDwvZGl2Pg0KICAgICAgICA8L2Rpdj4NCiAgICA8L2JvZHk+DQo8L2h0bWw+";
        $defaultEmailTemplate->TemplateText = "";
        $defaultEmailTemplate->FromEmail = "";
        $defaultEmailTemplate->FromName = "";
        $defaultEmailTemplate->ReplyToEmail = "";
        $defaultEmailTemplate->ReplyToDisplayName = "";
        $defaultEmailTemplate->TemplateSource = "2";
        $defaultEmailTemplate->TemplateTypeId = "TransactionReceiptCustomer";

        return $defaultEmailTemplate;
    }

    /**
     * Prepare Email Print Tempaltes
     *
     * @param null|mixed $transactionReferenceNumber
     * @param string $htmlTemplate
     * @return string|string[]
     * @throws NoSuchEntityException
     */
    public function prepareEmailPrintTemplate($transactionReferenceNumber = null, $htmlTemplate = '')
    {
        $htmlPrintTemplate = '';
        $store = $this->_storeManager->getStore();
        /** @var $customerTransactionResp */
        $customerTransactionResp = $this->getTransactionDetailByReferenceId($transactionReferenceNumber);

        if ($customerTransactionResp['error'] == false) {
            $customerTransaction = $customerTransactionResp['response'];
            $transactionDetail = array_merge(
                (array)$customerTransaction['Details'],
                (array)$customerTransaction['Response'],
                ['ShippingAddress' => (array)$customerTransaction['ShippingAddress']],
                ['LineItems' => (array)$customerTransaction['LineItems']],
                (array)$customerTransaction['User'],
                (array)$customerTransaction['CreditCardData'],
                ['CustomerID' => isset($customerTransaction['CustomerID']) ? $customerTransaction['CustomerID'] : ''],
                ['ClientIP' => isset($customerTransaction['ClientIP']) ? $customerTransaction['ClientIP'] : ''],
                ['CheckTrace' => isset($customerTransaction['CheckTrace']) ? $customerTransaction['CheckTrace'] : ''],
                ['CheckData' => isset($customerTransaction['CheckData']) ? $customerTransaction['CheckData'] : ''],
                ['BillingAddress' => (array)$customerTransaction['BillingAddress']],
                ['AccountHolder' => $customerTransaction['AccountHolder'] ?? ''],
                ['Status' => isset($customerTransaction['Status']) ? $customerTransaction['Status'] : ''],
                ['TransactionType' => $customerTransaction['TransactionType'] ?? ''],
                ['DateTime' => isset($customerTransaction['DateTime']) ? $customerTransaction['DateTime'] : '']
            );

            $cardData = (array)$customerTransaction['CreditCardData'];
            $last4Digit = isset($cardData['CardNumber']) ? $cardData['CardNumber'] : 'xxxx';
            $last4Digit = substr((string)$last4Digit, strlen($last4Digit) - 4);
            $customerBankAccount = isset($customerTransaction['checkData']) ?
                (array)$customerTransaction['checkData'] : [];
            $paymenType = 'Credit Card';

            /** Customer Bank Account */
            if (count($customerBankAccount) > 0) {
                $last4Digit = isset($customerBankAccount['Account']) ? $customerBankAccount['Account'] : 'xxxx';
                $paymenType = 'ACH';
            }

            if (isset($transactionDetail['CheckData'])) {
                $checkData = (array)$transactionDetail['CheckData'];
                $paymenType = isset($checkData['Account']) ? 'Check Sale' : 'Credit Card';
            }
            /** @var $transResponse */
            $transResponse = isset($customerTransaction['Response']) ? (array)$customerTransaction['Response'] : [];
            /** @var $transDetail */
            $transDetail = isset($customerTransaction['Details']) ? (array)$customerTransaction['Details'] : [];

            $authAmount = $this->_priceHelper->currencyByStore($transResponse['AuthAmount'] ?? 0, $store);
            $taxAmount = $this->_priceHelper->currencyByStore($transactionDetail['Tax'] ?? 0, $store);

            $transResult = isset($transResponse['Result']) ? $transResponse['Result'] : '*';
            $transactionType = isset($transResponse['TransactionType']) ? $transResponse['TransactionType'] : 'Auth';
            $authCode = isset($transResponse['AuthCode']) ? $transResponse['AuthCode'] : '*';
            $transRefNumber = isset($transResponse['RefNum']) ? $transResponse['RefNum'] : '*';

            /** @var $transDescription */
            $transDescription = isset($transDetail['Description']) ? $transDetail['Description'] : '*';
            $orderId = isset($transDetail['OrderID']) ? $transDetail['OrderID'] : '*';
            $poNumber = isset($transDetail['PONum']) ? $transDetail['PONum'] : '*';
            $invoiceNumber = isset($transDetail['Invoice']) ? $transDetail['Invoice'] : '*';

            /** @var $orderlineItems */
            $orderlineItems = $transactionDetail['LineItems'];

            $orderLineItemHtml = '<br/><table style="border:1px solid #ccc;" class="table-bordered">';
            $orderLineItemHtml .= '<tr ><td style="border-bottom: 1px solid #ccc;">Line Item</td>
                                    <td style="border-bottom: 1px solid #ccc;">Qty</td>
                                    <td style="border-bottom: 1px solid #ccc;">Price</td>
                                    <td style="border-bottom: 1px solid #ccc;">Tax</td>
                                    <td style="border-bottom: 1px solid #ccc;">Row Total</td>
                                    </tr>';
            $subTotal = 0;
            $taxSubTotal = 0;

            if (count($orderlineItems) > 0) {
                foreach ($orderlineItems as $lineItem) {
                    /** @var $lineItem */
                    $lineItem = (array)$lineItem;

                    $sku = isset($lineItem['SKU']) ? $lineItem['SKU'] : '';
                    $productName = isset($lineItem['ProductName']) ? $lineItem['ProductName'] : '';
                    $unitPrice = isset($lineItem['UnitPrice']) ? (float)$lineItem['UnitPrice'] : 0;
                    $orderedQty = isset($lineItem['Qty']) ? (double)$lineItem['Qty'] : 0;

                    /** @var $taxAmount */
                    $taxAmount = isset($lineItem['TaxAmount']) ? (double)$lineItem['TaxAmount'] : 0;

                    $rowSubTotal = (float)$unitPrice * (float)$orderedQty;
                    $rowTotal = $this->_priceHelper->currencyByStore($rowSubTotal, $store);
                    $subTotal += $rowSubTotal;
                    $taxSubTotal += $taxAmount;

                    $orderLineItemHtml .= '<tr><td>' . $productName . '(' . $sku . ')</td>
                                        <td>' . round($orderedQty, 2) . '</td>
                                        <td>' . $this->_priceHelper->currency($unitPrice, $store) . '</td>
                                        <td>' . $this->_priceHelper->currency($taxAmount, $store) . '</td>
                                        <td>' . $rowTotal . '</td>
                                        </tr>';
                }
            }
            $orderLineItemHtml .= '<tr ><td colspan="5" > </td></tr>';
            $orderLineItemHtml .= '<tr ><td colspan="5" > </td></tr>';
            $orderLineItemHtml .= '<tr ><td colspan="4" style="border-top: 1px solid #ccc; text-align: right"
>Sub Total : &nbsp;</td><td style="border-top: 1px solid #ccc;">' .
                $this->_priceHelper->currencyByStore($subTotal, $store) . '</td></tr>';
            $orderLineItemHtml .= '<tr ><td colspan="4" style="border-top: 1px solid #ccc; text-align: right"
>Tax  : &nbsp;</td><td style="border-top: 1px solid #ccc;">' .
                $this->_priceHelper->currencyByStore($taxSubTotal, $store) . '</td></tr>';
            $orderLineItemHtml .= '<tr ><td colspan="4" style="border-top: 1px solid #ccc; text-align: right"
>Grand Total  : &nbsp;</td><td style="border-top: 1px solid #ccc;">' . $authAmount . '</td></tr></table>
            <br/>';

            if ($customerTransaction) {

                /** @var $templateVars */
                $templateVars = [
                    '{Amount}' => $authAmount,
                    '${Amount}' => $authAmount,
                    '{Tax}' => $taxAmount,
                    '{TaxAmount}' => $taxAmount,
                    '{TransactionType}' => $transactionType,
                    '{TransactionResult}' => $transResult,
                    '{Description}' => $transDescription,
                    '{Date}' => $customerTransaction['DateTime'],
                    '{PaymentMethodType}' => $paymenType,
                    '{Last4Digits}' => $last4Digit,
                    '{AccountHolderName}' => $customerTransaction['AccountHolder'],
                    '{AuthCode}' => $authCode,
                    '{RefNum}' => $transRefNumber,
                    '{InvoiceNumber}' => $invoiceNumber,
                    '{PONumber}' => $poNumber,
                    '{OrderId}' => $orderId,
                    '{CustomerId}' => $customerTransaction['CustomerID'],
                    '{CompanyName}' => $customerTransaction['Source'],
                    '{MerchantName}' => $customerTransaction['Status'],
                    '{ReceiptNote}' => $transDescription,
                    '{Status}' => $customerTransaction['Status'],
                    '{LineItems}' => $orderLineItemHtml
                ];
            }
            /** @var $htmlTemplate */
            $htmlTemplate = str_replace(['$', 'USD', 'Rs'], ['', '', ''], $htmlTemplate);

            /** @var $htmlTemplate */
            $htmlTemplate = str_replace(array_keys($templateVars), array_values($templateVars), $htmlTemplate);

            return $htmlTemplate;
        } else {
            return $customerTransactionResp;
        }
    }

    /**
     * Get Transaction Detail By Reference Id
     *
     * @param mixed $referenceId
     * @return array
     */
    public function getTransactionDetailByReferenceId($referenceId = null)
    {
        $soapResponse = [
            'error' => true,
            'message' => '',
            'response' => []
        ];

        try {
            $storeId = $this->getStoreId();
            /** @var $params */
            $params = [
                'securityToken' => $this->tranApiFactory->create()->getUeSecurityToken($storeId),
                'transactionRefNum' => $referenceId,
            ];
            /** @var $transactionResponse */
            $transactionResponse = $this->tranApiFactory->create()->getClient($storeId)->GetTransactionDetails($params);

            /** Transaction Response fetching */
            if (is_object($transactionResponse->GetTransactionDetailsResult)) {
                $soapResponse['error'] = false;
                $soapResponse['response'] = (array)$transactionResponse->GetTransactionDetailsResult;
                $soapResponse['message'] = __('Success, We found the Transaction Response against Ref #' .
                    $referenceId);
                $this->_ebizchargeLogger->addInfo(__('Success, We found the Transaction Response against Ref #' .
                    $referenceId));
            } else {
                $this->_ebizchargeLogger->addError(__(
                    "Error occured during fetching the Transaction detail against Ref:" . $referenceId
                ));
                $soapResponse['message'] = __(
                    "Error occured during fetching the Transaction detail against Ref:" . $referenceId
                );
            }

            return $soapResponse;

        } catch (SoapFault $soapFault) {
            $this->_ebizchargeLogger->addCritical(__(
                'Soap error occured during fetching transaction detail Soap Fault:' . $soapFault->getMessage()
            ));
            $soapResponse['message'] = __(
                "Soap Error occured during feching the Transaction detail against Ref:" . $referenceId
            );
            return $soapResponse;
        }
    }

    /**
     * Get Payment Method Profile By Id
     *
     * @param mixed $paymentMethodId
     * @param mixed $customerToken
     * @return array
     */
    public function getPaymentMethodProfileById($paymentMethodId = 0, $customerToken = '')
    {
        /** @var  paymentMethodProfile */
        $this->paymentMethodProfile = $this->getSavedPaymentMethodById($paymentMethodId, $customerToken);
        return $this->paymentMethodProfile;
    }

    /**
     * Get Payment Profile Method Id
     *
     * @return mixed|string
     */
    public function getPaymentProfileMethodId()
    {
        return $this->paymentMethodProfile["MethodID"] ?? "";
    }

    /**
     * Get Payment Method Profile Method Type
     *
     * @return mixed|string
     */
    public function getPaymentMethodProfileMethodType()
    {
        return $this->paymentMethodProfile["MethodType"] ?? "";
    }

    /**
     * Get Payment Method Name Json String
     *
     * @return array|string|string[]
     */
    public function getPaymentMethodNameJsonString()
    {
        $paymentMethodName = $this->getPaymentProfileMethodName();
        return str_replace("\"", "'", $paymentMethodName ?? "");
    }

    /**
     * Get Payment Profile Method Name
     *
     * @return mixed|string
     */
    public function getPaymentProfileMethodName()
    {
        return $this->paymentMethodProfile["MethodName"] ?? "";
    }

    /**
     * Get Payment Method Profile Json Name
     *
     * @return mixed|string
     */
    public function getPaymentMethodProfileJsonName()
    {
        $paymentMethodName = $this->paymentMethodProfile["MethodName"] ?? "";
        $paymentMethod = json_decode($paymentMethodName);
        if (is_object($paymentMethod)) {
            $paymentMethodName = $paymentMethod->b ?? $paymentMethodName;
        }
        return $paymentMethodName;
    }

    /**
     * Get Payment Method Profile Json Type
     *
     * @return mixed|string
     */
    public function getPaymentMethodProfileJsonType()
    {
        $paymentMethodName = $this->paymentMethodProfile["MethodName"] ?? "";
        $paymentMethod = json_decode($paymentMethodName);
        if (is_object($paymentMethod)) {
            $paymentMethodName = $paymentMethod->a . "-" . $paymentMethod->b ?? $paymentMethodName;
        }
        return $paymentMethodName;
    }

    /**
     * Get Payment Method Profile Exp Month
     *
     * @return string
     */
    public function getPaymentMethodProfileExpMonth()
    {
        return explode("-", $this->paymentMethodProfile["CardExpiration"])[1] ?? "";
    }

    /**
     * Get Payment Method Profile Exp Year
     *
     * @return string
     */
    public function getPaymentMethodProfileExpYear()
    {
        return explode("-", $this->paymentMethodProfile["CardExpiration"])[0] ?? "";
    }

    /**
     * Get Payment Method Profile Create At
     *
     * @return mixed|string
     */
    public function getPaymentMethodProfileCreatedAt()
    {
        return $this->paymentMethodProfile["Created"] ?? "";
    }

    /**
     * Get Payment Method Profile Modified At
     *
     * @return mixed|string
     */
    public function getPaymentMethodProfileModifiedAt()
    {
        return $this->paymentMethodProfile["Modified"] ?? "";
    }

    /**
     * Get Payment Method Profile Card Number
     *
     * @return mixed|string
     */
    public function getPaymentMethodProfileCardNumber()
    {
        return $this->paymentMethodProfile["CardNumber"] ?? "";
    }

    /**
     * Get Payment Method Profile Avs Street
     *
     * @return mixed|string
     */
    public function getPaymentMethodProfileAvsStreet()
    {
        return $this->paymentMethodProfile["AvsStreet"] ?? "";
    }

    /**
     * Get Payment Method Profile Avs Zip
     *
     * @return mixed|string
     */
    public function getPaymentMethodProfileAvsZip()
    {
        return $this->paymentMethodProfile["AvsZip"] ?? "";
    }

    /**
     * Get Payment Method Profile Account Holder Name
     *
     * @return mixed|string
     */
    public function getPaymentMethodProfileAccountHolderName()
    {
        return $this->paymentMethodProfile["AccountHolderName"] ?? "";
    }

    /**
     * Get Payment Method Profile Secondary Sort
     *
     * @return mixed|string
     */
    public function getPaymentMethodProfileSecondarySort()
    {
        return $this->paymentMethodProfile["SecondarySort"] ?? "";
    }

    /**
     * Get Payment Method Profile Card Type
     *
     * @return mixed|string
     */
    public function getPaymentMethodProfileCardType()
    {
        return $this->paymentMethodProfile["CardType"] ?? "";
    }

    /**
     * Get Payment Method Profile Balance
     *
     * @return mixed|string
     */
    public function getPaymentMethodProfileBalance()
    {
        return $this->paymentMethodProfile["Balance"] ?? "";
    }

    /**
     * Get Payment Method Profile Max Balance
     *
     * @return mixed|string
     */
    public function getPaymentMethodProfileMaxBalance()
    {
        return $this->paymentMethodProfile["MaxBalance"] ?? "";
    }

    /**
     * Get Payment Method Profile Relaod Schedule
     *
     * @return mixed|string
     */
    public function getPaymentMethodProfileReloadSchedule()
    {
        return $this->paymentMethodProfile["ReloadSchedule"] ?? "";
    }

    /**
     * Get Email Receipt
     *
     * @param mixed $selectedRows
     * @return array
     */
    public function getEmailReceipt($selectedRows = [])
    {
        $emailResponse[] = [
            'error' => true,
            'message' => 'Error occured during sending email receipt ',
            'refid' => 0,
            'response' => []
        ];

        try {
            if (count($selectedRows) > 0) {
                $counter = 0;
                foreach ($selectedRows as $selectedRow) {
                    $entityId = $selectedRow;
                    $paymentHistoryModel = $this->_paymentHistoryModel->load($entityId);
                    $refId = $paymentHistoryModel->getPaymentRefNumber();
                    $customerName = $paymentHistoryModel->getCustomerName();
                    $customerEmail = $paymentHistoryModel->getCustomerEmail();

                    /** Customer Email */
                    if ($customerEmail == 'N/A' || $refId == '' || $customerName == 'N/A') {
                        continue;
                    }

                    if ($customerEmail) {
                        $receiptEmailParams = [
                            'tid' => $refId,
                            'email' => $customerEmail,
                        ];

                        /** sending customer receipt Email **/
                        $emailSentResp = $this->sendCustomerReceipt($receiptEmailParams);

                        if ($emailSentResp['error'] == false) {
                            $emailResponse[$counter] = [
                                'error' => false,
                                'refid' => $refId,
                                'message' => __('Error occured during sending Email  to ' . $customerEmail),
                                'response' => $emailSentResp['message']
                            ];
                        } else {
                            $emailResponse[$counter] = [
                                'error' => true,
                                'refid' => $refId,
                                'message' => __('Error occured during sending Email  to ' . $customerEmail),
                                'response' => $emailSentResp['message']
                            ];
                        }
                    } else {
                        $emailResponse[$counter] = [
                            'error' => true,
                            'refid' => $refId,
                            'message' => __('Error occured during sending Email  to ' . $customerEmail),
                            'response' => __('Customer Email doest not exists')
                        ];
                    }
                }
                $counter++;
            }
        } catch (SoapFault $soapFault) {
            $this->_ebizchargeLogger->addError(__("Exception occured during sending email " .
                $soapFault->getMessage()));
            $emailResponse[] = ['error' => true,
                'refid' => 0,
                'message' => 'Error occured during sending email receipt ' . $soapFault->getMessage(),
                'response' => []];
        }

        return $emailResponse;
    }

    /**
     * Get Customer Name
     *
     * @param Customer $customer
     * @return bool|string
     */
    public function getCustomerName($customer = null)
    {
        if (!$customer) {
            return false;
        }

        /** @var $customerName */
        $customerName = '';
        $customerName .= trim($customer->getPrefix()) . ' ';
        $customerName .= trim($customer->getFirstname()) . ' ';
        $customerName .= trim($customer->getMiddlename()) . ' ';
        $customerName .= trim($customer->getLastname()) . ' ';
        $customerName .= trim($customer->getSuffix());

        return $customerName;
    }

    /**
     * Send Customer Receipt
     *
     * @param array $receiptParams
     * @return array
     */
    public function sendCustomerReceipt($receiptParams = [])
    {
        $emailReceiptsResp = [
            'error' => true,
            'message' => __(''),
            'response' => [
                'StatusCode' => 0
            ]
        ];

        try {
            $emailReceiptsCollectionResp = $this->getEbizReceiptEmailCollection();
            $emailReceiptsCollection = [];
            $storeId = $this->_ebizConfigFactory->create()->getStoreId() ?? "0";

            if ($emailReceiptsCollectionResp['error'] == false) {
                $emailReceiptsCollection = $emailReceiptsCollectionResp['response'];
                $emailReceipts = isset($emailReceiptsCollection['Receipt']) ? $emailReceiptsCollection['Receipt'] : [];
                $emailReceiptRefNo = 0;

                if (count($emailReceipts) > 0) {
                    foreach ($emailReceipts as $emailReceipt) {
                        if (strpos($emailReceipt->Name, 'Transaction') !== -1) {
                            $emailReceiptRefNo = $emailReceipt->ReceiptRefNum;
                        }
                    }
                }
                $paymentEntityId = isset($receiptParams['entity_id']) ? $receiptParams['entity_id'] : 0;
                $paymentHistoryModel = $this->_paymentHistoryModel->load($paymentEntityId);

                $params = [
                    'securityToken' => $this->tranApiFactory->create()->getUeSecurityToken($storeId),
                    'transactionRefNum' => $paymentHistoryModel->getPaymentRefNumber(),
                    'receiptRefNum' => $emailReceiptRefNo,
                    'emailAddress' => $paymentHistoryModel->getCustomerEmail(),
                ];

                /** @var $emailReceipt */
                $emailReceipt = $this->tranApiFactory->create()->getClient($storeId)->EmailReceipt($params);
                $emailReceiptResult = $emailReceipt->EmailReceiptResult;

                /** Email Receipt Results */
                if ($emailReceiptResult->Status == 'Success') {
                    $emailReceiptsResp = [
                        'error' => false,
                        'message' => __('Success, the Email Receipts has been sent to the Customer'),
                        'response' => [
                            'StatusCode' => $emailReceiptResult->StatusCode
                        ]
                    ];

                    $this->_ebizchargeLogger->addInfo(__($emailReceiptsResp['message']));
                } else {
                    $emailReceiptsResp['message'] = __('Error occured during sending Email Receipt');
                    $this->_ebizchargeLogger->addInfo(__($emailReceiptsResp['message']));
                }
            } else {
                $emailReceiptsResp['message'] = __(
                    'Error occured during sending Email Receipt as no Email Receipt found'
                );
                $this->_ebizchargeLogger->addInfo(__($emailReceiptsResp['message']));
            }

            return $emailReceiptsResp;
        } catch (SoapFault $soapFault) {
            $this->_ebizchargeLogger->addCritical(__('Exception occured during sending Receipt Error: ' .
                $soapFault->getMessage()));
            $emailReceiptsResp['message'] = __('Exception occured during sending Receipt Error: ' .
                $soapFault->getMessage());

            return $emailReceiptsResp;
        }
    }

    /**
     * Get Ebiz Receipt Email Collection
     *
     * @param string $emailType
     * @return array
     */
    public function getEbizReceiptEmailCollection($emailType = 'email')
    {
        /** @var  $ebizEmailCollection */
        $ebizEmailCollectionResp = [
            'error' => true,
            'message' => __(''),
            'response' => []

        ];
        /** @var $emailCollection */
        $emailCollection = [];

        try {
            $storeId = $this->_ebizConfigFactory->create()->getStoreId() ?? "0";
            $ebizEmailListParams = [
                'securityToken' => $this->tranApiFactory->create()
                    ->getUeSecurityToken($storeId),
                'receiptType' => $emailType
            ];

            $ebizReceiptEmailsCollectionResp = $this->tranApiFactory
                ->create()
                ->getClient($storeId)
                ->GetReceiptsList($ebizEmailListParams);

            if ($ebizReceiptEmailsCollectionResp->GetReceiptsListResult) {
                /** @var  $emailCollection */
                $emailCollection = (array)$ebizReceiptEmailsCollectionResp->GetReceiptsListResult;

                if (count($emailCollection) > 0) {
                    $this->_ebizchargeLogger->addInfo(__(
                        'Success, found Email Receipts from Ebizcharge Gateway'
                    ));
                    /** @var $ebizEmailCollectionResp */
                    $ebizEmailCollectionResp = [
                        'error' => false,
                        'message' => __('Success found Email Receipts'),
                        'response' => $emailCollection

                    ];
                } else {
                    $ebizEmailCollection['message'] = __(
                        'Error, occured during fetching Email Receipts from Ebizcharge Gateway'
                    );
                }
            }
            return $ebizEmailCollectionResp;
        } catch (SoapFault $soapFault) {
            $this->_ebizchargeLogger->addCritical(__(
                "Exception occured during fetching Email Receipts Error: " . $soapFault->getMessage()
            ));
            $ebizEmailCollection['message'] = __(
                'Exception, occured during fetching Email Receipts from Ebizcharge Gateway Error: ' .
                $soapFault->getMessage()
            );
            return $ebizEmailCollectionResp;
        }
    }

    /**
     * Get Ebiz Customer Bank Accounts
     *
     * @param null|mixed $customerId
     * @return array
     */
    public function getEbizCustomerBankAccounts($customerId = null)
    {
        if (!$customerId) {
            return [];
        }
        $paymentMethods = $this->getEbizCustomerPaymentMethods($customerId);
        $ebizBankAccounts = [];

        if (count($paymentMethods) > 0) {
            foreach ($paymentMethods as $paymentMethod) {
                if ($paymentMethod->MethodType === SoapApiModelInterface::EBIZCHARGE_PAYMENT_ACCOUNT_TYPE_ACH) {
                    $ebizBankAccounts[] = $paymentMethod;
                }
            }
        }
        return $ebizBankAccounts;
    }

    /**
     * Get EbizCharge Customer Payment Methods
     *
     * @param null|mixed $customerId
     * @return array|null
     */
    public function getEbizCustomerPaymentMethods($customerId = null)
    {
        if (!$customerId) {
            return [];
        }
        $customer = $this->load($customerId);
        $customerToken = $customer->getEcCustToken();
        if (empty($customerToken)) {
            $this->getResource()->saveEbizchargeFields($customer);
            $customer = $this->load($customerId);
            $customerToken = $customer->getEcCustToken();
        }
        return $this->tranApiFactory->create()->getCustomerPaymentMethods($customer->getEcCustToken());
    }

    /**
     * Get Ebiz Customer Credit Cards
     *
     * @param null|mixed $customerId
     * @return array
     */
    public function getEbizCustomerCreditCards($customerId = null)
    {
        if (!$customerId) {
            return [];
        }
        $paymentMethods = $this->getEbizCustomerPaymentMethods($customerId);
        $ebizCreditCards = [];

        if (count($paymentMethods) > 0) {
            foreach ($paymentMethods as $paymentMethod) {
                if ($paymentMethod->MethodType === SoapApiModelInterface::EBIZCHARGE_PAYMENT_ACCOUNT_TYPE_CREDIT_CARD) {
                    $ebizCreditCards[] = $paymentMethod;
                }
            }
        }
        return $ebizCreditCards;
    }

    /**
     * Get Customer Address List
     *
     * @param mixed $customerId
     * @param string $selectedAddress
     * @return string
     */
    public function getCustomerAddressList($customerId, $selectedAddress = '')
    {
        $addresses = "<option value=''>No customer address found</option>";

        if (!empty($customerId)) {
            $customer = $this->load($customerId);
            try {
                $html = "";
                $regionName = '*';
                $customerAddresses = $customer->getAddresses();

                if (count($customerAddresses)) {
                    foreach ($customerAddresses as $customerAddress) {
                        $regionId = $customerAddress->getRegionId();
                        $regionName = $customerAddress->getRegion() ?? $this->getRegionById($regionId);

                        $address = join(' - ', array_filter(
                            [
                                $customerAddress->getFirstname(),
                                $customerAddress->getLastname(),
                                $customerAddress->getStreet()[0],
                                $customerAddress->getPostcode(),
                                $regionName,
                                $customerAddress->getCountryId()
                            ]
                        ));
                        $addressId = $customerAddress->getEntityId();
                        $selected = ($addressId === $selectedAddress) ? 'selected' : '';

                        /** HTML with address options */
                        $html .= "<option value='" . $addressId . "' $selected >" . $address . "</option>";
                    }
                }

                return $html;
            } catch (Exception $ex) {
                $this->_ebizchargeLogger->addCritical(__("Exception occured during fetching results : " .
                    __METHOD__ . $ex->getMessage()));

                return $html;
            }
        } else {
            $addresses = "<option value=''>Invalid Customer ID</option>";
            $this->_ebizchargeLogger->addError(__("Error occured during fetching Address "));
        }

        return $addresses;
    }

    /**
     * Get Region By Id
     *
     * @param mixed $regionId
     * @return array|mixed|null
     */
    public function getRegionById($regionId)
    {
        if (!empty($regionId)) {
            $region = $this->_regionFactory->create()->load($regionId);
            return $region->getData();
        }
        return null;
    }

    /**
     * Get the customer address
     *
     * @param mixed $addressId
     * @return array
     */
    public function getCustomerAddress($addressId): array
    {
        try {
            $billingAddress = $this->_addressRepository->getById($addressId);

            if (!empty($billingAddress)) {
                return [
                    'FirstName' => $billingAddress->getFirstname(),
                    'LastName' => $billingAddress->getLastname(),
                    'CompanyName' => $billingAddress->getCompany(),
                    'Address1' => isset($billingAddress->getStreet()[0]) ? $billingAddress->getStreet()[0] : "",
                    'Address2' => isset($billingAddress->getStreet()[1]) ? $billingAddress->getStreet()[1] : "",
                    'Street' => isset($billingAddress->getStreet()[0]) ? $billingAddress->getStreet()[0] : "",
                    'City' => $billingAddress->getCity(),
                    'State' => $billingAddress->getRegion()->getRegion(),
                    'ZipCode' => $billingAddress->getPostcode(),
                    'Country' => $this->getCountryName($billingAddress->getCountryId()) ?? "US"
                ];
            }
        } catch (Exception $e) {
            /** logging exception to the logger file */
            $this->_ebizchargeLogger->addCritical(__("Error occurred: " . $e->getMessage()));
        }

        return [];
    }

    /**
     * @param $addressId
     * @return false
     */
    public function loadCustomerAddressById($addressId = null)
    {
        $customerAddress = $this->_addressRepository->getById($addressId);
        return $customerAddress ?? false;
    }

    /**
     * Prepare Web Form URL
     *
     * @param mixed $cartId
     * @param mixed $approvedUrl
     * @param mixed $declinedUrl
     * @param mixed $errorUrl
     * @param mixed $storeId
     * @return array
     * @throws NoSuchEntityException
     */
    public function prepareEbizWebFormUrl($cartId, $approvedUrl, $declinedUrl, $errorUrl, $storeId)
    {
        return $this->tranApiFactory->create()->prepareEbizWebFormUrl($cartId, $approvedUrl, $declinedUrl, $errorUrl, $storeId);
    }

    /**
     * Format Payment Method Name
     *
     * @param string $methodName
     * @return mixed|string
     */
    public function formatPaymentMethodName(string $methodName = ''): mixed
    {
        $methodNameData = $this->_ebizConfigFactory->create()->validateJsonString($methodName);
        if ($methodNameData !== false) {
            if (isset($methodNameData['a']) && isset($methodNameData['b'])) {
                $methodName = $methodNameData['a'] . '-' . $methodNameData['b'];
            } elseif (isset($methodNameData['a'])) {
                $methodName = $methodNameData['a'];
            }
        }

        return $methodName;
    }

    /**
     * Create Surcharge Total Object
     *
     * @param int|string|null $amount
     * @param float|string|null $percentage
     * @return array|DataObject
     */
    public function createSurchargeTotalObject($amount = 0, $percentage = 0)
    {
        $total = [];
        $storeId = $this->_ebizConfigFactory->create()->getStoreId();
        if ($percentage > 0) {
            /** Surcharge settings for getting surcharge caption */
            $surchargeSettings = $this->getSurchargeSettings($storeId);
            $surchargeCaption = $surchargeSettings['surchargeCaption'] ?? '';
            $label = $surchargeCaption . ' (' . $percentage . '%)';

            $total = new DataObject([
                'code' => 'ebizcharge_surcharge',
                'value' => $amount,
                'label' => $label,
                'title' => $label
            ]);
        }


        return $total;
    }

    /**
     * Get Surcharge Settings
     *
     * @param int|string $storeId
     * @return array
     */
    public function getSurchargeSettings($storeId = 0)
    {
        return $this->tranApiFactory->create()->getSurchargeSettings($storeId);
    }

    /**
     * Is 3D Secure Enabled against Security Id on Ebiz Portal
     *
     * @param mixed $storeId
     * @return bool
     * @throws NoSuchEntityException
     */
    public function is3DSecureEnabled($storeId = '')
    {
        $storeId = $storeId ?: $this->_ebizConfigFactory->create()->getStoreId();
        $ebiz3DSecure = $this->get3DSecureSettings($storeId);
        return (bool)$ebiz3DSecure[SoapApiModelInterface::EBIZ_IS_3DSECURE_ENABLED];
    }

    /**
     * Get 3D Secure Settings
     *
     * @param int|string $storeId
     * @return array
     */
    public function get3DSecureSettings($storeId = 0)
    {
        return $this->tranApiFactory->create()->get3DSecureSettings($storeId);
    }

    /**
     * Check is Surcharge Enabled on merchant portal
     *
     * @param mixed $storeId
     * @return bool
     */
    public function isSurchargeEnabled(mixed $storeId = 0): bool
    {
        $surchargeEnabled = false;
        $surchargeSettings = $this->getSurchargeSettings($storeId);
        $ebizSurchargeEnabled = (bool)$surchargeSettings[SurchargeInterface::EBIZ_SURCHARGE_ENABLED];
        if ($ebizSurchargeEnabled) {
            //$surchargeEnabled = $this->_scopeConfig->isSetFlag(
            //   ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_SURCHARGE_ENABLED,
            //    ScopeInterface::SCOPE_STORE,
            //    $storeId
            //);
            $surchargeEnabled = true;
        }

        return $surchargeEnabled;
    }

    /**
     * Get PreAuthTransaction Enabled
     *
     * @param int $storeCode
     * @return bool|mixed
     */
    public function getPreAuthTransactionEnabled($storeCode = 0)
    {
        $scope = $storeCode ?? 0;
        return $this->isVerifyCreditCardBeforeSave($scope);
    }

    /**
     * Is Guest Customer
     *
     * @return bool
     */
    public function isGuestCustomer()
    {
        $isGuest = true;
        if ($this->_customerSession->isLoggedIn()) {
            $isGuest = false;
        }
        return $isGuest;
    }

    /**
     * @param string $customerId
     * @param string $methodType
     * @param bool $isRedirect
     * @param string $redirectUrl
     * @param int $storeId
     * @return string
     */
    public function prepareWebHostedPaymentMethodFormUrl(
        string $customerId = "",
        string $methodType = "CC",
        bool   $isRedirect = false,
        string $redirectUrl = "",
        int    $storeId = 0
    )
    {
        $webFormUrl = "";
        $storeId = $storeId !== 0 ? $storeId : (int)$this->_ebizConfigFactory->create()->getStore()->getId();
        // $redirectUrl = "";

        try {
            $webFormResponse = $this->tranApiFactory->create()->createEbizWebHostedPaymentMethodFormUrl(
                $customerId,
                $methodType,
                $isRedirect,
                $redirectUrl,
                $storeId
            );
            $webFormUrl = isset($webFormResponse["response"]["ebiz_hosted_pro_url"]) ? $webFormResponse["response"]["ebiz_hosted_pro_url"] : "https://ebizcharge.com";
            //  var_dump("<pre>", $webFormUrl, $webFormResponse);

        } catch (Exception $e) {
            $this->_ebizchargeLogger->addCritical(__("Error preparing payment method URL: " . $e->getMessage()));
            throw new LocalizedException(__("Error preparing payment method URL: " . $e->getMessage()));

        }
        return $webFormUrl;
    }

    /**
     * @param $customerId
     * @param $addressType
     * @return array
     */
    public function getCustomerAddressAsArray($customerId = null, $addressType = "billing")
    {
        $customerAddress = [
            "Country" => "US",
            "State" => "NY"
        ];
        try {
            $customer = $this->load($customerId);
            $address = $customer->getAddresses();
            $defaultBillingAddress = $customer->getDefaultBillingAddress();
            $defaultShippingAddress = $customer->getDefaultShippingAddress();

            if ($defaultBillingAddress || $defaultShippingAddress) {
                if ($defaultBillingAddress && $addressType === "billing") {
                    $address = $defaultBillingAddress;
                }
                if ($defaultBillingAddress && $addressType === "shipping") {
                    $address = $defaultShippingAddress;
                }
            } else {
                $addresses = $customer->getAddresses();
                if (!empty($addresses)) {
                    $address = $addresses[0];
                }
            }

            if (!empty($address)) {
                $postAddress = $address->getStreet();
                $countryCode = $address->getCountryId();
                $regionId = $address->getRegionId();
                $cRegion = $this->_ebizConfigFactory->create()->getStateByRegionId($countryCode, $regionId);
                $regionCode = isset($cRegion["code"]) ? $cRegion["code"] : "";

                $customerAddress = [
                    'id' => $address->getId(),
                    'FirstName' => $address->getFirstname(),
                    'LastName' => $address->getLastname(),
                    'Address1' => isset($postAddress[0]) ? $postAddress[0] : "",
                    'Address2' => isset($postAddress[1]) ? $postAddress[1] : "",
                    'City' => $address->getCity(),
                    'ZipCode' => $address->getPostcode(),
                    'Telephone' => $address->getTelephone(),
                    'country_id' => $address->getCountryId(),
                    'Country' => $address->getCountry(),
                    'State' => $regionCode,
                    'region_id' => $address->getRegionId()
                ];
            }
        } catch (NoSuchEntityException $e) {
            return $customerAddress;
        }
        return $customerAddress;
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function authenticateGatewayPaymentResponse(RequestInterface $httpRequest)
    {

        $hostedWebFormRespParams = $httpRequest->getParams();
        $htmlOutRenderer = "";
        $isPaymentAuthenticated = "false";
        $newHostedWebformUrl = "";
        /**
         * set Hosted Webform Payment Response
         */
        $this->setHostedFormResponse($hostedWebFormRespParams);
        $isPaymentIsTokenizedOnly = $this->isCardTokenizedOnly();

        /**
         * find if it is a registered User
         */
        $isValidGatewayResponse = $this->validateGatewayResponse($httpRequest);
        $customerId = isset($hostedWebFormRespParams["customer_id"]) ? $hostedWebFormRespParams["customer_id"] : 0;

        $customer = $this->_customerFactory->create()->load($customerId);
        if ($customer && $customer->getId()) {
            /**
             * if there is registered user force customer to logged in iframe
             */
            $this->setCustomerLogged($customerId);
        }

        /**
         * Transaction Result Code
         */
        $transactionResultCode = isset($hostedWebFormRespParams["TranResultCode"]) ? $hostedWebFormRespParams["TranResultCode"] : "E";

        // if ($isPaymentIsTokenizedOnly) {
        if (isset($hostedWebFormRespParams["CustToken"]) && !empty($hostedWebFormRespParams["CustToken"])) {
            if (isset($hostedWebFormRespParams["PmToken"]) && !empty($hostedWebFormRespParams["PmToken"])) {
                $transactionResultCode = "A";
            }
        }
        // }

        if ($transactionResultCode === "A") {
            $isPaymentAuthenticated = "true";
            $htmlOutRenderer .= "<br/><h2 style='margin:auto;text-align: center; color:#666666; font-size:14px;'>Placing order please wait...</h2>";

        } else {
            //$newHostedWebformUrl = $this->prepareHostedWebformUrl($hostedWebFormRespParams);
            $htmlOutRenderer .= "<br/><h2 style='margin:auto;text-align: center; color:#666666; font-size:14px;'>Redirecting please wait...</h2>";
        }
        $htmlOutRenderer .= "<script>";
        $htmlOutRenderer .= "
                     const isPaymentAuthenticated = " . $isPaymentAuthenticated . ";
                     const iframeWebformCheckoutUrl = '" . $newHostedWebformUrl . "';
                     const parentForm = parent.document.getElementById(\"ebizcharge-payment-webform\");
                     let hostedPaymentResponseTxt = parent.document.getElementById(\"ebizcharge_ebizcharge_hosted_payment_response\");
                            if(hostedPaymentResponseTxt !== null){
                                hostedPaymentResponseTxt.value = '" . json_encode($hostedWebFormRespParams) . "';
                                }
                     const placeOrderBtn = parent.document.getElementById('place-order');
                     if(isPaymentAuthenticated === true){
                                if (placeOrderBtn) {
                                    placeOrderBtn . click();
                                }
                         }else{
                              if (parentForm) {
                                    const iframeWebformCheckoutUrl = parent.window.checkoutConfig.payment.ebizcharge.checkoutWebHostedFormUrl;
                                    parentForm.src = iframeWebformCheckoutUrl;
                              }
                         }
                     ";
        $htmlOutRenderer .= "</script>";

        return $htmlOutRenderer;
    }

    /**
     * @param $hostedWebFormResponseParams
     * @return void
     */
    public function setHostedFormResponse($hostedWebFormResponseParams = [])
    {
        $this->unsetHostedFormResponse();
        if ($this->_checkoutSession->getSessionId()) {
            $hostedWebFormResponseJson = json_encode($hostedWebFormResponseParams);
            $this->_checkoutSession->setHostedFormResponse($hostedWebFormResponseJson);
        }
    }

    /**
     * @return void
     */
    public function unsetHostedFormResponse()
    {
        if ($this->_checkoutSession->getSessionId()) {
            $this->_checkoutSession->unsHostedFormResponse();
        }
    }

    /**
     * @param $storeId
     * @return string
     */
    public function isCardTokenizedOnly($storeId = 0)
    {
        $storeId = $storeId ?? $this->getStoreId();
        return $this->_ebizConfigFactory->create()->IsCardsTokenizeOnly($storeId);
    }

    public function validateGatewayResponse($httpRequest = null)
    {
        $validatedResponse = $this->_ebizConfigFactory->create()->validateFormKey($httpRequest);
        return $validatedResponse;

    }

    /**
     * @param $customerId
     * @return CustomerInterfaceAlias|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function setCustomerLogged($customerId = null)
    {
        $customer = null;
        if ($customerId) {
            $customer = $this->_customerRepository->getById($customerId);
            $this->_customerSession->setCustomerDataAsLoggedIn($customer);
        }
        return $customer;
    }

    /**
     * @param $hostedWebFormRespParams
     * @return array|string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function prepareHostedWebformUrl($hostedWebFormRespParams = [])
    {
        $webformUrl = "";
        $storeId = $this->_ebizConfigFactory->create()->getStoreId();
        $quoteId = isset($hostedWebFormRespParams["quote_id"]) ? $hostedWebFormRespParams["quote_id"] : "";

        if ($quoteId) {
            $webformUrl = $this->_orderFactory->create()->renderCheckoutWebHostedProFormUrl($storeId, $quoteId);
        }
        return $webformUrl;
    }

    /**
     * @param $guestCustomerId
     * @return int|mixed|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function saveGuestCustomerToEbizcharge($guestCustomerId = null)
    {
        $ebizCustomerId = 0;
        try {
            $quote = $this->_checkoutSession->getQuote();
            $storeId = $quote->getStoreId() ?? $this->_ebizConfigFactory->create()->getStoreId() ?? "0";
            $guestCustomerParams = $this->prepareGuestCustomerParams($guestCustomerId);
            $ebizCustomerId = isset($guestCustomerParams["CustomerId"]) ? $guestCustomerParams["CustomerId"] : $guestCustomerId;
            /** @var  $soapClient */
            $soapClient = $this->tranApiFactory->create()->getClient($storeId);

            /** @var $addGuestCustomerResultResp */
            $addGuestCustomerResultResp = $soapClient->AddCustomer($guestCustomerParams);
            if (is_object($addGuestCustomerResultResp)) {
                /** Add Guest Customer Results */
                $customerResult = (array)($addGuestCustomerResultResp->AddCustomerResult ?? []);
                $ebizCustomberID = $customerResult["CustomerId"] ?? "";
            }
            $quote->setEcCustId($ebizCustomberID)->save();

        } catch (SoapFault $soapFault) {
            $this->_ebizchargeLogger->addCritical(__("Exception occurred during adding Customer at Ebicharge.
            Error:" . $soapFault->getMessage())
            );
        }
        return $ebizCustomerId;

    }

    /**
     * Prepare Guest Customer Params
     *
     * @param mixed $guestCustomerId
     * @return array
     * @throws NoSuchEntityException
     */
    public function prepareGuestCustomerParams(mixed $guestCustomerId = null)
    {
        $customerParams = [];
        try {
            $quoteId = $guestCustomerId;
            $tranApiFactory = $this->tranApiFactory->create();
            $quote = $this->_quoteFactory->create()->load($quoteId);

            if ($quote->getId()) {
                $store = $quote->getStore() ?? $this->getStore();
                $storeId = $quote->getStoreId() ?? $store->getId();
                $guestUserEmail = $this->_checkoutSession->getQuote()->getCustomerEmail() ??
                    $this->_checkoutSession->getQuote()->getBillingAddress()->getEmail();

                $quoteBillingAddress = $quote->getBillingAddress();
                $quoteShippingAddress = $quote->getShippingAddress();
                $softwareId = $quote->getEcSoftWareId() ?? $tranApiFactory->getSoftwareId();
                $divisionId = $quote->getEcDivisionId() ?? $tranApiFactory->getDivisionId();
                $customerNotes = "Guest User at quote.";
                $website = $store->getBaseUrl() ?? "";
                $ecCustomerId = $guestCustomerId;


                /** @var $customerParams */
                $customerParams = [
                    'securityToken' => $tranApiFactory->getUeSecurityToken($storeId),
                    'customer' => [
                        'CustomerId' => $ecCustomerId,
                        'FirstName' => trim((string)($quoteBillingAddress->getFirstname() ?? "")),
                        'LastName' => trim((string)($quoteBillingAddress->getLastname() ?? "")),
                        'CompanyName' => trim((string)($quoteBillingAddress->getCompany() ?? "")),
                        'Phone' => trim((string)($quoteBillingAddress->getTelephone() ?? "")),
                        'CellPhone' => trim((string)($quoteBillingAddress->getTelephone() ?? "")),
                        'Fax' => trim((string)($quoteBillingAddress->getFax() ?? "")),
                        'SoftwareId' => (string)$softwareId,
                        'DivisionId' => (string)$divisionId,
                        'Email' => trim((string)$guestUserEmail),
                        'WebSite' => $website ? trim((string)($website ?? "")) : "",
                        'CustomerCustomFields' => [],
                        'CustomerNotes' => $customerNotes
                    ]
                ];

                if ($quoteBillingAddress->getId()) {
                    $defaultBillingCountryId = $quoteBillingAddress->getCountryId() ?
                        $quoteBillingAddress->getCountryId() : "US";
                    $billingStreet = $quoteBillingAddress->getStreet();
                    $billingCity = $quoteBillingAddress->getCity() ?? "";

                    $customerParams['customer']['BillingAddress'] = [
                        'FirstName' => trim($quoteBillingAddress->getFirstname() ?
                            $quoteBillingAddress->getFirstname() : ''),
                        'LastName' => trim($quoteBillingAddress->getLastname() ?
                            $quoteBillingAddress->getLastname() : ''),
                        'CompanyName' => trim($quoteBillingAddress->getCompany() ?
                            $quoteBillingAddress->getCompany() : ''),
                        'Address1' => isset($billingStreet[0]) ? $billingStreet[0] : '',
                        'Address2' => isset($billingStreet[1]) ? $billingStreet[1] : '',
                        'City' => trim($billingCity),
                        'State' => trim($quoteBillingAddress->getRegion() ?
                            $quoteBillingAddress->getRegion() : ''),
                        'ZipCode' => trim($quoteBillingAddress->getPostcode() ?
                            $quoteBillingAddress->getPostcode() : ''),
                        'Country' => $this->getCountryName($defaultBillingCountryId),
                        'IsDefault' => true
                    ];
                }
                if ($quoteShippingAddress->getId()) {
                    $defaultShippingCountryId = $quoteShippingAddress->getCountryId() ?
                        $quoteShippingAddress->getCountryId() : "USA";
                    $shippingStreet = $quoteShippingAddress->getStreet();

                    $customerParams['customer']['ShippingAddress'] = [
                        'FirstName' => trim($quoteShippingAddress->getFirstname() ?
                            $quoteShippingAddress->getFirstname() : ''),
                        'LastName' => trim($quoteShippingAddress->getLastname() ?
                            $quoteShippingAddress->getLastname() : ''),
                        'CompanyName' => trim($quoteShippingAddress->getCompany() ?
                            $quoteShippingAddress->getCompany() : ''),
                        'Address1' => isset($shippingStreet[0]) ? $shippingStreet[0] : '',
                        'Address2' => isset($shippingStreet[1]) ? $shippingStreet[1] : '',
                        'City' => trim($quoteShippingAddress->getCity() ?
                            $quoteShippingAddress->getCity() : ''),
                        'State' => trim($quoteShippingAddress->getRegion() ?
                            $quoteShippingAddress->getRegion() : ''),
                        'ZipCode' => trim($quoteShippingAddress->getPostcode() ?
                            $quoteShippingAddress->getPostcode() : ''),
                        'Country' => $this->getCountryName($defaultShippingCountryId),
                        'IsDefault' => true
                    ];
                }
            }
        } catch (Exception $ex) {
            $this->_ebizchargeLogger->addCritical(__("Exception during prepare customer params." .
                $ex->getMessage())
            );
        }
        return $customerParams;
    }


}

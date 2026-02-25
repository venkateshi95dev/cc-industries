<?php

namespace Silk\Core\Helper;

use Magento\Framework\Escaper;
class Context extends \Magento\Framework\App\Helper\Context {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @var \Magento\AdminNotification\Model\InboxFactory
     */
    protected $adminNotificationInboxFactory;

    /**
     * //@var \Magento\Email\Model\TemplateFactory
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $emailTemplateFactory;

    /**
     * //@var \Magento\Framework\TranslateInterface
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $translateInterface;

    /**
     * @var \Magento\Framework\Session\GenericFactory
     */
    protected $genericFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $catalogResourceModelProductCollectionFactory;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $directoryCountryFactory;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $directoryRegionFactory;

    /**
     * @var \Magento\Checkout\Model\CartFactory
     */
    protected $checkoutCartFactory;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteQuoteFactory;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    protected $quoteResourceModelQuoteCollectionFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Magento\Framework\Filesystem\Io\FileFactory
     */
    protected $ioFileFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceConfig;

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $design;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $storeSystemStore;

    /**
     * @var \Magento\Framework\App\ObjectManagerInterface
     */
    protected $objectManager;
    protected $escaper;
    /**
     * @param \Magento\Framework\Url\EncoderInterface                        $urlEncoder
     * @param \Magento\Framework\Url\DecoderInterface                        $urlDecoder
     * @param \Psr\Log\LoggerInterface                                       $logger
     * @param \Magento\Framework\Module\Manager                              $moduleManager
     * @param \Magento\Framework\App\RequestInterface                        $httpRequest
     * @param \Magento\Framework\Cache\ConfigInterface                       $cacheConfig
     * @param \Magento\Framework\Event\ManagerInterface                      $eventManager
     * @param \Magento\Framework\UrlInterface                                $urlBuilder
     * @param \Magento\Framework\HTTP\Header                                 $httpHeader
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress           $remoteAddress
     * @param \Magento\Framework\App\Config\ScopeConfigInterface             $scopeConfig
     * @param \Magento\AdminNotification\Model\InboxFactory                  $adminNotificationInboxFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogResourceModelProductCollectionFactory
     * @param \Magento\Checkout\Model\CartFactory                            $checkoutCartFactory
     * @param \Magento\Checkout\Model\Session                                $checkoutSession
     * @param \Magento\Config\Model\ResourceModel\Config                     $resourceConfig
     * @param \Magento\Customer\Model\CustomerFactory                        $customerCustomerFactory
     * @param \Magento\Customer\Model\SessionFactory                         $customerSessionFactory
     * @param \Magento\Directory\Helper\Data                                 $directoryHelper
     * @param \Magento\Directory\Model\CountryFactory                        $directoryCountryFactory
     * @param \Magento\Directory\Model\RegionFactory                         $directoryRegionFactory
     * @param \Magento\Framework\App\CacheInterface                          $cache
     * @param \Magento\Framework\App\Filesystem\DirectoryList                $directoryList
     * @param \Magento\Framework\App\Request\Http                            $request
     * @param \Magento\Framework\DataObjectFactory                           $dataObjectFactory
     * @param \Magento\Framework\Filesystem\Io\FileFactory                   $ioFileFactory
     * @param \Magento\Framework\Locale\CurrencyInterface                    $localeCurrency
     * @param \Magento\Framework\Locale\ResolverInterface                    $localeResolver
     * @param \Magento\Framework\Mail\Template\TransportBuilder              $emailTemplateFactory
     * @param \Magento\Framework\Registry                                    $registry
     * @param \Magento\Framework\Session\GenericFactory                      $genericFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface           $timezone
     * @param \Magento\Framework\Translate\Inline\StateInterface             $translateInterface
     * @param \Magento\Framework\View\DesignInterface                        $design
     * @param \Magento\Quote\Model\QuoteFactory                              $quoteQuoteFactory
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory     $quoteResourceModelQuoteCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface                     $storeManager
     * @param \Magento\Store\Model\System\Store                              $storeSystemStore
     * @param \Magento\Framework\ObjectManagerInterface                      $objectManager
     */
    public function __construct(
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\App\RequestInterface $httpRequest,
        \Magento\Framework\Cache\ConfigInterface $cacheConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\HTTP\Header $httpHeader,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\AdminNotification\Model\InboxFactory $adminNotificationInboxFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogResourceModelProductCollectionFactory,
        \Magento\Checkout\Model\CartFactory $checkoutCartFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Directory\Model\CountryFactory $directoryCountryFactory,
        \Magento\Directory\Model\RegionFactory $directoryRegionFactory,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\Filesystem\Io\FileFactory $ioFileFactory,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Mail\Template\TransportBuilder $emailTemplateFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Session\GenericFactory $genericFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Translate\Inline\StateInterface $translateInterface,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Quote\Model\QuoteFactory $quoteQuoteFactory,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteResourceModelQuoteCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\System\Store $storeSystemStore,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Escaper $escaper
    ) {
        $this->_localeResolver                              = $localeResolver;
        $this->adminNotificationInboxFactory                = $adminNotificationInboxFactory;
        $this->cache                                        = $cache;
        $this->catalogResourceModelProductCollectionFactory = $catalogResourceModelProductCollectionFactory;
        $this->checkoutCartFactory                          = $checkoutCartFactory;
        $this->checkoutSession                              = $checkoutSession;
        $this->customerCustomerFactory                      = $customerCustomerFactory;
        $this->customerSessionFactory                       = $customerSessionFactory;
        $this->dataObjectFactory                            = $dataObjectFactory;
        $this->design                                       = $design;
        $this->directoryCountryFactory                      = $directoryCountryFactory;
        $this->directoryHelper                              = $directoryHelper;
        $this->directoryList                                = $directoryList;
        $this->directoryRegionFactory                       = $directoryRegionFactory;
        $this->emailTemplateFactory                         = $emailTemplateFactory;
        $this->genericFactory                               = $genericFactory;
        $this->ioFileFactory                                = $ioFileFactory;
        $this->localeCurrency                               = $localeCurrency;
        $this->quoteQuoteFactory                            = $quoteQuoteFactory;
        $this->quoteResourceModelQuoteCollectionFactory     = $quoteResourceModelQuoteCollectionFactory;
        $this->registry                                     = $registry;
        $this->request                                      = $request;
        $this->resourceConfig                               = $resourceConfig;
        $this->storeManager                                 = $storeManager;
        $this->storeSystemStore                             = $storeSystemStore;
        $this->timezone                                     = $timezone;
        $this->translateInterface                           = $translateInterface;
        $this->objectManager                                = $objectManager;
        $this->escaper = $escaper;
        parent::__construct(
            $urlEncoder,
            $urlDecoder,
            $logger,
            $moduleManager,
            $httpRequest,
            $cacheConfig,
            $eventManager,
            $urlBuilder,
            $httpHeader,
            $remoteAddress,
            $scopeConfig
        );
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckoutSession() {
        return $this->checkoutSession;
    }

    /**
     * @return \Magento\Store\Model\System\Store
     */
    public function getStoreSystemStore() {
        return $this->storeSystemStore;
    }

    /**
     * @return \Magento\Framework\Registry
     */
    public function getRegistry() {
        return $this->registry;
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager() {
        return $this->storeManager;
    }

    /**
     * @return \Magento\Directory\Helper\Data
     */
    public function getDirectoryHelper() {
        return $this->directoryHelper;
    }

    /**
     * @return \Magento\AdminNotification\Model\InboxFactory
     */
    public function getAdminNotificationInboxFactory() {
        return $this->adminNotificationInboxFactory;
    }

    /**
     * @return \Magento\Email\Model\TemplateFactory
     */
    public function getEmailTemplateFactory() {
        return $this->emailTemplateFactory;
    }

    /**
     * @return \Magento\Framework\Translate\Inline\StateInterface
     */
    public function getTranslateInterface() {
        return $this->translateInterface;
    }

    /**
     * @return \Magento\Framework\Session\GenericFactory
     */
    public function getGenericFactory() {
        return $this->genericFactory;
    }

    /**
     * @return \Magento\Framework\App\Request\Http
     */
    public function getRequest() {
        return $this->request;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    public function getCatalogResourceModelProductCollectionFactory() {
        return $this->catalogResourceModelProductCollectionFactory;
    }

    /**
     * @return \Magento\Framework\App\CacheInterface
     */
    public function getCache() {
        return $this->cache;
    }

    /**
     * @return \Magento\Directory\Model\CountryFactory
     */
    public function getDirectoryCountryFactory() {
        return $this->directoryCountryFactory;
    }

    /**
     * @return \Magento\Directory\Model\RegionFactory
     */
    public function getDirectoryRegionFactory() {
        return $this->directoryRegionFactory;
    }

    /**
     * @return \Magento\Checkout\Model\CartFactory
     */
    public function getCheckoutCartFactory() {
        return $this->checkoutCartFactory;
    }

    /**
     * @return \Magento\Customer\Model\SessionFactory
     */
    public function getCustomerSessionFactory() {
        return $this->customerSessionFactory;
    }

    /**
     * @return \Magento\Quote\Model\QuoteFactory
     */
    public function getQuoteQuoteFactory() {
        return $this->quoteQuoteFactory;
    }

    /**
     * @return \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    public function getQuoteResourceModelQuoteCollectionFactory() {
        return $this->quoteResourceModelQuoteCollectionFactory;
    }

    /**
     * @return \Magento\Customer\Model\CustomerFactory
     */
    public function getCustomerCustomerFactory() {
        return $this->customerCustomerFactory;
    }

    /**
     * @return \Magento\Framework\Filesystem\Io\FileFactory
     */
    public function getIoFileFactory() {
        return $this->ioFileFactory;
    }

    /**
     * @return \Magento\Framework\DataObjectFactory
     */
    public function getDataObjectFactory() {
        return $this->dataObjectFactory;
    }

    /**
     * @return \Magento\Config\Model\ResourceModel\Config
     */
    public function getResourceConfig() {
        return $this->resourceConfig;
    }

    /**
     * @return \Magento\Framework\View\DesignInterface
     */
    public function getDesign() {
        return $this->design;
    }

    /**
     * @return \Magento\Framework\App\Filesystem\DirectoryList
     */
    public function getDirectoryList() {
        return $this->directoryList;
    }

    /**
     * @return \Magento\Framework\Locale\ResolverInterface
     */
    public function getLocaleResolver() {
        return $this->_localeResolver;
    }

    /**
     * @return \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    public function getTimezone() {
        return $this->timezone;
    }

    /**
     * @return \Magento\Framework\Locale\CurrencyInterface
     */
    public function getLocaleCurrency() {
        return $this->localeCurrency;
    }

    /**
     * @return \Magento\Framework\App\ObjectManagerInterface
     */
    public function getObjectManager() {
        return $this->objectManager;
    }
    public function getEscaper(){
        return $this->escaper;
    }
}
<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Silk\Core\Helper;

use Magento\Store\Model\ScopeInterface;
/**
 * Checkout default helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

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
    protected $transportBuilder;

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
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $storeSystemStore;

    /*
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
     * @var \Magento\Framework\App\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        $this->checkoutSession                          = $context->getCheckoutSession();
        $this->storeSystemStore                         = $context->getStoreSystemStore();
        $this->ioFileFactory                            = $context->getIoFileFactory();
        $this->dataObjectFactory                        = $context->getDataObjectFactory();
        $this->registry                                 = $context->getRegistry();
        $this->storeManager                             = $context->getStoreManager();
        $this->directoryHelper                          = $context->getDirectoryHelper();
        $this->adminNotificationInboxFactory            = $context->getAdminNotificationInboxFactory();
        $this->transportBuilder                         = $context->getEmailTemplateFactory();
        $this->translateInterface                       = $context->getTranslateInterface();
        $this->genericFactory                           = $context->getGenericFactory();
        $this->request                                  = $context->getRequest();
        $this->cache                                    = $context->getCache();
        $this->directoryCountryFactory                  = $context->getDirectoryCountryFactory();
        $this->directoryRegionFactory                   = $context->getDirectoryRegionFactory();
        $this->checkoutCartFactory                      = $context->getCheckoutCartFactory();
        $this->customerSessionFactory                   = $context->getCustomerSessionFactory();
        $this->quoteQuoteFactory                        = $context->getQuoteQuoteFactory();
        $this->quoteResourceModelQuoteCollectionFactory = $context->getQuoteResourceModelQuoteCollectionFactory();
        $this->customerCustomerFactory                  = $context->getCustomerCustomerFactory();
        $this->resourceConfig                           = $context->getResourceConfig();
        $this->design                                   = $context->getDesign();
        $this->directoryList                            = $context->getDirectoryList();
        $this->_localeResolver                          = $context->getLocaleResolver();
        $this->timezone                                 = $context->getTimezone();
        $this->localeCurrency                           = $context->getLocaleCurrency();
        $this->objectManager                            = $context->getObjectManager();
        $this->scopeConfig                              = $context->getScopeConfig();
        $this->escaper                                  = $context->getEscaper();
        parent::__construct($context);
    }
    public function getConfig($path, $storeCode = null)
    {
        return $this->getSystemValue($path, $storeCode);
    }

    public function getSystemValue($path, $storeCode = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }
}
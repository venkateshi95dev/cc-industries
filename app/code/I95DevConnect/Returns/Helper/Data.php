<?php

/**
 * I95Dev.com
 *
 * Returns Class Doc Comment
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.i95dev.com/LICENSE-M1.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sub@i95dev.com so we can send you a copy immediately.
 *
 * PHP version 7
 *
 * @category  I95DevConnect
 * @package   I95DevConnect_Returns
 * @Description Custom returns process
 * @author    I95Dev <info@i95dev.com>
 * @copyright 2021-2022 i95Dev
 * @license   http://store.i95dev.com/LICENSE-M1.txt EULA
 * @link      http://store.i95dev.com/
 * @codingStandardsIgnoreFile
 */

namespace I95DevConnect\Returns\Helper;

use DateTime;
use DateTimeZone;
use I95DevConnect\MessageQueue\Model\SalesOrder;
use I95DevConnect\MessageQueue\Model\SalesOrderFactory;
use IntlDateFormatter;
use Magento\Backend\Model\Auth\Session;
use Magento\Catalog\Model\ProductRepository;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Mail\Template\TransportBuilder;
use \Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Xml\Parser;
use \I95DevConnect\MessageQueue\Api\LoggerInterface;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Sales\Model\Order;
use Magento\Shipping\Helper\Carrier;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Rma\Helper\Eav;

/**
 * Returns base helper
 */
class Data extends AbstractHelper
{

    /**
     * Enabled config path
     */
    public const XML_PATH_ENABLED
        = 'i95devconnect_returns/returns_enabled_settings/enable_plugin';
    public const APPROVE = 4;
    public const CANCEL = 3;
    public const PROCESSING = 2;
    public const PENDING = 1;
    /**
     * Email template config path
     */
    public const XML_PATH_EMAIL_TEMPLATE = 'i95devconnect_returns/email/email_template';

    /**
     * Date time formatter
     *
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var TimezoneInterface
     */
    // @codingStandardsIgnoreLine
    protected $_localeDate;

    /**
     * Order Model
     *
     * @var Order $salesOrder
     */
    // @codingStandardsIgnoreLine
    protected $_salesOrder;

    /**
     * @var SalesOrder
     */
    protected $customSalesOrder;

    /**
     *
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     *
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Shipping carrier helper
     *
     * @var Carrier
     */
    protected $carrierHelper;

    /**
     * Shipping carrier factory
     *
     * @var CarrierFactory
     */
    // @codingStandardsIgnoreLine
    protected $_carrierFactory;

    /**
     *
     * @var DirectoryList $directoryList
     */
    protected $directoryList;

    /**
     * Allowed hash keys for shipment tracking
     *
     * @var string[]
     */
    // @codingStandardsIgnoreLine
    protected $_allowedHashKeys = ['rma_id', 'track_id'];

    /**
     * @var CountryFactory
     */
    protected $countryFactory;

    /**
     * @var RegionFactory
     */
    protected $regionFactory;

    /**
     * Sales quote address factory
     *
     * @var AddressFactory
     */
    // @codingStandardsIgnoreLine
    protected $_addressFactory;

    /**
     * Backend authorization session model
     *
     * @var Session
     */
    // @codingStandardsIgnoreLine
    protected $_authSession;

    /**
     *
     * @var FilterManager $renderer
     */
    protected $renderer;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var Configurable
     */
    protected $resourceConfigurable;

    /**
     * @var Eav
     */
    // @codingStandardsIgnoreLine
    protected $_rmaEav;

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Parser
     */
    protected $parser;


    /**
     * Data constructor.
     * @param Context $context
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param LoggerInterface $logger
     * @param TimezoneInterface $localeDate
     * @param Order $salesOrder
     * @param StateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param Parser $parser
     * @param Reader $reader
     * @param SalesOrderFactory $customSalesOrder
     * @param Carrier $carrierHelper
     * @param CarrierFactory $carrierFactory
     * @param DirectoryList $directoryList
     * @param RegionFactory $regionFactory
     * @param CountryFactory $countryFactory
     * @param AddressFactory $addressFactory
     * @param Session $authSession
     * @param FilterManager $renderer
     * @param ProductRepository $productRepository
     * @param Configurable $resourceConfigurable
     * @param Eav $rmaEav
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        LoggerInterface $logger,
        TimezoneInterface $localeDate,
        Order $salesOrder,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        Parser $parser,
        Reader $reader,
        SalesOrderFactory $customSalesOrder,
        Carrier $carrierHelper,
        CarrierFactory $carrierFactory,
        DirectoryList $directoryList,
        RegionFactory $regionFactory,
        CountryFactory $countryFactory,
        AddressFactory $addressFactory,
        Session $authSession,
        FilterManager $renderer,
        ProductRepository $productRepository,
        Configurable $resourceConfigurable,
        Eav $rmaEav
    )
    {
        $this->dateTime = $dateTime;
        $this->_localeDate = $localeDate;
        $this->_salesOrder = $salesOrder;
        $this->scopeConfig = $context->getScopeConfig();
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->reader = $reader;
        $this->logger = $logger;
        $this->parser = $parser;
        $this->customSalesOrder = $customSalesOrder;
        $this->carrierHelper = $carrierHelper;
        $this->_carrierFactory = $carrierFactory;
        $this->directoryList = $directoryList;
        $this->regionFactory = $regionFactory;
        $this->countryFactory = $countryFactory;
        $this->_addressFactory = $addressFactory;
        $this->_authSession = $authSession;
        $this->renderer = $renderer;
        $this->productRepository = $productRepository;
        $this->resourceConfigurable = $resourceConfigurable;
        $this->_rmaEav = $rmaEav;
        parent::__construct($context);
    }

    /**
     * Check if enabled
     *
     * @return string|null
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get formated date in store timezone
     *
     * @param string $date
     * @return string
     */
    public function getFormatedDate($date)
    {
        $date = new DateTime($date);

        $date->setTimezone(
            new DateTimeZone(
                $this->_localeDate->getConfigTimezone(null, $this->storeManager->getStore())
            )
        );

        return $this->_localeDate->formatDate($date, IntlDateFormatter::SHORT);
    }

    /**
     * Get parent sku
     *
     * @param string $childId
     * @return mixed
     */
    public function getParentSku($childId)
    {
        if ($childId) {
            $parentId = $this->resourceConfigurable->getParentIdsByChild($childId);
            if (!empty($parentId)) {
                return $this->getProductById($parentId[0])->getSku();
            }
        }
    }

    /**
     * Get product by Id
     *
     * @param int $id
     * @return mixed
     */
    public function getProductById($id)
    {
        return $this->productRepository->getById($id);
    }

    /**
     * Get rma attributeId
     *
     * @param string $optionType
     * @param string $value
     * @param string $sku
     * @return mixed|string
     */
    public function getRmaAttributeId($optionType, $value, $sku) {
        $eavHelper = $this->_rmaEav;
        $alwOptions = $eavHelper->getAttributeOptionValues($optionType);
        $lowerTrim = function (&$value) {
            $value = strtolower(trim($value));
        };
        array_walk($alwOptions, $lowerTrim);
        $attributeIds = array_keys($alwOptions, strtolower(trim($value)));
        if ($optionType == 'reason' && empty($attributeIds)) {
            return 'other';
        }
        if (count($attributeIds)) {
            return $attributeIds[0];
        }
        else {
            throw new LocalizedException(__('This '. $optionType . ' not allowed for sku '. $sku));
        }
    }
}

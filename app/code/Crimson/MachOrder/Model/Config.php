<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/27/2019 12:59 PM
 * @brief
 */

namespace Crimson\MachOrder\Model;

use Crimson\MachShipping\Model\Shipping\Carrier\Source\Method;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Config
 * @package Crimson\MachOrder\Model
 */
class Config
{
    const XPATH_CATALOG_REQUEST_ENABLED         = 'mach/order/add_order_error_threshold';
    const XPATH_SHIPPING_METHOD_ATYPICAL        = 'mach/order/atypical_shipping_method_mach_code';
    const XPATH_ORDERS_UPDATE_SHORT_TERM_MONTHS         = 'mach/order/update_from_mach_short_term_number_months';
    const XPATH_ORDERS_UPDATE_SHORT_TERM_MONTHS_DEFAULT = 1;
    const XPATH_ORDERS_UPDATE_LONG_TERM_MONTHS          = 'mach/order/update_from_mach_long_term_number_months';
    const XPATH_ORDERS_UPDATE_LONG_TERM_MONTHS_DEFAULT  = 3;
    const XPATH_MACH_FREE_SHIPPING_CODE       = 'mach/shipping/freeshipping_code';
    const XPATH_MACH_FLAT_RATE_CODE           = 'mach/shipping/freeshipping_code';
    const TABLE_SUREPOST_SHIPPING_METHOD      = 'tablerate_bestway_surepost';
    const TABLE_ZIPFLATRATE_SHIPPING_METHOD   = 'tablerate_bestway_zipflatrate';
    const TABLE_SUREPOST_MACH_CODE            = 'PM';
    const TABLE_ZIPFLATRATE_MACH_CODE         = 'GND';
    const XPATH_SUREPOST_MACH_SHIPVIA_CODE    = 'carriers/tablerate_surepost/mach_shipvia_code';
    const XPATH_ZIPFLATRATE_MACH_SHIPVIA_CODE = 'carriers/tablerate_zipflatrate/mach_shipvia_code';
    const XPATH_MACH_FALLBACK_SHIPPING_METHOD = 'carriers/mach/fallback_shipping_method';

    CONST ATYPICAL_REGIONS_SHIPPING_METHOD_CODE = "atypicalregions_atypicalregions";
    CONST MACH_UPS_GROUND_SHIPPING_METHOD_CODE  = ["mach_501", "mach_207", "mach_208"];
    CONST MACH_DOWN_SHIPPING_METHOD_CODE        = "tablerate_bestway";

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /** @var Method $machMethod  */
    protected $machMethod;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface         $storeManager
     * @param Method                                             $machMethod
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Method $machMethod
    ) {
        $this->scopeConfig  = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->machMethod   = $machMethod;
    }

    /**
     * @param $websiteId
     * @return int
     * @throws NoSuchEntityException
     */
    public function getShortTermMonths($websiteId = null): int
    {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }

        return (int) $this->scopeConfig->getValue(self::XPATH_ORDERS_UPDATE_SHORT_TERM_MONTHS, ScopeInterface::SCOPE_WEBSITE, $websiteId)
            ?: self::XPATH_ORDERS_UPDATE_SHORT_TERM_MONTHS_DEFAULT;
    }

    /**
     * @param $websiteId
     * @return int
     * @throws NoSuchEntityException
     */
    public function getLongTermMonths($websiteId = null): int
    {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }

        return (int) $this->scopeConfig->getValue(self::XPATH_ORDERS_UPDATE_LONG_TERM_MONTHS, ScopeInterface::SCOPE_WEBSITE, $websiteId)
            ?: self::XPATH_ORDERS_UPDATE_LONG_TERM_MONTHS_DEFAULT;
    }

    /**
     * @param null $websiteId
     * @return int
     * @throws NoSuchEntityException
     */
    public function getAddOrderErrorThreshold($websiteId = null): int
    {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }

        return (int)$this->scopeConfig->getValue(self::XPATH_CATALOG_REQUEST_ENABLED, ScopeInterface::SCOPE_WEBSITE, $websiteId) ?: 5;
    }

    /**
     * @param null $websiteId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getMachFreeShippingCode($websiteId = null): string
    {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }

        return (string)$this->scopeConfig->getValue(self::XPATH_MACH_FREE_SHIPPING_CODE, ScopeInterface::SCOPE_WEBSITE, $websiteId) ?: '';
    }

    /**
     * @param null $websiteId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getAtypicalShippingMethodCode($websiteId = null): string
    {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }

        return (string)$this->scopeConfig->getValue(self::XPATH_SHIPPING_METHOD_ATYPICAL, ScopeInterface::SCOPE_WEBSITE, $websiteId) ?: 'EX';
    }

    /**
     * @param null $websiteId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getMachFlatRateCode($websiteId = null): string
    {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }

        return (string)$this->scopeConfig->getValue(self::XPATH_MACH_FLAT_RATE_CODE, ScopeInterface::SCOPE_WEBSITE, $websiteId) ?: '';
    }

    /**
     * @param null $websiteId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getMachTableRateSurePostCode($websiteId = null): string
    {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }

        return (string)$this->scopeConfig->getValue(self::XPATH_SUREPOST_MACH_SHIPVIA_CODE, ScopeInterface::SCOPE_WEBSITE, $websiteId) ?: '';
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getMachTableRateZipFlatRateCode(int $storeId): string
    {
        return (string)$this->scopeConfig->getValue(self::XPATH_ZIPFLATRATE_MACH_SHIPVIA_CODE, ScopeInterface::SCOPE_STORE, $storeId) ?: '';
    }

    /**
     * @param null $websiteId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getMachFallbackShippingCode($websiteId = null): string
    {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }

        $fallbackMethod = (string)$this->scopeConfig->getValue(self::XPATH_MACH_FALLBACK_SHIPPING_METHOD, ScopeInterface::SCOPE_WEBSITE, $websiteId);
        if (empty($fallbackMethod)) {
            $fallbackMethod = (string)$this->machMethod->getFinalFallbackMethod();
        }

        return $fallbackMethod;
    }
}

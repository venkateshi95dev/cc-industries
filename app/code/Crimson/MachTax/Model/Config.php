<?php

namespace Crimson\MachTax\Model;

use Crimson\MachBase\Model\Api\HealthCheck;
use Crimson\MachShipping\Model\Api\Shipping;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Config
 * @package Crimson\MachTax\Model
 */
class Config
{

    /**
     * Values for the admin config action options
     */
    const REGIONFILTER_OFF			= 0;
    const REGIONFILTER_TAX			= 1;

    const XPATH_MACH_TAX_ENABLE              = 'mach/tax/enabled';
    const XPATH_MACH_TAX_REGION_FILTER_MODE  = 'mach/tax/region_filter_mode';
    const XPATH_MACH_TAX_REGION_FILTER_LIST  = 'mach/tax/region_filter_list';
    const XPATH_MACH_TAX_COUNTRY_FILTER_LIST = 'mach/tax/country_filter_list';
    /**
     * @var HealthCheck
     */
    protected $healthCheck;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /** @var Shipping $_machShippingApi */
    protected $_machShippingApi;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Shipping $machShippingApi,
        HealthCheck $healthCheck
    ) {
        $this->scopeConfig  = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->_machShippingApi = $machShippingApi;
        $this->healthCheck = $healthCheck;
    }

    /**
     * @return bool
     */
    public function isMachUp(): bool
    {
        return $this->healthCheck->isUp();
    }

    /**
     * @param null $websiteId
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isMachTaxEnable($websiteId = null): bool
    {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }

        return $this->_machShippingApi->isMachEnabled($websiteId) && $this->scopeConfig->isSetFlag(self::XPATH_MACH_TAX_ENABLE, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    /**
     * @param $websiteId
     * @return int
     */
    public function getRegionFilterMode($websiteId): int
    {
        return (int) $this->scopeConfig->getValue(self::XPATH_MACH_TAX_REGION_FILTER_MODE, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    /**
     * @param $websiteId
     * @return array
     */
    public function getRegionFilterList($websiteId): array
    {
        $region = explode(',', $this->scopeConfig->getValue(self::XPATH_MACH_TAX_REGION_FILTER_LIST, ScopeInterface::SCOPE_WEBSITE, $websiteId));
        if ($region === false) {
            $region = [];
        }

        return $region;
    }

    /**
     * @param $websiteId
     * @return array
     */
    public function getCountryFilterList($websiteId): array
    {
        $countryList = explode(',', $this->scopeConfig->getValue(self::XPATH_MACH_TAX_COUNTRY_FILTER_LIST, ScopeInterface::SCOPE_WEBSITE, $websiteId));
        if ($countryList === false) {
            $countryList = [];
        }

        return $countryList;
    }

    /**
     * @param     $address
     * @param $websiteId
     * @param int $filterMode
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isAddressActionable($address, $websiteId, $filterMode = self::REGIONFILTER_TAX): bool
    {
        $filter = false;

        if (!$this->isMachTaxEnable($websiteId)) {
            return false;
        }

        $configFilterMode = $this->getRegionFilterMode($websiteId);
        if ($configFilterMode >= $filterMode) {
            $regionFilters = $this->getRegionFilterList($websiteId);
            if (!in_array($address->getRegionId(), $regionFilters)) {
                $filter = 'region';
            }
        }

        $countryFilters = $this->getCountryFilterList($websiteId);
        if (!in_array($address->getCountryId(), $countryFilters)) {
            $filter = 'country';
        }

        return $filter ? false : true;
    }
}

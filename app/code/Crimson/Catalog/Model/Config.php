<?php

namespace Crimson\Catalog\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Config
 * @package Crimson\Catalog\Model
 */
class Config
{
    const XPATH_STOCK_OK                 = 'catalog/crimson/stock_ok';
    const XPATH_OUT_OF_STOCK_DROPSHIP    = 'catalog/crimson/out_of_stock_dropship';
    const XPATH_CRONS_ENABLED            = 'catalog/crimson_homepage_products/cron_enabled';
    const XPATH_GENERATION_VALUES        = 'catalog/crimson_homepage_products/generations';
    const XPATH_NUMBER_OF_RESULTS        = 'catalog/crimson_homepage_products/sections_max_results';
    const XPATH_PURCHASED_PRICE_FILTER   = 'catalog/crimson_homepage_products/purchased_price_filter';
    const XPATH_DISABLE_DISCONTINUED     = 'catalog/crimson_disc/disable_discontinued';
    const XPATH_DEFAULT_AVAILABILITY_MESSAGE     = 'catalog/crimson_disc/default_availability_message';
    const XPATH_AVAILABILITY_MESSAGES     = 'catalog/crimson_disc/availability_messages';
    const XPATH_OOS_PDP_CUSTOM_MESSAGE    = 'catalog/crimson_availability/oos_message';
    const OOS_PDP_CUSTOM_MESSAGE          = 'Out of Stock';
    const XPATH_ETA_DEFAULT_MESSAGE      = 'catalog/crimson_eta/default_message';
    const XPATH_ETA_FIRST_RANGE_MESSAGE  = 'catalog/crimson_eta/first_range_message';
    const XPATH_ETA_SECOND_RANGE_MESSAGE = 'catalog/crimson_eta/second_range_message';
    const XPATH_ETA_THIRD_RANGE_MESSAGE  = 'catalog/crimson_eta/third_range_message';
    const XPATH_SPECIAL_PRICE_CRON_ENABLED = 'catalog/special_price_update/cron_enabled';
    const XPATH_ETA_FOURTH_RANGE_MESSAGE  = 'catalog/crimson_eta/fourth_range_message';

    CONST NUMBER_OF_RESULTS              = 10;
    CONST PURCHASED_PRICE_FILTER         = 25.00;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /** @var StoreManagerInterface $_storeManager */
    protected $_storeManager;

    /**
     * MachConfig constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
    }

    /**
     * @return mixed
     */
    public function isDisableDiscontinuedProduct()
    {
        return $this->scopeConfig->getValue(self::XPATH_DISABLE_DISCONTINUED, ScopeInterface::SCOPE_WEBSITE);
    }


    public function getDefaultAvailabilityMessage()
    {
        return $this->scopeConfig->getValue(self::XPATH_DEFAULT_AVAILABILITY_MESSAGE, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getAvailabilityMessages()
    {
        return json_decode(
            $this->scopeConfig->getValue(self::XPATH_AVAILABILITY_MESSAGES, ScopeInterface::SCOPE_WEBSITE) ?: '',
            true
        );
    }

    /**
     * @return mixed
     */
    public function getChildSelectedStockMsg()
    {
        return $this->scopeConfig->getValue(self::XPATH_STOCK_OK, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return mixed
     */
    public function getChildSelectedNoStockDropshipMsg()
    {
        return $this->scopeConfig->getValue(self::XPATH_OUT_OF_STOCK_DROPSHIP, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @param int $websiteId
     * @return bool
     */
    public function areCronsEnabled(int $websiteId): bool
    {
        return $this->scopeConfig->isSetFlag(self::XPATH_CRONS_ENABLED, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    /**
     * @param int $websiteId
     * @return array
     */
    public function getGenerationValues(int $websiteId): array
    {
        $region = explode(',', $this->scopeConfig->getValue(self::XPATH_GENERATION_VALUES, ScopeInterface::SCOPE_WEBSITE, $websiteId));
        if ($region === false) {
            $region = [];
        }

        return $region;
    }

    /**
     * @param int $websiteId
     * @return int
     */
    public function getResultsLimit(int $websiteId): int
    {
        return (int)$this->scopeConfig->getValue(self::XPATH_NUMBER_OF_RESULTS, ScopeInterface::SCOPE_WEBSITE, $websiteId) ?: self::NUMBER_OF_RESULTS;
    }

    /**
     * @param int $websiteId
     * @return float
     */
    public function getPurchasedPriceFilter(int $websiteId): float
    {
        return (float)$this->scopeConfig->getValue(self::XPATH_PURCHASED_PRICE_FILTER, ScopeInterface::SCOPE_WEBSITE, $websiteId) ?: self::PURCHASED_PRICE_FILTER;
    }

    public function getOOSMPDPCustomMessage(): string
    {
        $message = $this->scopeConfig->getValue(self::XPATH_OOS_PDP_CUSTOM_MESSAGE, ScopeInterface::SCOPE_WEBSITE);

        return (string)$message ?: self::OOS_PDP_CUSTOM_MESSAGE;
    }

    public function getETADefaultMsg(): string
    {
        return (string)$this->scopeConfig->getValue(self::XPATH_ETA_DEFAULT_MESSAGE, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getETAFirstRangeMsg(): string
    {
        return (string)$this->scopeConfig->getValue(self::XPATH_ETA_FIRST_RANGE_MESSAGE, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getETASecondRangeMsg(): string
    {
        return (string)$this->scopeConfig->getValue(self::XPATH_ETA_SECOND_RANGE_MESSAGE, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getETAThirdRangeMsg(): string
    {
        return (string)$this->scopeConfig->getValue(self::XPATH_ETA_THIRD_RANGE_MESSAGE, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getETAAllMessages(): array
    {
        return [
            'default'      => $this->getETADefaultMsg(),
            'first_range'  => $this->getETAFirstRangeMsg(),
            'second_range' => $this->getETASecondRangeMsg(),
            'third_range'  => $this->getETAThirdRangeMsg(),
        ];
    }

    public function isSpecialPriceCronEnabled(int $websiteId): bool
    {
        return $this->scopeConfig->isSetFlag(self::XPATH_SPECIAL_PRICE_CRON_ENABLED, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    public function getETAFourthRangeMsg(): string
    {
        return (string)$this->scopeConfig->getValue(self::XPATH_ETA_FOURTH_RANGE_MESSAGE, ScopeInterface::SCOPE_WEBSITE);
    }
}

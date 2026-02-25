<?php

namespace Crimson\BuyNow\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class BuyNowConfig
{

    const XPATH_BUYNOW_ENABLED      = 'catalog/basic_setting/enabled';
    const XPATH_BUYNOW_BRAND_FILTER = 'catalog/basic_setting/brand_filter';
    const BUYNOW_CSV_FILENAME       = 'buynow_feeds.csv';

    public function __construct(
        protected ScopeConfigInterface $scopeConfig,
        protected StoreManagerInterface $storeManager
    ) {}

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XPATH_BUYNOW_ENABLED);
    }

    public function getBrandFilter(): string
    {
        return (string) $this->scopeConfig->getValue(self::XPATH_BUYNOW_BRAND_FILTER);
    }
}

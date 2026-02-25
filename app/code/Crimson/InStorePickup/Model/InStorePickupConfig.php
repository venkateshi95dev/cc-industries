<?php

namespace Crimson\InStorePickup\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class InStorePickupConfig
{

    CONST SOURCE_COLUMN_ALLOWED_SKU = 'allowed_sku';

    const XPATH_INSTOREPICKUP_ORDER_NOTIFICATION_ENABLED = 'sales_email/order/location_notification_enabled';
    const XPATH_INSTOREPICKUP_ALL_MUST_MATCH             = 'carriers/instore/match_all_items_enabled';
    const XPATH_INSTOREPICKUP_ALL_MUST_MATCH_ERROR_MSG   = 'carriers/instore/not_all_match_error_msg';

    public function __construct(
        protected ScopeConfigInterface $scopeConfig,
        protected StoreManagerInterface $storeManager
    ) {}

    public function isLocationEmailNotificationEnabled($websiteId = null): bool
    {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }

        return $this->scopeConfig->isSetFlag(self::XPATH_INSTOREPICKUP_ORDER_NOTIFICATION_ENABLED, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    public function isLocationAllMustMatchEnabled($websiteId = null): bool
    {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }

        return $this->scopeConfig->isSetFlag(self::XPATH_INSTOREPICKUP_ALL_MUST_MATCH, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    public function getLocationAllMustMatchErrorMsg($websiteId = null): string
    {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }

        return (string)$this->scopeConfig->getValue(self::XPATH_INSTOREPICKUP_ALL_MUST_MATCH_ERROR_MSG, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }
}

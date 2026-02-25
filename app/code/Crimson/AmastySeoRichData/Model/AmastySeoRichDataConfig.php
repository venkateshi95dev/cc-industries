<?php

namespace Crimson\AmastySeoRichData\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class AmastySeoRichDataConfig
{

    CONST XPATH_ACCOUNT_BACKORDER_ENABLED = "amseorichdata/product/account_backorder_enabled";
    CONST XPATH_FUTURE_DATE_AVAILABILITY  = "amseorichdata/product/backorder_future_date";
    CONST XPATH_BACKORDER_PREORDER        = "amseorichdata/product/backorder_preorder";
    CONST XPATH_SHIPPING_WEIGHT           = "amseorichdata/product/shipping_weight_added";

    public function __construct(
        protected ScopeConfigInterface $scopeConfig
    ) {}

    public function isBackorderEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XPATH_ACCOUNT_BACKORDER_ENABLED, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getFutureDate(): string
    {
        return (string)$this->scopeConfig->getValue(self::XPATH_FUTURE_DATE_AVAILABILITY, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getBackOrderOrPreOrder(): string
    {
        return (string)$this->scopeConfig->getValue(self::XPATH_BACKORDER_PREORDER, ScopeInterface::SCOPE_WEBSITE);
    }

    public function isShippingWeightAdded(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XPATH_SHIPPING_WEIGHT, ScopeInterface::SCOPE_WEBSITE);
    }
}

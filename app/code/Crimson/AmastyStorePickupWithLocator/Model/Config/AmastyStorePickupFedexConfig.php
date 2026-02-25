<?php

namespace Crimson\AmastyStorePickupWithLocator\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class AmastyStorePickupFedexConfig
{
    public function __construct(protected readonly ScopeConfigInterface $scopeConfig)
    {
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag('storepickup_locator/general/enable_fedex_rates', ScopeInterface::SCOPE_STORE);
    }

    public function selectedDeliveryMethod(): string
    {
        return $this->scopeConfig->getValue('storepickup_locator/general/fedex_calculation_delivery_method', ScopeInterface::SCOPE_STORE);
    }
}
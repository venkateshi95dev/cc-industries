<?php

namespace Crimson\AheadworksNmiCustomizations\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class AheadworksNmiCustomizationsConfig
{

    CONST XPATH_WIND_RIVER_VALIDATE_MODE_ONLY = 'payment/aw_nmi/validate_only';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {}

    public function getValidateModeOnly(): bool
    {
        try {
            return $this->scopeConfig->isSetFlag(self::XPATH_WIND_RIVER_VALIDATE_MODE_ONLY, ScopeInterface::SCOPE_WEBSITE);
        } catch (\Exception $e) {
            return $this->scopeConfig->isSetFlag(self::XPATH_WIND_RIVER_VALIDATE_MODE_ONLY);
        }
    }
}

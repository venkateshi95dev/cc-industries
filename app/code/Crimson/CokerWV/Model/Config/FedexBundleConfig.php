<?php

namespace Crimson\CokerWV\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class FedexBundleConfig
{
    public function __construct(
        protected readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function getMaxBundleWeight() : float
    {
        $value = $this->scopeConfig->getValue('carriers/fedex/max_bundle_weight_for_shipping');

        return is_numeric($value) ? (float)$value : 0.0;
    }

    /**
     * @return array|int[]
     */
    public function getEnabledStores(): array
    {
        $value = $this->scopeConfig->getValue('carriers/fedex/enabled_for_stores');

        if (!$value) {
            return [];
        }

        return array_map('intval', explode(',', $value));
    }
}
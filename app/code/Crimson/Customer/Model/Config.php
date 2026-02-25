<?php

namespace Crimson\Customer\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Config
{
    const CONFIG_USE_COMPANY_ADDRESS_FOR_BILLING = 'company/billing_address/use_company_address_as_billing_address';

    public function __construct(
        protected StoreManagerInterface $storeManager,
        protected ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * @param int $websiteId
     * @return bool
     */
    public function useCompanyAddressAsBillingAddress(int $websiteId): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_USE_COMPANY_ADDRESS_FOR_BILLING,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }
}

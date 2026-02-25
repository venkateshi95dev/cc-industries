<?php

namespace Crimson\Customer\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class CustomerConfig
{

    CONST CATALOG_REQUEST_URL                    = 'catalog/request/form';
    CONST CATALOG_REQUEST_FORM_INPUT_NAME        = 'catalog_request_referral';
    CONST XPATH_CATALOG_REQUEST_REDIRECT_ENABLED = 'customer/catalog_request_redirect/enabled';

    public function __construct(
        protected ScopeConfigInterface $scopeConfig
    ) {}

    public function isCatalogRequestRedirectEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XPATH_CATALOG_REQUEST_REDIRECT_ENABLED, ScopeInterface::SCOPE_WEBSITE);
    }
}

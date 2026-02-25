<?php

namespace Crimson\CokerWV\Model;

use Magento\Customer\Model\Session;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Config
{

    CONST COKER_WV_CUSTOMER_ATTRIBUTES = [
        'customer_vehicle_segment',
        'gq_company',
        'my_car_group_001',
        'car_1_year',
        'car_1_make',
        'car_1_model',
        'car_1_submodel',
        'my_car_group_002',
        'car_02_year',
        'car_02_make',
        'car_02_model',
        'car_02_submodel',
        'my_car_group_003',
        'motorcycle_year_001',
        'motorcycle_make_001',
        'motorcycle_model_001',
        'motorcycle_year_002',
        'motorcycle_make_002',
        'motorcycle_model_002',
    ];

    const XPATH_LOF_PRODUCT_SHIPPING_ENABLED        = 'carriers/lofproductshipping/active';
    const XPATH_LOF_PRODUCT_SHIPPING_TITLE          = 'carriers/lofproductshipping/title';
    const XPATH_LOF_PRODUCT_SHIPPING_NAME           = 'carriers/lofproductshipping/name';
    const XPATH_LOF_PRODUCT_SHIPPING_BASED_ON       = 'carriers/lofproductshipping/shippingbasedon';
    const XPATH_LOF_PRODUCT_SHIPPING_DEFAULT_PRICE  = 'carriers/lofproductshipping/defaultprice';
    const XPATH_STOREPICKUP_MESSAGE_HTML            = 'checkout/options/storepickup_message_html';

    public function __construct(
        protected ScopeConfigInterface $scopeConfig,
        protected StoreManagerInterface $storeManager,
        protected Session $customerSession,
        protected Currency $currency
    ) {}

    public function getIsActive(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XPATH_LOF_PRODUCT_SHIPPING_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    public function getshippingTitle(): string
    {
        return (string) $this->scopeConfig->getValue(self::XPATH_LOF_PRODUCT_SHIPPING_TITLE, ScopeInterface::SCOPE_STORE);
    }

    public function getshippingName(): string
    {
        return (string) $this->scopeConfig->getValue(self::XPATH_LOF_PRODUCT_SHIPPING_NAME, ScopeInterface::SCOPE_STORE);
    }

    public function getCurrencySymbol(): string
    {
        return $this->currency->getCurrencySymbol();
    }

    public function getShippingBasedOn()
    {
        return $this->scopeConfig->getValue(self::XPATH_LOF_PRODUCT_SHIPPING_BASED_ON, ScopeInterface::SCOPE_STORE);
    }

    public function getDefaultShippingPrice()
    {
        return $this->scopeConfig->getValue(self::XPATH_LOF_PRODUCT_SHIPPING_DEFAULT_PRICE, ScopeInterface::SCOPE_STORE);
    }

    public function getPartnerId(): ?int
    {
        return $this->customerSession->getCustomerId();
    }

    public function getStorePickupMessageHtml(): string
    {
        return (string) $this->scopeConfig->getValue(self::XPATH_STOREPICKUP_MESSAGE_HTML, ScopeInterface::SCOPE_STORE);
    }
}

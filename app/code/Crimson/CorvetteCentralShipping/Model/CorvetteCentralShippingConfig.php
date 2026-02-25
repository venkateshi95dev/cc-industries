<?php

namespace Crimson\CorvetteCentralShipping\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class CorvetteCentralShippingConfig
{

    CONST GIFT_CERTIFICATE_NAME                = 'Gift Certificate';
    CONST TRUCK_FREIGHT_PREPAID_SET_PRICE      = 'Truck Freight Prepaid - Set Price';
    CONST TRUCK_FREIGHT_PREPAID_SEE_PRICE_007  = 'Truck Freight Prepaid - See Item 0070';
    CONST AIR_SHIPMENT_OK_ATTR_CODE            = 'cc_airshipmentok';
    CONST PRODUCT_PACKAGES_ATTR_CODE           = 'cc_packages';
    CONST PRODUCT_HAS_PACKAGES_ATTR_CODE       = 'cc_packageexists';
    CONST PRODUCT_TRUCK_FREIGHT_TYPE_ATTR_CODE = 'cc_truck_freight_type';

    CONST XPATH_CARRIER_US_ATYPICAL_REGIONS = "carriers/%s/atypical_us_regions";

    public function __construct(
        protected ScopeConfigInterface $scopeConfig,
        protected StoreManagerInterface $storeManager
    ) {}

    public function getUSAtypicalRegions($carrier): array
    {
        if (!$carrier) {
            return [];
        }

        $result = $this->scopeConfig->getValue(sprintf(self::XPATH_CARRIER_US_ATYPICAL_REGIONS, $carrier), ScopeInterface::SCOPE_WEBSITE);
        if (empty($result)) {
            $result = '';
        }

        return explode(',', $result);
    }
}

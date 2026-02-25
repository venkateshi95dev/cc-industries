<?php

namespace Crimson\CorvetteCentralOSCO\Model;

use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Sales\Model\Order\Shipment;
use Magento\Shipping\Model\Config as ShippingConfig;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class CorvetteCentralOSCOConfig
{

    CONST XPATH_OSCO_SHIPPING_ENABLED      = "carriers/osco/active";
    CONST XPATH_OSCO_URL                   = "carriers/osco/url";
    CONST XPATH_OSCO_USERNAME              = "carriers/osco/username";
    CONST XPATH_OSCO_PASSWORD              = "carriers/osco/password";
    CONST XPATH_OSCO_ORDER_ID              = "carriers/osco/order_id";

    CONST XPATH_OSCO_BESTWAY_ENABLED       = "carriers/osco/best_way_enabled";

    CONST XPATH_OSCO_SHOW_ETA_COUNTRIES    = "carriers/osco/eta_specificcountry";
    CONST XPATH_OSCO_SHOW_ETA_ADD_DAYS     = "carriers/osco/eta_add_days";

    CONST XPATH_OSCO_UPS_CA_ALLOWED_METHODS         = "carriers/osco/ups_ca_allowed_methods";
    CONST XPATH_OSCO_UPS_FOREIGN_ALLOWED_METHODS    = "carriers/osco/ups_foreign_allowed_methods";
    CONST XPATH_OSCO_LOG_REQUEST_RESPONSE           = "carriers/osco/log_enabled";
    CONST XPATH_OSCO_CARRIER_ALLOWED_METHODS        = "carriers/osco/%s_allowed_methods";
    CONST XPATH_OSCO_CARRIER_ALLOWED_NO_AIR_SHIPMENT_METHOD = "carriers/osco/allow_if_no_air_shipment";

    CONST XPATH_OSCO_CARRIERS = [
        'ups',
        'usps'
    ];

    public function __construct(
        protected ScopeConfigInterface $scopeConfig,
        protected StoreManagerInterface $storeManager,
        protected EncryptorInterface $encryptor,
        protected RegionFactory $regionFactory
    ) {}

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XPATH_OSCO_SHIPPING_ENABLED, ScopeInterface::SCOPE_WEBSITE);
    }

    public function isLogEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XPATH_OSCO_LOG_REQUEST_RESPONSE, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getUrl(): string
    {
        return (string)$this->scopeConfig->getValue(self::XPATH_OSCO_URL, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getOrderId(): string
    {
        return (string)$this->scopeConfig->getValue(self::XPATH_OSCO_ORDER_ID, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getUsername(): string
    {
        return (string)$this->scopeConfig->getValue(self::XPATH_OSCO_USERNAME, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getPassword(): string
    {
        $result = $this->scopeConfig->getValue(self::XPATH_OSCO_PASSWORD, ScopeInterface::SCOPE_WEBSITE);
        if (empty($result)) {
            $result = '';
        }

        return $this->encryptor->decrypt($result);
    }

    public function getUpsCAAllowedMethods(): array
    {
        $methods = $this->scopeConfig->getValue(self::XPATH_OSCO_UPS_CA_ALLOWED_METHODS, ScopeInterface::SCOPE_WEBSITE);
        if (empty($methods)) {
            return [];
        }

        $result['ups'] = explode(',', $methods);

        return $result;
    }

    public function getUpsForeignAllowedMethods(): array
    {
        $methods = $this->scopeConfig->getValue(self::XPATH_OSCO_UPS_FOREIGN_ALLOWED_METHODS, ScopeInterface::SCOPE_WEBSITE);
        if (empty($methods)) {
            return [];
        }

        $result['ups'] = explode(',', $methods);

        return $result;
    }

    public function getCountriesForETA(): array
    {
        $countries = $this->scopeConfig->getValue(self::XPATH_OSCO_SHOW_ETA_COUNTRIES, ScopeInterface::SCOPE_WEBSITE);
        if (empty($countries)) {
            return [];
        }

        return explode(',', $countries);
    }

    public function getETAExtraDays(): int
    {
        return (int)$this->scopeConfig->getValue(self::XPATH_OSCO_SHOW_ETA_ADD_DAYS, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getAllowedMethodsByCarrier($carrier): array
    {
        if (!in_array($carrier, self::XPATH_OSCO_CARRIERS)) {
            return [];
        }

        $result = $this->scopeConfig->getValue(sprintf(self::XPATH_OSCO_CARRIER_ALLOWED_METHODS, $carrier), ScopeInterface::SCOPE_WEBSITE);
        if (empty($result)) {
            $result = '';
        }

        return explode(',', $result);
    }

    public function getAllCarriersAllowedMethods(): array
    {
        $result = [];
        foreach (self::XPATH_OSCO_CARRIERS as $carrier) {
            if ($options = $this->getAllowedMethodsByCarrier($carrier)) {
                $result[$carrier] = $options;
            }
        }

        return $result;
    }

    public function getOriginAddress($storeId): array
    {
        $regionState = null;
        $regionId = $this->scopeConfig->getValue(ShippingConfig::XML_PATH_ORIGIN_REGION_ID, ScopeInterface::SCOPE_STORE, $storeId);
        if (!empty($regionId)) {
            $regionState = $this->regionFactory->create()->load($regionId);
        }

        return [
            'AddressLine1' => $this->scopeConfig->getValue(
                Shipment::XML_PATH_STORE_ADDRESS1,
                ScopeInterface::SCOPE_STORE,
                $storeId
            ),
            'City' => $this->scopeConfig->getValue(
                Shipment::XML_PATH_STORE_CITY,
                ScopeInterface::SCOPE_STORE,
                $storeId
            ),
            'State' => $regionState && $regionState->getRegionId() ? $regionState->getCode() : '',
            'Zip' => $this->scopeConfig->getValue(
                ShippingConfig::XML_PATH_ORIGIN_POSTCODE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            ),
            'Country' => "US",
            'Residential' => 0
        ];
    }

    public function isBestWayEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XPATH_OSCO_BESTWAY_ENABLED, ScopeInterface::SCOPE_WEBSITE);
    }

    //Only 1 option is possible to be selected
    public function getNoAirShippingAllowedMethod(): array
    {
        $result = [];
        $option = $this->scopeConfig->getValue(self::XPATH_OSCO_CARRIER_ALLOWED_NO_AIR_SHIPMENT_METHOD, ScopeInterface::SCOPE_WEBSITE);
        if ($option) {
            $shippingStructure = explode("_", $option);
            if (is_array($shippingStructure) && !empty($shippingStructure[0]) && !empty($shippingStructure[1])) {
                $result[$shippingStructure[0]] = $shippingStructure[1];
            }
        }

        return $result;
    }
}

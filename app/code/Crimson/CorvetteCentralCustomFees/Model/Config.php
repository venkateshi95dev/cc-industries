<?php

namespace Crimson\CorvetteCentralCustomFees\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    const XML_PATH__CORE_CHARGE_ENABLED = 'custom_fees/core_charge/enabled';
    const XML_PATH__CRATE_FEE_ENABLED = 'custom_fees/crate_fee/enabled';
    const XML_PATH__FREIGHT_FEE_ENABLED = 'custom_fees/freight_fee/enabled';
    const XML_PATH__DROPSHIP_FEE_ENABLED = 'custom_fees/dropship_fee/enabled';
    const XML_PATH__CANADIAN_FREIGHT_ENABLED = 'custom_fees/canadian_freight/enabled';
    const XML_PATH__TRUCK_FREIGHT_ENABLED = 'custom_fees/truck_freight/enabled';
    const XML_PATH__DROPSHIP_FLAT_FEE = 'custom_fees/dropship_fee/amount';
    const XML_PATH__DROPSHIP_CUSTOMER_GROUPS = 'custom_fees/dropship_fee/allowed_customer_groups';
    const XML_PATH__CANADA_TAXES_ENABLED = 'custom_fees/canada_taxes/enabled';
    const XML_PATH__CANADA_TAXES_MAPPING = 'custom_fees/canada_taxes/mapping';
    const XML_PATH__CANADA_TAXES_DEALER_GROUPS = 'custom_fees/canada_taxes/dealer_customer_groups';
    const XML_PATH__CANADA_TAXES_RETAIL_GROUPS = 'custom_fees/canada_taxes/retail_customer_groups';
    const XML_PATH__TAX_CLASS = 'custom_fees/tax_class/class';

    public function __construct(
        private ScopeConfigInterface                       $scopeConfig,
        private \Magento\Store\Model\StoreManagerInterface $storeManager,
        private SerializerInterface $serializer
    )
    {
    }

    public function getTaxClassForCustomFees(?int $storeId = null)
    {
        if (!$storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return $this->scopeConfig->getValue(self::XML_PATH__TAX_CLASS, ScopeInterface::SCOPE_STORE, $storeId);

    }

    public function getTaxMappingConfig($scope = null)
    {
        return $this->serializer->unserialize($this->scopeConfig->getValue(self::XML_PATH__CANADA_TAXES_MAPPING, ScopeInterface::SCOPE_STORE, $scope));
    }
    public function getCustomerGroupsBundleConfig($group='',$scope = null): array
    {
        if($group == 'dealer')
            return explode(',',$this->scopeConfig->getValue(self::XML_PATH__CANADA_TAXES_DEALER_GROUPS, ScopeInterface::SCOPE_STORE, $scope));
        if($group == 'retail')
            return explode(',',$this->scopeConfig->getValue(self::XML_PATH__CANADA_TAXES_RETAIL_GROUPS, ScopeInterface::SCOPE_STORE, $scope));
        return [];
    }

    public function isCustomChargeEnabled($feeCode = 'custom_fee_core_charge', ?int $storeId = null)
    {
        if (!$storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        $configPath = match ($feeCode) {
            'custom_fee_crate_charge' => self::XML_PATH__CRATE_FEE_ENABLED,
            'custom_fee_freight_charge' => self::XML_PATH__FREIGHT_FEE_ENABLED,
            'custom_fee_dropship_charge' => self::XML_PATH__DROPSHIP_FEE_ENABLED,
            'FREIGHT-CANADA' => self::XML_PATH__CANADIAN_FREIGHT_ENABLED,
            'TRUCK-FRT-PREPAID' => self::XML_PATH__TRUCK_FREIGHT_ENABLED,
            'canada_taxes' => self::XML_PATH__CANADA_TAXES_ENABLED,
            default => self::XML_PATH__CORE_CHARGE_ENABLED,
        };
        return $this->scopeConfig->isSetFlag($configPath, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getDropshipChargeAmount(?int $storeId = null)
    {
        if (!$storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return $this->scopeConfig->getValue(self::XML_PATH__DROPSHIP_FLAT_FEE, ScopeInterface::SCOPE_STORE, $storeId);
    }
    public function getDropshipCustomerGroups(?int $storeId = null)
    {
        if (!$storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return explode(',',$this->scopeConfig->getValue(self::XML_PATH__DROPSHIP_CUSTOMER_GROUPS, ScopeInterface::SCOPE_STORE, $storeId));
    }
}

<?php
/**
 * @namespace   Crimson
 * @module      PoBoxRestriction
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        01/23/2019
 */
namespace Crimson\PoBoxRestriction\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 * @package Crimson\PoBoxRestriction\Helper
 */
class Data extends AbstractHelper
{
    CONST XPATH_RESTRICTION_ENABLED_FOR_SHIPPING_METHODS = "customer/crimson_pobox/enabled_for_ship_methods";

    /**
     * Get module system configuration values
     * @param bool $field
     * @param bool $group
     * @param bool $section
     * @return mixed
     */
    public function getModuleConfig($field = false, $group = false, $section = false)
    {
        $section = ($section) ? $section : 'customer';
        $group = ($group) ?  $group : 'crimson_pobox';
        $field = ($field) ? $field : 'enabled';
        return $this->scopeConfig->getValue("{$section}/{$group}/{$field}", ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check if PO Box rule needs to be enabled
     * @return bool
     */
    public function getEnablePOBoxRule(): bool
    {
        return !$this->getModuleConfig('shipping_po_box');
    }

    /**
     * @return mixed
     */
    public function getEnabledForCustomerAccountAddressCreation()
    {
        return $this->getModuleConfig('enabled_for_customer_account_address_creation');
    }

    /**
     * @return bool
     */
    public function isRestrictionEnabledForShipMethods(): bool
    {
        return $this->scopeConfig->isSetFlag(
                self::XPATH_RESTRICTION_ENABLED_FOR_SHIPPING_METHODS,
                ScopeInterface::SCOPE_WEBSITE
            );
    }

    /**
     * @return array
     */
    public function getAllowedShipMethodsForPoBox(): array
    {
        return explode(',', $this->getModuleConfig('ship_methods'));
    }

    /**
     * Get PO Box validation class as array
     * @return array|mixed
     */
    public function getValidationClassAsArrayForPoBox()
    {
        $validationClassArray = [];
        if ($this->getEnablePOBoxRule()) {
            $validationClassArray = $this->getPoBoxRule($validationClassArray);
        }

        return $validationClassArray;
    }

    /**
     * Get PO Box Rule for validation
     *
     * @param $validationClassArray
     * @return mixed
     */
    public function getPoBoxRule($validationClassArray)
    {
        $validationClassArray['po-box-validation'] = true;

        return $validationClassArray;
    }

    /**
     * @param string $value
     *
     * @return int
     */
    function isPoStreet($value): int
    {
        return (int)(bool)preg_match("/^\s*((P(OST)?.?\s*(O(FF(ICE)?)?)?.?\s+(B(IN|OX))?)|B(IN|OX))/i", $value ?: "");
    }
}

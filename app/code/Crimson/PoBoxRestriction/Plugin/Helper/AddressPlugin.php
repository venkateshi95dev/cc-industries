<?php

namespace Crimson\PoBoxRestriction\Plugin\Helper;

use Crimson\PoBoxRestriction\Helper\Data;
use Magento\Customer\Helper\Address;

/**
 * Class AddressPlugin
 * @package Crimson\PoBoxRestriction\Plugin\Helper
 */
class AddressPlugin
{
    protected $_helper;

    /**
     * AddressPlugin constructor.
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * @param Address $subject
     * @param \Closure $proceed
     * @param $attributeCode
     * @return mixed|string
     */
    public function aroundGetAttributeValidationClass(Address $subject, \Closure  $proceed, $attributeCode)
    {
        $result = $proceed($attributeCode);
        if ($this->_helper->getModuleConfig()
            && $this->_helper->getEnablePOBoxRule()
            && $this->_helper->getEnabledForCustomerAccountAddressCreation()
            && $attributeCode == 'street'
        ) {
            $result .= ' po-box-validation';
        }

        return $result;
    }
}

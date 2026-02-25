<?php
/**
 * @namespace   Crimson
 * @module      ${MODULE}
 * @author      Peter Talavera
 * @email       ptalavera@crimsonagility.com
 * @date        3/30/2019 6:32 AM
 * @brief
 */
namespace Crimson\Attributes\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->_scopeConfig = $scopeConfig;
    }

    public function getProductTruckMsg() {

        return $this->_scopeConfig->getValue('catalog/crimson/product_truck_msg', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getAddChargeMsg() {

        return $this->_scopeConfig->getValue('catalog/crimson/product_addcharges_msg', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    public function getDropshipMsg() {

        return $this->_scopeConfig->getValue('catalog/crimson/product_dropship_msg', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProductCoreChargesMsg() {

        return $this->_scopeConfig->getValue('catalog/crimson/product_corecharges_msg', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProductNoDiscountMsg() {

        return $this->_scopeConfig->getValue('catalog/crimson/product_nodiscount_msg', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProductNoDiscountRetailMsg() {

        return $this->_scopeConfig->getValue('catalog/crimson/product_nodiscountretail_msg', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
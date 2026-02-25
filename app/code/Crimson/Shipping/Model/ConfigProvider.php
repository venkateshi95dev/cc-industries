<?php
/**
 * @namespace   Crimson
 * @module      Shipping
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        05/23/2019
 */
namespace Crimson\Shipping\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ConfigProvider
 * @package Crimson\Shipping\Model
 */
class ConfigProvider implements ConfigProviderInterface
{
    const XPATH_CART_SIDEBAR_SHIPPING_ATYPICAL_MESSAGE = 'checkout/cart/cart_sidebar_shipping_atypical_message';
    const XPATH_FREE_SHIPPING_EXCLUDED_REGIONS = 'carriers/freeshipping/regions_excluded';

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * ConfigProvider constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @return string[]
     */
    public function getConfig(): array
    {
        $shippingRegionMessage = (string)$this->_scopeConfig->getValue(self::XPATH_CART_SIDEBAR_SHIPPING_ATYPICAL_MESSAGE, ScopeInterface::SCOPE_STORE)
            ?: '';
        return [
            'shipping_region_message' => $shippingRegionMessage
        ];
    }

    /**
     * @return array|string[]
     */
    public function getFreeShippingExcludedRegions(): array
    {
        $regions = explode(',', $this->_scopeConfig->getValue(self::XPATH_FREE_SHIPPING_EXCLUDED_REGIONS, ScopeInterface::SCOPE_WEBSITE));
        if ($regions === false) {
            return [];
        }

        return $regions;
    }
}

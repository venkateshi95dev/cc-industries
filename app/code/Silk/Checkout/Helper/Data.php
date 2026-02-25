<?php
namespace Silk\Checkout\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const SYSTEM_CONFIG_BASE_PATH = 'silk_checkout/shipping_for_backorder_express/';

    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    )
    {

        parent::__construct($context);
    }

    /**
     * @param $path
     * @return mixed
     */
    protected function getStoreConfig($path)
    {
        return $this->scopeConfig->getValue(static::SYSTEM_CONFIG_BASE_PATH . $path);
    }


    public function IsAlertEnabled()
    {
        return (boolean) $this->getStoreConfig('alert_enable');
    }

    public function getAlertCopy()
    {
        return $this->getStoreConfig('alert_copy');
    }

    public function getExpressShippingMethods()
    {
        return str_replace(' ', '', $this->getStoreConfig('express_shipping_methods')??'');
    }


}

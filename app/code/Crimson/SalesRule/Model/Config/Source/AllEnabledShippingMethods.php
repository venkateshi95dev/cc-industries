<?php

namespace Crimson\SalesRule\Model\Config\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Shipping\Model\Config;
use Magento\Store\Model\ScopeInterface;

/**
 * Class AllEnabledShippingMethods
 * @package Crimson\SalesRule\Model\Config\Source
 */
class AllEnabledShippingMethods implements OptionSourceInterface
{
    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var Config
     */
    protected $_shippingConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $shippingConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Config $shippingConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_shippingConfig = $shippingConfig;
    }

    /**
     * Return array of all active carriers.
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $methods = [['value' => '', 'label' => '']];
        $carriers = $this->_shippingConfig->getAllCarriers();
        foreach ($carriers as $carrierCode => $carrierModel) {
            if ($carrierModel->isActive()){
                $carrierMethods = $carrierModel->getAllowedMethods();
                if (!$carrierMethods) {
                    continue;
                }
                $carrierTitle = $this->_scopeConfig->getValue(
                    'carriers/' . $carrierCode . '/title',
                    ScopeInterface::SCOPE_STORE
                );
                $methods[$carrierCode] = ['label' => $carrierTitle, 'value' => []];
                foreach ($carrierMethods as $methodCode => $methodTitle) {
                    if(is_array($methodTitle)){
                        $methodTitle = implode(',',$methodTitle);
                    }
                    $methods[$carrierCode]['value'][] = [
                        'value' => $carrierCode . '_' . $methodCode,
                        'label' => '[' . $carrierCode . '] ' . $methodTitle,
                    ];
                }
            }
        }

        return $methods;
    }
}

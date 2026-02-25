<?php

namespace Crimson\PoBoxRestriction\Model\Config\Source;

use Magento\Shipping\Model\Config;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ActiveShippingMethods
 * @package Crimson\PoBoxRestriction\Model\Config\Source
 */
class ActiveShippingMethods implements OptionSourceInterface
{

    protected $_options = null;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfigInterface;

    /**
     * @var Config
     */
    protected $config;

    public function __construct(
        ScopeConfigInterface $scopeConfigInterface,
        Config $config

    ) {
        $this->scopeConfigInterface = $scopeConfigInterface;
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray(): ?array
    {
        if (!isset($this->_options)) {
            $activeCarriers = $this->config->getActiveCarriers();
            foreach($activeCarriers as $carrierCode => $carrierModel) {

                if ($carrierTitle = $this->_getCarrierTitle($carrierCode)) {
                    $this->_options[] = [
                        'value' => $carrierCode,
                        'label' => "(" . $carrierCode . ") " . $carrierTitle,
                    ];
                }
            }
        }

        return $this->_options;
    }

    /**
     * @param $carrierCode
     * @return string|null
     */
    protected function _getCarrierTitle($carrierCode): ?string
    {
        if (!$carrierCode) {
            return null;
        }

        return $this->scopeConfigInterface->getValue('carriers/'.$carrierCode.'/title')
            ?
            (string) $this->scopeConfigInterface->getValue('carriers/'.$carrierCode.'/title', ScopeInterface::SCOPE_WEBSITES)
            :
            null;
    }
}

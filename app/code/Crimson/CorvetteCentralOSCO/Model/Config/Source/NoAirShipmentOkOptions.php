<?php

namespace Crimson\CorvetteCentralOSCO\Model\Config\Source;

use Magento\Ups\Model\Config\Source\OriginShipment;

class NoAirShipmentOkOptions extends OriginShipment
{

    protected array $_availableRates = [
        'ups_03' => 'UPS Ground',
    ];

    public function toOptionArray(): ?array
    {
        $arr[] = ['label' => __('-- Please Select --'), 'value' => ''];
        foreach ($this->_availableRates as $k => $v) {
            $arr[] = ['value' => $k, 'label' => __($v)];
        }

        return $arr;
    }
}

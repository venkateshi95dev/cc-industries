<?php

namespace Crimson\CorvetteCentralOSCO\Model\Config\Source;

use Magento\Ups\Model\Config\Source\OriginShipment;

class OriginShipmentCustom extends OriginShipment
{

    CONST ORIGIN_OF_THE_SHIPMENT = 'Shipments Originating in United States';

    public function toOptionArray(): array
    {
        $orShipArr = $this->carrierConfig->getCode($this->_code);
        if (empty($orShipArr[self::ORIGIN_OF_THE_SHIPMENT])) {
            return [];
        }

        $returnArr = [];
        foreach ($orShipArr[self::ORIGIN_OF_THE_SHIPMENT] as $key => $val) {
            $returnArr[] = ['value' => $key, 'label' => $val];
        }

        return $returnArr;
    }
}

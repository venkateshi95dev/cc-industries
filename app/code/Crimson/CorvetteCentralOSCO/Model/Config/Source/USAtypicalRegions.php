<?php

namespace Crimson\CorvetteCentralOSCO\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class USAtypicalRegions implements OptionSourceInterface
{

    CONST ATYPICAL_US_REGIONS = [
        'AK', //Alaska
        'AS', //American Samoa
        'AE', //Armed Africa, Canada, Europe, Middle East
        'AA', //Armed Americas
        'AP', //Armed Pacific
        'FM', //Federated Stats of Micronesia
        'GU', //guam
        'HI', //hawaii
        'MH', //Marshall Islands
        'MP', //Northern Mariana Islands
        'PR', //Puerto Rico
        'VI'  //Virgin Islands
    ];

    public function toOptionArray(): array
    {
        $result = [];
        foreach (self::ATYPICAL_US_REGIONS as $region) {
            $result[] = [
                'value' => $region,
                'label' => $region
            ];
        }

        return $result;
    }
}

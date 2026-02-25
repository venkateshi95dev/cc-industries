<?php

namespace Crimson\CokerWV\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ShippingBasedOn implements OptionSourceInterface
{

    public function toOptionArray(): array
    {
        return [
            [
                'value' => 0,
                'label' => __('Main Product')
            ],
            [
                'value' => 1
                , 'label' => __('Associated Product')
            ]
        ];
    }

    public function toArray(): array
    {
        return [
            0 => __('Main Product'),
            1 => __('Associated Product')
        ];
    }
}

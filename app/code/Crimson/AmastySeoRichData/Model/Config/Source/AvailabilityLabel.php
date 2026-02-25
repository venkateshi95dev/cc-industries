<?php

namespace Crimson\AmastySeoRichData\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class AvailabilityLabel implements OptionSourceInterface
{

    public const BACKORDER = 'https://schema.org/BackOrder';
    public const PREORDER  = 'https://schema.org/PreOrder';

    protected array $_availabilityOptions = [
        self::BACKORDER => 'BackOrder',
        self::PREORDER  => 'PreOrder',
    ];

    public function toOptionArray(): ?array
    {
        $arr     = [];
        foreach ($this->_availabilityOptions as $k => $v) {
            $arr[] = ['value' => $k, 'label' => __($v)];
        }

        return $arr;
    }
}

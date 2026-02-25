<?php

namespace Crimson\AmastySeoRichData\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class FutureDates implements OptionSourceInterface
{

    protected array $_futureDates = [
        '+1 week' => '1 Week',
        '+3 week' => '3 Weeks',
        '+1 month' => '1 Month',
    ];

    public function toOptionArray(): ?array
    {
        $arr     = [];
        foreach ($this->_futureDates as $k => $v) {
            $arr[] = ['value' => $k, 'label' => __($v)];
        }

        return $arr;
    }
}

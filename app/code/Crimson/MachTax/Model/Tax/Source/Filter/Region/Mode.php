<?php

namespace Crimson\MachTax\Model\Tax\Source\Filter\Region;

use \Crimson\MachTax\Model\Config;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Mode
 * @package Crimson\MachTax\Model\Tax\Source\Filter\Region
 */
class Mode implements OptionSourceInterface
{

    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => Config::REGIONFILTER_OFF, 'label' => __('None')],
            ['value' => Config::REGIONFILTER_TAX, 'label' => __('Filter tax calculations')]
        ];
    }
}

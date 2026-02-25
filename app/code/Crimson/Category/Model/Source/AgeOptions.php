<?php

namespace Crimson\Category\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Method
 * @package Crimson\Catgory\Model\Source
 */
class AgeOptions implements OptionSourceInterface
{

    /**
     * @return array
     */
    public function toOptionArray(): ?array
    {
        return [
            [
                'value' => '',
                'label' => __('No Age')
            ],
            [
                'value' => '-2 days',
                'label' => __('2 Days')
            ],
            [
                'value' => '-7 days',
                'label' => __('7 Days')
            ],
            [
                'value' => '-15 days',
                'label' => __('15 Days')
            ],
            [
                'value' => '-30 days',
                'label' => __('30 Days')
            ],
            [
                'value' => '-45 days',
                'label' => __('45 Days')
            ],
            [
                'value' => '-60 days',
                'label' => __('60 Days')
            ],
            [
                'value' => '-90 days',
                'label' => __('90 Days')
            ],
        ];
    }

}

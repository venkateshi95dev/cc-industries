<?php

namespace Crimson\Testimonial\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class WidgetWidth implements ArrayInterface
{

    public function toOptionArray(): array
    {
        return [
                [
                 'value' => '100%',
                 'label' => __('100%'),
                ],
                [
                 'value' => '75%',
                 'label' => __('75%'),
                ],
                [
                 'value' => '50%',
                 'label' => __('50%'),
                ],
                [
                 'value' => '25%',
                 'label' => __('25%'),
                ],
               ];
    }
}

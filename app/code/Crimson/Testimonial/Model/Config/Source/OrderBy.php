<?php

namespace Crimson\Testimonial\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class OrderBy implements ArrayInterface
{

    public function toOptionArray(): array
    {
        return [
                [
                 'value' => 'rating',
                 'label' => __('Rating'),
                ],
                [
                 'value' => 'position',
                 'label' => __('Position'),
                ],
                [
                 'value' => 'random',
                 'label' => __('Random'),
                ],
                [
                 'value' => 'recent',
                 'label' => __('Recent'),
                ]
               ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
                'rating'   => __('Rating'),
                'position' => __('Position'),
                'random' => __('Random'),
                'recent' => __('Recent')
               ];
    }
}

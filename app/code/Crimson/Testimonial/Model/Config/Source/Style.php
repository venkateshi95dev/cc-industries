<?php

namespace Crimson\Testimonial\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Style implements ArrayInterface
{

    public function toOptionArray(): array
    {
        return [
                [
                 'value' => 'list',
                 'label' => __('List'),
                ],
                [
                 'value' => 'slide',
                 'label' => __('Slide'),
                ],
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
                'list'  => __('List'),
                'slide' => __('Slide'),
               ];

    }
}

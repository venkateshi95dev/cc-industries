<?php

namespace Crimson\Testimonial\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class DateFormat implements ArrayInterface
{


    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
                [
                 'value' => 'full',
                 'label' => __('Full'),
                ],
                [
                 'value' => 'long',
                 'label' => __('Long'),
                ],
                [
                 'value' => 'medium',
                 'label' => __('Medium'),
                ],
                [
                 'value' => 'short',
                 'label' => __('Short'),
                ],
               ];
    }
}

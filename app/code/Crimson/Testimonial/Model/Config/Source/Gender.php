<?php

namespace Crimson\Testimonial\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Gender implements ArrayInterface
{

    public function toOptionArray(): array
    {
        return [
                [
                 'value' => 'mal',
                 'label' => __('Male'),
                ],
                [
                 'value' => 'female',
                 'label' => __('Female'),
                ],
               ];
    }
}

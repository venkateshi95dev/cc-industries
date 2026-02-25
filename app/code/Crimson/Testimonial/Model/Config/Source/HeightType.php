<?php

namespace Crimson\Testimonial\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class HeightType implements ArrayInterface
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
                [
                 'value' => 'custom',
                 'label' => __('Custom Height'),
                ],
                [
                 'value' => 'equal',
                 'label' => __('Auto Equal Height'),
                ],
                [
                 'value' => '',
                 'label' => __('Auto Height'),
                ],
               ];
    }
}

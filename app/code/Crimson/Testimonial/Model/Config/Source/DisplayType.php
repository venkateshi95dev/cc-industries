<?php

namespace Crimson\Testimonial\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class DisplayType implements ArrayInterface
{


    public function toOptionArray()
    {
        $data   = [];
        $data[] = [
                   'value' => 'link',
                   'label' => __('Button Link'),
                  ];
        $data[] = [
                   'value' => 'current_page',
                   'label' => __('Show on current page'),
                  ];
        return $data;
    }
}

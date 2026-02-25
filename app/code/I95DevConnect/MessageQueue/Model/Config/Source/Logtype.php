<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace I95DevConnect\MessageQueue\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Logtype implements ArrayInterface
{
    /**
     * Error option array
     *
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'info', 'label' => __('info')],
            ['value' => 'critical', 'label' => __('critical')],
            ['value' => 'error', 'label' => __('error')],
            ['value' => 'debug', 'label' => __('debug')]
        ];
    }

    /**
     * Error option array label
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'info' => __('info'),
            'critical' => __('critical'),
            'error' => __('error'),
            'debug' => __('debug')
        ];
    }
}

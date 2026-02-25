<?php

namespace Crimson\Payware\Model\Source;

use Magento\Framework\Option\ArrayInterface;
use \Magento\Payment\Model\Method\AbstractMethod;

/**
 * Class PaymentAction
 * @package Crimson\Payware\Model\Source
 */
class PaymentAction implements ArrayInterface
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
                'value' => AbstractMethod::ACTION_AUTHORIZE,
                'label' => __('Authorize')
            ],
            [
                'value' => AbstractMethod::ACTION_AUTHORIZE_CAPTURE,
                'label' => __('Authorize and Capture')
            ]
        ];
    }
}

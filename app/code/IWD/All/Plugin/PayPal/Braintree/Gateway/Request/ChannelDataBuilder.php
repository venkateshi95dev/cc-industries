<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\All\Plugin\PayPal\Braintree\Gateway\Request;

use PayPal\Braintree\Gateway\Request\ChannelDataBuilder as BraintreeChannelDataBuilder;

/**
 * Class Config
 * @package IWD\Opc\Model\Payments\Paypal
 */
class ChannelDataBuilder
{
    /**
     * @param PaypalConfig $subject
     * @param $results
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterBuild(BraintreeChannelDataBuilder $subject, $result)
    {
        return [
            'channel' => 'IWD_SP_BT'
        ];
    }
}
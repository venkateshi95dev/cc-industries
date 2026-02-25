<?php
/**
 * @namespace   Crimson
 * @module      MachOrderFees
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        5/15/2019 1:20 PM
 * @brief
 */

namespace Crimson\MachOrderFees\Plugin\Model\Api;

use Magento\Paypal\Model\Api\Nvp;
use Magento\Paypal\Model\Cart;

/**
 * Class NvpPlugin
 * @package Crimson\MachOrderFees\Plugin\Model\Api
 */
class NvpPlugin
{
    /**
     * @var Cart
     */
    protected $_cart;

    /**
     * @param Nvp  $subject
     * @param Cart $cart
     *
     * @return null
     */
    public function beforeSetPaypalCart(Nvp $subject, Cart $cart)
    {
        $this->_cart = $cart;

        return null;
    }

    /**
     * @param Nvp $subject
     * @param $methodName
     * @param array $request
     * @return array|null
     */
    public function beforeCall(Nvp $subject, $methodName, array $request): ?array
    {
        if (!in_array(
                $methodName, [
                    Nvp::SET_EXPRESS_CHECKOUT,
                    Nvp::GET_EXPRESS_CHECKOUT_DETAILS,
                    Nvp::DO_EXPRESS_CHECKOUT_PAYMENT,
                ]
            )
            || !$this->_cart) {
            return null;
        }

        // it is impossible to calculate max amount - shipping can be VERY expensive.
        $request['MAXAMT'] = $request['AMT'] + 99999.00;

        return [
            $methodName,
            $request,
        ];
    }
}

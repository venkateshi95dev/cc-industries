<?php
/**
 * @namespace   Crimson
 * @module      MachOrderFees
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/13/2019 1:46 PM
 * @brief
 */

namespace Crimson\MachOrderFees\Observer;;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

/**
 * Class SetOrderFeesOnOrder
 * @package Crimson\MachOrderFees\Observer
 */
class SetOrderFeesOnOrder implements ObserverInterface
{
    /**
     * List of attributes that should be added to an order.
     *
     * @var array
     */
    private $attributes = [
        'core_charge_amount',
        'base_core_charge_amount',
        'additional_handling_amount',
        'base_additional_handling_amount',
        'core_charge_sku_list',
    ];

    /**
     * Performs extension of the order by the Gift Wrapping attributes.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /* @var $order Order */
        $order = $observer->getEvent()->getOrder();

        /* @var $quote Quote */
        $quote = $observer->getEvent()->getQuote();
        $shippingAddress = $quote->getShippingAddress();

        foreach ($this->attributes as $attribute) {
            if ($shippingAddress->hasData($attribute)) {
                $order->setData($attribute, $shippingAddress->getData($attribute));
            }
        }
    }
}

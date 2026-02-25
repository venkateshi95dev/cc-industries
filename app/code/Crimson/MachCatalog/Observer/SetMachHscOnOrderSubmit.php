<?php
/**
 * @namespace   Crimson
 * @module      MachCatalog
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/13/2019 4:55 PM
 * @brief
 */

namespace Crimson\MachCatalog\Observer;;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

/**
 * Class SetMachHscOnOrderSubmit
 * @package Crimson\MachCatalog\Observer
 */
class SetMachHscOnOrderSubmit implements ObserverInterface
{
    /**
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

        if ($shippingAddress->getExtensionAttributes()->getMachHsc()) {
            $order->getExtensionAttributes()->setMachHsc($shippingAddress->getExtensionAttributes()->getMachHsc());
        }
    }
}

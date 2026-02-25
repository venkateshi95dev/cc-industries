<?php

namespace Crimson\MachTax\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

/**
 * Class SetTaxCodeOnOrderSubmit
 * @package Crimson\MachTax\Observer
 */
class SetTaxCodeOnOrderSubmit implements ObserverInterface
{

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /* @var $order Order */
        $order = $observer->getEvent()->getOrder();

        /* @var $quote Quote */
        $quote = $observer->getEvent()->getQuote();

        $shippingAddress = $quote->getShippingAddress();

        if ($shippingAddress && $shippingAddress->getExtensionAttributes()->getTaxCode()) {
            $order->getExtensionAttributes()->setTaxCode($shippingAddress->getExtensionAttributes()->getTaxCode());
        }
    }
}

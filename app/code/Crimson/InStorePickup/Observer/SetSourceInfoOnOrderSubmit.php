<?php

namespace Crimson\InStorePickup\Observer;

use Crimson\InStorePickup\Service\InStorePickupMethod;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class SetSourceInfoOnOrderSubmit implements ObserverInterface
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

        if ($shippingAddress->getId() &&
            $shippingAddress->getShippingMethod() === InStorePickupMethod::IN_STORE_PICKUP_SHIPPING_METHOD &&
            $shippingAddress->getSourceCode() &&
            $shippingAddress->getSourceEmail()
        ) {
            $order->getExtensionAttributes()->setSourceCode($shippingAddress->getSourceCode());
            $order->getExtensionAttributes()->setSourceEmail($shippingAddress->getSourceEmail());
        }
    }
}

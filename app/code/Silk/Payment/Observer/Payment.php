<?php
namespace Silk\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class Payment implements ObserverInterface {
    public function execute(Observer $observer) {
        $order = $observer->getEvent()->getOrder();
      	$quoteId = $order->getQuoteId();
      	$orderId = $order->getId();
      	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      	$quote = $objectManager->create("\Magento\Quote\Model\Quote")->load($quoteId);
      	$token = $quote->getPaymenttoken();
      	$clientToken = $quote->getPaymentclienttoken();
      	if($token){
			$orderitem = $objectManager->create("\Magento\Sales\Model\Order")->load($orderId);
      	    $orderitem->setPaymenttoken($token);
      	    $orderitem->setPaymentclienttoken($clientToken);
      	    $orderitem->save();
      	}

    }
}

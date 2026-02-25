<?php
namespace Silk\Coker\Observer;

use Magento\Framework\Event\ObserverInterface;

class AddCouponAfterObserver implements ObserverInterface
{

	 public function __construct(
         private readonly \Magento\Checkout\Model\Session $session,
         private readonly \Magento\Checkout\Model\Cart $cart,
	) {}

	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		$coupon = $this->session->getCmsCoupon();
		if($coupon){
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/coupon.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
			$logger->info($coupon);
			$quote = $this->cart->getQuote();
			if($quote && $quote->getId()){
				try {
				    $quote->setCouponCode($coupon);
				    $quote->save();
				    $this->session->setCmsCoupon("");
				} catch (\Exception $e) {

				}
			}
		}
	}
}

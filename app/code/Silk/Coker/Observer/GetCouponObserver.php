<?php
namespace Silk\Coker\Observer;
use Magento\Framework\Event\ObserverInterface;
class GetCouponObserver implements ObserverInterface
{
    private \Magento\Framework\App\RequestInterface $_request;

    public function __construct(
        private readonly \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->_request = $request;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $coupon = $this->_request->getParam("coupon");
        if($coupon){
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/coupon.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info($coupon);
            $this->checkoutSession->setCmsCoupon($coupon);
        }
    }
}

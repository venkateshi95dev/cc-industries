<?php
namespace Silk\Coker\Observer;
  
use Magento\Framework\Event\ObserverInterface;
  
class SetPaymentMethodAvailable implements ObserverInterface{

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if($observer->getEvent()->getMethodInstance()->getCode()=="paypal_express")
        {
            $getcheckResult = $observer->getEvent()->getResult();
            $getcheckResult->setData('is_available', false); 
        }
    }
}
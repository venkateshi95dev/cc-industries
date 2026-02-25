<?php
namespace Silk\Coker\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;


class OrderSendEmail implements ObserverInterface {
    const RADIAL = 'finderattribute/ordercancel/email';
    public function __construct(
        private readonly \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender $orderCommentSender,
        private readonly \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        private readonly \Magento\Framework\App\Config\ScopeConfigInterface $_scopeConfig
    )
    {
    }

    public function execute(Observer $observer) {
        $orderId = $observer->getEvent()->getOrder()->getId();
        $order = $this->orderRepository->get($orderId);
        $orderEmail = $this->getEmail();
        if($orderEmail){
            $orderEmail = str_replace(' ', '',$orderEmail);
            if(stripos($orderEmail,',') !==false){
               $orderEmails = explode(',',$orderEmail);
               foreach ($orderEmails as $key => $value) {
                  $order->setCustomerEmail($value);
                  $order->setState("Canceled");
                  $order->setStatus("Canceled");
                $comment = "Order ".$order->getIncrementId()." Cancelled";
                try {
                    $this->orderCommentSender->send($order, "1", $comment);
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
               }
            }else{
                $order->setCustomerEmail($orderEmail);
                $order->setState("Canceled");
                $order->setStatus("Canceled");
                $comment = "Order ".$order->getIncrementId()." Cancelled";
                 try {
                    $this->orderCommentSender->send($order, "1", $comment);
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }

            }
        }
    }

    public function getEmail() {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->_scopeConfig->getValue(self::RADIAL, $storeScope);
    }




}

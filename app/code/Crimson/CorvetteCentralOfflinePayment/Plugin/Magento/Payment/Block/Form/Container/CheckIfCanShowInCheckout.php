<?php
namespace Crimson\CorvetteCentralOfflinePayment\Plugin\Magento\Payment\Block\Form\Container;

use Crimson\CorvetteCentralOfflinePayment\Model\Config;
use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\Payment\Block\Form\Container as PaymentMethod;

class CheckIfCanShowInCheckout
{
    public function __construct(
        protected Config $config
    )
    {
    }
    public function afterGetMethods(PaymentMethod $subject, $methods): array
    {
        if($this->config->codIsHidden($subject->getQuote()->getStoreId())){
            $results = [];
            foreach ($methods as $method){
                if($method->getCode() != Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE){
                    $results[] = $method;
                }
            }
            return $results;
        }
        return $methods;
    }
}

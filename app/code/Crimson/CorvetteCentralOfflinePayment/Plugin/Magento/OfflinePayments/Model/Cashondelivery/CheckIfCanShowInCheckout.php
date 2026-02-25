<?php
namespace Crimson\CorvetteCentralOfflinePayment\Plugin\Magento\OfflinePayments\Model\Cashondelivery;

use Crimson\CorvetteCentralOfflinePayment\Model\Config;
use Magento\OfflinePayments\Model\Cashondelivery as PaymentMethod;

class CheckIfCanShowInCheckout
{
    public function __construct(
        protected Config $config
    )
    {
    }
    public function afterCanUseCheckout(PaymentMethod $subject, $result): bool
    {
        if($result)
            return !$this->config->codIsHidden();
        return $result;
    }
}

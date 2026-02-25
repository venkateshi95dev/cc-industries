<?php

namespace Crimson\SalesRule\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class SalesruleRuleSaveBefore
 * @package Crimson\SalesRule\Observer
 */
class SalesruleRuleSaveBefore implements ObserverInterface
{
    /***
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        try {
            $rule                = $observer->getRule();
            $freeShippingMethods = $rule->getData('free_shipping_methods');
            if (!$freeShippingMethods || !is_array($freeShippingMethods)) {
                return;
            }
            $rule->setData('free_shipping_methods', json_encode($freeShippingMethods));
        } catch (\Exception $e) {

        }
    }
}

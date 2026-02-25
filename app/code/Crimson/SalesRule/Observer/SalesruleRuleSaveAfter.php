<?php

namespace Crimson\SalesRule\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class SalesruleRuleSaveAfter
 * @package Crimson\SalesRule\Observer
 */
class SalesruleRuleSaveAfter implements ObserverInterface
{
    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        try {
            $rule                = $observer->getRule();
            $freeShippingMethods = $rule->getData('free_shipping_methods');
            //if no data specified, or already an array, exit.
            if (!$freeShippingMethods || is_array($freeShippingMethods)) {
                return;
            }
            $rule->setData('free_shipping_methods', json_decode($freeShippingMethods));
        } catch (\Exception $e) {

        }
    }
}

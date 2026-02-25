<?php
namespace Crimson\CorvetteCentralCustomFees\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AddCustomFeeToOrder implements ObserverInterface
{
    public function __construct(
        private \Crimson\CorvetteCentralCustomFees\Service\CustomFeeCalculator $customFeeCalculator
    )
    {

    }
    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //Set fee data to order
        $quote = $observer->getQuote();
        $order = $observer->getOrder();
        $order->getExtensionAttributes()->setBaseCustomFeeCoreCharge($quote->getBaseCustomFeeCoreCharge());
        $order->getExtensionAttributes()->setCustomFeeCoreCharge($quote->getCustomFeeCoreCharge());
        $order->getExtensionAttributes()->setBaseCustomFeeCrateCharge($quote->getBaseCustomFeeCrateCharge());
        $order->getExtensionAttributes()->setCustomFeeCrateCharge($quote->getCustomFeeCrateCharge());
        $order->getExtensionAttributes()->setBaseCustomFeeFreightCharge($quote->getBaseCustomFeeFreightCharge());
        $order->getExtensionAttributes()->setCustomFeeFreightCharge($quote->getCustomFeeFreightCharge());
        $order->getExtensionAttributes()->setBaseCustomFeeDropshipCharge($quote->getBaseCustomFeeDropshipCharge());
        $order->getExtensionAttributes()->setCustomFeeDropshipCharge($quote->getCustomFeeDropshipCharge());
        $order->getExtensionAttributes()->setBaseCustomFeeTruckFrtSetPrice($quote->getBaseCustomFeeTruckFrtSetPrice());
        $order->getExtensionAttributes()->setCustomFeeTruckFrtSetPrice($quote->getCustomFeeTruckFrtSetPrice());
        $order->getExtensionAttributes()->setIsCanadianFreight($quote->getIsCanadianFreight());
        $order->getExtensionAttributes()->setIsTruckFrt0070($quote->getIsTruckFrt0070());
        if($quote->getData("canada_taxes")){
            $canadaTaxes = $this->customFeeCalculator->processCanadaTaxFromJson($quote->getData("canada_taxes"));
            $order->getExtensionAttributes()->setCanadaTaxes($canadaTaxes);
        }
        if($quote->getData("base_canada_taxes")){
            $baseCanadaTaxes = $this->customFeeCalculator->processCanadaTaxFromJson($quote->getData("base_canada_taxes"));
            $order->getExtensionAttributes()->setBaseCanadaTaxes($baseCanadaTaxes);
        }

        return $this;
    }
}

<?php
namespace I95Dev\Customizations\Observer\Reverse;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Eav\Model\Config as EavConfig;

class Customer implements ObserverInterface
{

     /**
     * @var EavConfig
     */
    protected $eavConfig;


    public function __construct(
        EavConfig $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
    */
    public function execute(Observer $observer)
    {
        // Get the current object passed in the event
        $currentObject = $observer->getData('currentObject');
        if (!$currentObject) {
            return;
        }

        $stringData = $currentObject->stringData ?? [];

        if(isset($stringData['hasDiscount'])){
            $hasDiscount = $currentObject->dataHelper->getValueFromArray('hasDiscount', $currentObject->stringData);
            $hasDiscount = (int) ($hasDiscount ?? 0);
            $currentObject->customerInterface->setCustomAttribute('cc_hasdiscount', $hasDiscount);
        }

        // Example: Set car_generations custom attribute using label names
        $carGenerationsLabels = $currentObject->dataHelper->getValueFromArray('carGenerations', $currentObject->stringData); // e.g. "C1,C7,C10"
        if ($carGenerationsLabels) {
            $labels = array_map('trim', explode(',', $carGenerationsLabels));
            $optionIds = [];

            // Load attribute source model
            /** @var \Magento\Eav\Model\Config $eavConfig */
            $eavConfig = $this->eavConfig;
            $attribute = $eavConfig->getAttribute('customer', 'car_demos');
            $options = $attribute->getSource()->getAllOptions(false);

            // Map labels to option IDs
            foreach ($labels as $label) {
                foreach ($options as $option) {
                    // Match if option label ends with the short label (e.g. "1953-1962 C1" ends with "C1")
                   if (preg_match('/' . preg_quote( $label, '/') . '$/i', $option['label'])){
                        $optionIds[] = $option['value'];
                        break;
                    }
                }
            }

            if (is_array($optionIds) && count($optionIds) > 0) {
                $optionIds = array_unique($optionIds);
                $currentObject->customerInterface->setCustomAttribute('car_demos', $optionIds);
            }
        }

        $carWonYearLabels = $currentObject->dataHelper->getValueFromArray('carYears', $currentObject->stringData); // e.g. "C1,C7,C10"
        if ($carWonYearLabels) {
            $labels = array_map('trim', explode(',', $carWonYearLabels));
            $yearOptionIds = [];
            $eavConfig = $this->eavConfig;
            $attribute = $eavConfig->getAttribute('customer', 'corvette_years_you_own');
            $options = $attribute->getSource()->getAllOptions(false);
            // Map labels to option IDs
            foreach ($labels as $label) {
                foreach ($options as $option) {
                    // Match if option label ends with the short label (e.g. "1953-1962 C1" ends with "C1")
                    if (preg_match('/' . preg_quote( $label, '/') . '$/i', $option['label'])){
                        $yearOptionIds[] = $option['value'];
                        break;
                    }
                }
            }

            if (is_array($yearOptionIds) && count($yearOptionIds) > 0) {
                $yearOptionIds = array_unique($yearOptionIds);
                $currentObject->customerInterface->setCustomAttribute('corvette_years_you_own', $yearOptionIds);
            }
            
            $target_customer_id = $currentObject->dataHelper->getValueFromArray('targetId', $currentObject->stringData);
            if ($target_customer_id != '') {
                $currentObject->customerInterface->setCustomAttribute('target_customer_id', $target_customer_id);
            }
            
        }
       
    }
}

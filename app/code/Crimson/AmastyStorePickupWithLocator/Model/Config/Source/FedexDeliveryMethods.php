<?php

namespace Crimson\AmastyStorePickupWithLocator\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Fedex\Model\Carrier as FedexCarrier;

class FedexDeliveryMethods implements OptionSourceInterface
{
    public function __construct(
        protected readonly FedexCarrier $fedexCarrier
    ) {
    }

    public function toOptionArray()
    {
        $methods = $this->fedexCarrier->getAllowedMethods();
        $options = [];
        $options[] = ['value' => '', 'label' => ''];
        foreach ($methods as $code => $label) {
            $options[] = [
                'value' => $code,
                'label' => $label
            ];
        }

        return $options;
    }
}
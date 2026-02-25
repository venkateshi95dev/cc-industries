<?php

namespace Crimson\InStorePickup\Model\Source\Options;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\StoreManagerInterface;

class Website implements OptionSourceInterface
{

    public function __construct(
        protected StoreManagerInterface $storeManagerInterface
    ){}

    public function toOptionArray(): array
    {
        $result = [];
        foreach ($this->storeManagerInterface->getWebsites() as $website) {
            $result[] = [
                'label' => $website->getName(),
                'value' => $website->getId()
            ];
        }

        return $result;
    }
}

<?php

namespace Crimson\InStorePickup\Model\Checkout;

use Crimson\InStorePickup\Model\InStorePickupConfig;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\StoreManagerInterface;

class ConfigProvider implements ConfigProviderInterface
{

    public function __construct(
        protected StoreManagerInterface $storeManager,
        protected InStorePickupConfig   $inStorePickupConfig
    ) {}

    public function getConfig() : array
    {
        return [
            'not_all_match_no_locations_found_error_msg' => $this->inStorePickupConfig->getLocationAllMustMatchErrorMsg($this->storeManager->getWebsite()->getId()),
        ];
    }
}

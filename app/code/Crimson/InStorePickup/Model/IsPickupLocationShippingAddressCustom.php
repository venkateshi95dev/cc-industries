<?php

namespace Crimson\InStorePickup\Model;

use Magento\InventoryInStorePickup\Model\ExtractPickupLocationAddressData;
use Magento\InventoryInStorePickupApi\Api\Data\PickupLocationInterface;
use Magento\InventoryInStorePickupQuote\Model\ExtractQuoteAddressShippingAddressData;
use Magento\InventoryInStorePickupQuote\Model\GetShippingAddressData;
use Magento\InventoryInStorePickupQuote\Model\IsPickupLocationShippingAddress;
use Magento\Quote\Api\Data\AddressInterface;

class IsPickupLocationShippingAddressCustom extends IsPickupLocationShippingAddress
{

    public function __construct(
        private readonly ExtractPickupLocationAddressData       $extractPickupLocationShippingAddressData,
        private readonly ExtractQuoteAddressShippingAddressData $extractQuoteAddressShippingAddressData,
        private readonly GetShippingAddressData                 $getShippingAddressData
    ) {
        parent::__construct($extractPickupLocationShippingAddressData, $extractQuoteAddressShippingAddressData, $getShippingAddressData);
    }

    public function execute(PickupLocationInterface $pickupLocation, AddressInterface $shippingAddress): bool
    {
        $data = $this->getShippingAddressData->execute() +
            $this->extractPickupLocationShippingAddressData->execute($pickupLocation);

        if (!$shippingAddress->getExtensionAttributes() ||
            !$shippingAddress->getExtensionAttributes()->getPickupLocationCode()
        ) {
            return false;
        }

        $shippingAddressData = $this->extractQuoteAddressShippingAddressData->execute($shippingAddress);

        foreach ($data as $key => $value) {
            if (!array_key_exists($key, $shippingAddressData) || $this->_areSameAddresses($key, $shippingAddressData[$key], $value)) {
                return false;
            }
        }

        return true;
    }

    private function _areSameAddresses($key, $shippingAddressDataValue, $value): bool
    {
        $shippingAddressDataValue = is_string($shippingAddressDataValue) ? strtolower($shippingAddressDataValue) : $shippingAddressDataValue;
        $value = is_string($value) ? strtolower($value) : $value;

        if ($key === 'postcode') {
            $shippingAddressDataValue = strlen($shippingAddressDataValue) > 5 ? substr($shippingAddressDataValue, 0, 5) : $shippingAddressDataValue;
            $value = strlen($value) > 5 ? substr($value, 0, 5) : $value;
        }

        return $shippingAddressDataValue != $value;
    }
}

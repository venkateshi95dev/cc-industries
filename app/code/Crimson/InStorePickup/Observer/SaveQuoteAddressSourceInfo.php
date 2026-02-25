<?php

namespace Crimson\InStorePickup\Observer;

use Crimson\InStorePickup\Service\InStorePickupMethod;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\InventoryInStorePickupApi\Api\Data\PickupLocationInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\InventoryApi\Api\SourceRepositoryInterface;

class SaveQuoteAddressSourceInfo implements ObserverInterface
{

    public function __construct(
        protected SourceRepositoryInterface $sourceRepositoryInterface
    ) {}

    public function execute(Observer $observer)
    {
        /** @var Address $quoteAddress */
        $quoteAddress = $observer->getEvent()->getDataObject();
        if ($quoteAddress->getId() &&
            $quoteAddress->getShippingMethod() === InStorePickupMethod::IN_STORE_PICKUP_SHIPPING_METHOD &&
            !empty($quoteAddress->getExtensionAttributes()->getPickupLocationCode())
        ) {
            $sourceCode = $quoteAddress->getExtensionAttributes()->getPickupLocationCode();
            $quoteAddress->setData('source_code', $sourceCode);
            $quoteAddress->setData('source_email', $this->_getSourceInfoByCode($sourceCode));
        }

        if ($quoteAddress->getId() &&
            $quoteAddress->getShippingMethod() === InStorePickupMethod::IN_STORE_PICKUP_SHIPPING_METHOD &&
            empty($quoteAddress->getExtensionAttributes()->getPickupLocationCode()) &&
            !empty($quoteAddress->getData('source_code'))
        ) {
            $quoteAddress->getExtensionAttributes()->setPickupLocationCode($quoteAddress->getData('source_code'));
        }

        return $this;
    }

    private function _getSourceInfoByCode($sourceCode): string
    {
        try {
            return (string) $this->sourceRepositoryInterface->get($sourceCode)->getEmail();
        } catch (\Exception $e) {
            return '';
        }
    }
}

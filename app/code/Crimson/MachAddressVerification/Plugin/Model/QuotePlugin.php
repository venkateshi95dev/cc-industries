<?php

namespace Crimson\MachAddressVerification\Plugin\Model;

use Crimson\MachBase\Model\MachConfig;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class QuotePlugin
 * @package Crimson\MachAddressVerification\Plugin\Model
 */
class QuotePlugin
{
    public function __construct(
        protected StoreManagerInterface $storeManager
    ) {}

    public function aroundGetAddressesCollection(
        Quote $subject,
        callable $proceed
    ) {
        $hasCollection = $subject->addressCollectionWasSet();
        $addresses = $proceed();
        $webSite = $this->storeManager->getWebsite();

        if (!$hasCollection && $webSite->getCode() === MachConfig::ZIP_WEBSITE_CODE) {
            foreach ($addresses as $address) {
                /** @var Address $address */
                if ($address->getAddressType() == "billing") {
                    continue;
                }

                $address->getExtensionAttributes()->setShipAdv($address->getData('ship_adv'));
                $address->getExtensionAttributes()->setShipAdvDate($address->getData('ship_adv_date'));
                $address->getExtensionAttributes()->setShipAdvDpi($address->getData('ship_adv_dpi'));
                $address->getExtensionAttributes()->setShipAdvDi($address->getData('ship_adv_di'));
            }
        }

        return $addresses;
    }
}

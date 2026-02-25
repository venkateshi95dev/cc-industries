<?php
 /**
 * @namespace   Crimson
 * @module      MachCatalog
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/13/2019 11:02 PM
 * @brief
 */

namespace Crimson\MachCatalog\Plugin\Model;

use Crimson\MachBase\Model\MachConfig;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class QuotePlugin
 * @package Crimson\MachCatalog\Plugin\Model
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

        //if collection wasn't set before, but is now, attach our extension variables.
        if (!$hasCollection && $webSite->getCode() === MachConfig::ZIP_WEBSITE_CODE) {
            foreach ($addresses as $address) {
                /** @var Address $address */
                $address->getExtensionAttributes()->setMachHsc($address->getData('mach_hsc'));
            }
        }

        return $addresses;
    }
}

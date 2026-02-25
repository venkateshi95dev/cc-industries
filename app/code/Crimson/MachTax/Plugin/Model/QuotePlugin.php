<?php

namespace Crimson\MachTax\Plugin\Model;

use Crimson\MachBase\Model\MachConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class QuotePlugin
 * @package Crimson\MachTax\Plugin\Model
 */
class QuotePlugin
{

    public function __construct(
        protected StoreManagerInterface $storeManager
    ) {}

    /**
     * @param Quote $subject
     * @param callable $proceed
     * @return mixed
     * @throws LocalizedException
     */
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
                $address->getExtensionAttributes()->setTaxCode($address->getData('tax_code'));
            }
        }

        return $addresses;
    }
}

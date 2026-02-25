<?php
/**
 * @namespace   Crimson
 * @module      MachCatalog
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/13/2019 11:18 PM
 * @brief
 */

namespace Crimson\MachCatalog\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class SaveQuoteAddressMachHsc
 * @package Crimson\MachCatalog\Observer
 */
class SaveQuoteAddressMachHsc implements ObserverInterface
{
    /**
     * Check if gift registry prefix is set for customer address id
     * and set giftRegistryItemId
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer): SaveQuoteAddressMachHsc
    {
        /** @var \Magento\Quote\Model\Quote\Address $quoteAddress */
        $quoteAddress = $observer->getEvent()->getDataObject();
        $quoteAddress->setData('mach_hsc', $quoteAddress->getExtensionAttributes()->getMachHsc());

        return $this;
    }
}

<?php

namespace Crimson\MachTax\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Address;

/**
 * Class SaveQuoteAddressTaxCode
 * @package Crimson\MachTax\Observer
 */
class SaveQuoteAddressTaxCode implements ObserverInterface
{

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer): SaveQuoteAddressTaxCode
    {
        /** @var Address $quoteAddress */
        $quoteAddress = $observer->getEvent()->getDataObject();
        $quoteAddress->setData('tax_code', $quoteAddress->getExtensionAttributes()->getTaxCode());

        return $this;
    }

}

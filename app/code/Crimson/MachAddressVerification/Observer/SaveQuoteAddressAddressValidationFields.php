<?php

namespace Crimson\MachAddressVerification\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Address;

/**
 * Class SaveQuoteAddressAddressValidationFields
 * @package Crimson\MachAddressVerification\Observer
 */
class SaveQuoteAddressAddressValidationFields implements ObserverInterface
{

    /**
     * @param Observer $observer
     *
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        /** @var Address $quoteAddress */
        $quoteAddress = $observer->getEvent()->getDataObject();
        if ($quoteAddress->getAddressType() == "shipping") {
            $quoteAddress->setData('ship_adv', $quoteAddress->getExtensionAttributes()->getShipAdv());
            $quoteAddress->setData('ship_adv_date', $quoteAddress->getExtensionAttributes()->getShipAdvDate());
            $quoteAddress->setData('ship_adv_dpi', $quoteAddress->getExtensionAttributes()->getShipAdvDpi());
            $quoteAddress->setData('ship_adv_di', $quoteAddress->getExtensionAttributes()->getShipAdvDi());
        }

        return $this;
    }

}

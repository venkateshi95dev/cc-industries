<?php
/**
 * @namespace   Crimson
 * @module      MachTax
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        5/23/2019 1:40 PM
 * @brief
 */

namespace Crimson\MachTax\Model\Sales\Total\Quote;

use Crimson\MachShipping\Model\Api\Result\Tax as TaxResult;
use Crimson\MachTax\Model\Config as MachConfig;
use Crimson\MachTax\Model\Service\Tax as MachTaxService;
use Magento\Customer\Api\Data\AddressInterfaceFactory as CustomerAddressFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory as CustomerAddressRegionFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory;
use Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory;
use Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Tax\Helper\Data;
use Magento\Tax\Model\Config;

/**
 * Class Tax
 * @package Crimson\MachTax\Model\Sales\Total\Quote
 */
class Tax extends \Magento\Tax\Model\Sales\Total\Quote\Tax
{
    const TAX_CODE = 'tax';

    public function __construct(
        Config                           $taxConfig,
        TaxCalculationInterface          $taxCalculationService,
        QuoteDetailsInterfaceFactory     $quoteDetailsDataObjectFactory,
        QuoteDetailsItemInterfaceFactory $quoteDetailsItemDataObjectFactory,
        TaxClassKeyInterfaceFactory      $taxClassKeyDataObjectFactory,
        CustomerAddressFactory           $customerAddressFactory,
        CustomerAddressRegionFactory     $customerAddressRegionFactory,
        Data                             $taxData,
        protected MachConfig             $machConfig,
        protected MachTaxService         $machTaxService,
        Json                             $serializer = null
    ) {
        parent::__construct($taxConfig, $taxCalculationService, $quoteDetailsDataObjectFactory, $quoteDetailsItemDataObjectFactory, $taxClassKeyDataObjectFactory, $customerAddressFactory, $customerAddressRegionFactory, $taxData, $serializer);
    }

    public function collect(Quote $quote, ShippingAssignmentInterface $shippingAssignment, Total $total)
    {
        if (!$this->_shouldProcessMachTax($quote->getStore()->getWebsite()->getId())) {
            return parent::collect($quote, $shippingAssignment, $total);
        }

        $this->clearValues($total);
        if (!$shippingAssignment->getItems()) {
            return $this;
        }

        $address = $shippingAssignment->getShipping()->getAddress();
        if (!$address->getPostcode()
            || $address->getPostcode() === '-'
            || !$this->machConfig->isAddressActionable($address, $quote->getStore()->getWebsiteId())
        ) {
            $this->resetCalculatedTaxes($address);
            $address->getExtensionAttributes()->setTaxCode(0);

            return $this;
        }

        $taxResult = $this->machTaxService->getTax($address);

        if ($taxResult instanceof TaxResult) {
            $total->addTotalAmount(self::TAX_CODE, $taxResult->getTaxAmount());
            $total->addBaseTotalAmount(self::TAX_CODE, $taxResult->getTaxAmount());

            /**
             * Customization ZIP-833: Adding the Tax Code returned in GET_FREIGHT
             * to the Quote Address to be added to the addOrder API call later.
             */
            if (isset($taxResult['tax_code'])) {
                $address->getExtensionAttributes()->setTaxCode($taxResult->getTaxCode());
            }
        }

        return $this;
    }

    protected function _shouldProcessMachTax($websiteId = null): bool
    {
        return $this->machConfig->isMachTaxEnable($websiteId) && $this->machConfig->isMachUp();
    }

    public function resetCalculatedTaxes(Address $address): Tax
    {
        $items = $address->getAllItems();
        if (!count($items)) {
            return $this;
        }

        foreach ($items as $item) {
            /**
             * Child item's tax we calculate for parent
             */
            if ($item->getParentItemId()) {
                continue;
            }

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $child->setTaxPercent(0);
                }
            } else {
                $item->setTaxPercent(0);
            }
        }

        return $this;
    }

    protected function clearValues(Total $total): void
    {
        if (!$this->_shouldProcessMachTax()) {
            parent::clearValues($total);
            return;
        }

        $total->setTotalAmount('tax', 0);
        $total->setBaseTotalAmount('tax', 0);
        $total->setTotalAmount('extra_tax', 0);
        $total->setBaseTotalAmount('extra_tax', 0);
    }
}

<?php

namespace Crimson\ZipCokerWvConsolidation\Preference\Magento\Sales\Total\Quote;

use Crimson\CokerWV\Api\CokerStoreInterface;
use Crimson\CokerWV\Api\WVStoreInterface;
use Crimson\MachBase\Model\MachConfig;
use Magento\Customer\Api\Data\AddressInterfaceFactory as CustomerAddressFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory as CustomerAddressRegionFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory;
use Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory;
use Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address\Total as QuoteAddressTotal;
use Magento\Tax\Helper\Data;
use Magento\Tax\Model\Config;
use Magento\Tax\Model\Sales\Total\Quote\Tax;

class ConsolidationTax extends Tax
{

    public function __construct(
        Config $taxConfig,
        TaxCalculationInterface $taxCalculationService,
        QuoteDetailsInterfaceFactory $quoteDetailsDataObjectFactory,
        QuoteDetailsItemInterfaceFactory $quoteDetailsItemDataObjectFactory,
        TaxClassKeyInterfaceFactory $taxClassKeyDataObjectFactory,
        CustomerAddressFactory $customerAddressFactory,
        CustomerAddressRegionFactory $customerAddressRegionFactory,
        protected StoreManagerInterface $storeManager,
        protected \Avalara\AvaTax\Model\Tax\Sales\Total\Quote\Tax $avatax,
        protected \Crimson\MachTax\Model\Sales\Total\Quote\Tax $crimsonMachTax,
        Data $taxData,
        Json $serializer = null
    )
    {
        parent::__construct(
            $taxConfig,
            $taxCalculationService,
            $quoteDetailsDataObjectFactory,
            $quoteDetailsItemDataObjectFactory,
            $taxClassKeyDataObjectFactory,
            $customerAddressFactory,
            $customerAddressRegionFactory,
            $taxData,
            $serializer
        );
    }

    public function collect(Quote $quote, ShippingAssignmentInterface $shippingAssignment, QuoteAddressTotal $total)
    {
        $webSiteCode = $quote->getStore()->getWebsite()->getCode();
        if (in_array($webSiteCode, [CokerStoreInterface::COKER_WEBSITE_CODE, WVStoreInterface::WV_WEBSITE_CODE])) {
            return $this->avatax->collect($quote, $shippingAssignment, $total);
        }

        if ($webSiteCode === MachConfig::ZIP_WEBSITE_CODE) {
            return $this->crimsonMachTax->collect($quote, $shippingAssignment, $total);
        }

        return parent::collect($quote, $shippingAssignment, $total);
    }
}

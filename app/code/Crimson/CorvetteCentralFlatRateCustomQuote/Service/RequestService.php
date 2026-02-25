<?php

namespace Crimson\CorvetteCentralFlatRateCustomQuote\Service;

use Crimson\CorvetteCentralCustomFees\Service\CustomFeeCalculator;
use Crimson\CorvetteCentralOSCO\Model\CorvetteCentralOSCOConfig;
use Crimson\CorvetteCentralShipping\Model\Config\Source\USAtypicalRegions;
use Crimson\CorvetteCentralShipping\Model\CorvetteCentralShippingConfig;
use Crimson\CorvetteCentralShipping\Service\QuoteType;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;

class RequestService
{

    public function __construct(
        protected QuoteType $quoteTypeService,
    ) {}

    public function isFullFlatRateCustomQuoteRequest(RateRequest $request): bool
    {
        $itemsNumber = 0;
        foreach ($request->getAllItems() as $item) {
            if ($this->quoteTypeService->isNotShippableItem($item)) {
                continue;
            }

            if (!$this->_isFlatRateCustomItem($item, $request)) {
                return false;
            }

            $itemsNumber++;
        }

        return $itemsNumber > 0;
    }

    private function _isFlatRateCustomItem($item, RateRequest $request): bool
    {
        //When ship-to is US and to a regular Region then Truck freight type needs to be 'Truck Freight Prepaid - See Item 0070'
        if ($request->getDestCountryId() === AbstractCarrier::USA_COUNTRY_ID &&
            !empty($request->getDestRegionCode()) &&
            !in_array($request->getDestRegionCode(), USAtypicalRegions::ATYPICAL_US_REGIONS)
        ) {
            return CorvetteCentralShippingConfig::TRUCK_FREIGHT_PREPAID_SEE_PRICE_007 == $item->getProduct()->getAttributeText(CorvetteCentralShippingConfig::PRODUCT_TRUCK_FREIGHT_TYPE_ATTR_CODE);
        }

        return true;
    }
}

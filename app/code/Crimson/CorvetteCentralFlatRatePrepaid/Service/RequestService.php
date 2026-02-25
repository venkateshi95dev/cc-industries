<?php

namespace Crimson\CorvetteCentralFlatRatePrepaid\Service;

use Crimson\CorvetteCentralCustomFees\Service\CustomFeeCalculator;
use Crimson\CorvetteCentralShipping\Model\CorvetteCentralShippingConfig;
use Crimson\CorvetteCentralShipping\Service\QuoteType;
use Magento\Quote\Model\Quote\Address\RateRequest;

class RequestService
{

    public function __construct(
        protected QuoteType $quoteTypeService,
    ) {}

    public function isFullFlatRatePrepaidRequest(RateRequest $request): bool
    {
        $itemsNumber = 0;
        foreach ($request->getAllItems() as $item) {
            if ($this->quoteTypeService->isNotShippableItem($item)) {
                continue;
            }

            if (!$this->_isFlatRatePrepaidItem($item)) {
                return false;
            }

            $itemsNumber++;
        }

        return $itemsNumber > 0;
    }

    private function _isFlatRatePrepaidItem($item): bool
    {
        $wewe = $item->getProduct()->getAttributeText(CorvetteCentralShippingConfig::PRODUCT_TRUCK_FREIGHT_TYPE_ATTR_CODE);
        $wewe11 = CorvetteCentralShippingConfig::TRUCK_FREIGHT_PREPAID_SEE_PRICE_007;
        return (float)$item->getProduct()->getData(CustomFeeCalculator::CC_FREIGHT_CHARGE_ATTR) &&
            CorvetteCentralShippingConfig::TRUCK_FREIGHT_PREPAID_SEE_PRICE_007 != $item->getProduct()->getAttributeText(CorvetteCentralShippingConfig::PRODUCT_TRUCK_FREIGHT_TYPE_ATTR_CODE);
    }
}

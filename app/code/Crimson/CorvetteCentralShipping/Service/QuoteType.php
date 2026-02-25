<?php

namespace Crimson\CorvetteCentralShipping\Service;

use Crimson\CorvetteCentralCustomFees\Service\CustomFeeCalculator;
use Crimson\CorvetteCentralShipping\Model\CorvetteCentralShippingConfig;
use Magento\Catalog\Model\Product;

class QuoteType
{

    public function isNotShippableItem($item): bool
    {
        return $item->getProduct()->isVirtual() ||
            $item->getProductType() !== "simple" ||
            str_contains($item->getProduct()->getName(), CorvetteCentralShippingConfig::GIFT_CERTIFICATE_NAME);
    }
}

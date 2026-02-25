<?php

namespace Crimson\Shipping\Plugin\Carrier;

use Magento\OfflineShipping\Model\Carrier\Tablerate;
use Magento\Quote\Model\Quote\Address\RateRequest;

/**
 * Class TablerateCollectRates
 * @package Crimson\Shipping\Plugin\Carrier
 */
class TablerateCollectRates
{

    /**
     * @param Tablerate $subject
     * @param RateRequest $request
     * @return RateRequest[]
     */
    public function beforeCollectRates(Tablerate $subject, RateRequest $request)
    {
        $request->setConditionName("");

        return [$request];
    }
}

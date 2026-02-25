<?php

namespace Crimson\Shipping\Plugin\Carrier;

use Crimson\Shipping\Model\Carrier\Tableratetwoday;
use Magento\Quote\Model\Quote\Address\RateRequest;

/**
 * Class TableratetwodayCollectRates
 * @package Crimson\Shipping\Plugin\Carrier
 */
class TableratetwodayCollectRates
{

    /**
     * @param Tableratetwoday $subject
     * @param RateRequest $request
     * @return RateRequest[]
     */
    public function beforeCollectRates(Tableratetwoday $subject, RateRequest $request)
    {
        $request->setConditionName("");

        return [$request];
    }
}

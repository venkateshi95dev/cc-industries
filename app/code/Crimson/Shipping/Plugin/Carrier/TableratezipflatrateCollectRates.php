<?php

namespace Crimson\Shipping\Plugin\Carrier;

use Crimson\Shipping\Model\Carrier\Tableratezipflatrate;
use Magento\Quote\Model\Quote\Address\RateRequest;

/**
 * Class TableratezipflatrateCollect
 * @package Crimson\Shipping\Plugin\Carrier
 */
class TableratezipflatrateCollectRates
{

    /**
     * @param Tableratezipflatrate $subject
     * @param RateRequest $request
     * @return RateRequest[]
     */
    public function beforeCollectRates(Tableratezipflatrate $subject, RateRequest $request)
    {
        $request->setConditionName("");

        return [$request];
    }
}

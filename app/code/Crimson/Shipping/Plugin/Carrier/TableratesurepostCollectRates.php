<?php

namespace Crimson\Shipping\Plugin\Carrier;

use Crimson\Shipping\Model\Carrier\Tableratesurepost;
use Magento\Quote\Model\Quote\Address\RateRequest;

/**
 * Class TableratesurepostCollectRates
 * @package Crimson\Shipping\Plugin\Carrier
 */
class TableratesurepostCollectRates
{

    /**
     * @param Tableratesurepost $subject
     * @param RateRequest $request
     * @return RateRequest[]
     */
    public function beforeCollectRates(Tableratesurepost $subject, RateRequest $request)
    {
        $request->setConditionName("");

        return [$request];
    }
}

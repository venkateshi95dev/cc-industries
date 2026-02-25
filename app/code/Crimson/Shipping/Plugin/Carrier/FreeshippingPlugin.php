<?php

namespace Crimson\Shipping\Plugin\Carrier;

use Crimson\Shipping\Model\ConfigProvider;
use Magento\OfflineShipping\Model\Carrier\Freeshipping;
use Magento\Quote\Model\Quote\Address\RateRequest;

class FreeshippingPlugin
{

    /**
     * @var ConfigProvider
     */
    protected  $configProvider;

    public function __construct(
        ConfigProvider $configProvider
    )
    {
        $this->configProvider = $configProvider;
    }

    public function aroundCollectRates(Freeshipping $subject, callable $proceed, RateRequest $request)
    {
        if ($this->_areExcludedRegionsInRequest($request)) {
            return false;
        }

        return $proceed($request);
    }

    /**
     * @param RateRequest $request
     * @return bool
     */
    protected function _areExcludedRegionsInRequest(RateRequest $request): bool
    {
        if ($request &&
            $request->getDestCountryId() == "US" &&
            ($request->getDestRegionCode() &&
                in_array($request->getDestRegionCode(), $this->configProvider->getFreeShippingExcludedRegions())
            )
        ) {
            return true;
        }

        return false;
    }
}

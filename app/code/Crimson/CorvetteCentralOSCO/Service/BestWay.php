<?php

namespace Crimson\CorvetteCentralOSCO\Service;

use Crimson\CorvetteCentralOSCO\Model\CorvetteCentralOSCOConfig;
use Magento\Quote\Model\Quote\Address\RateRequest;

class BestWay
{

    CONST USPS_PRIORITY_PACKAGE_WEIGHT = 5;
    CONST USPS_PRIORITY_PACKAGE_VALUE  = 500;
    CONST BEST_WAY_LABEL = "Best Way";
    CONST BEST_WAY_UPS_CARRIER_CODE  = 'ups';
    CONST BEST_WAY_USPS_CARRIER_CODE = 'usps';

    private array $_bestWayCarriersMethods = [
        self::BEST_WAY_UPS_CARRIER_CODE  => '03',
        self::BEST_WAY_USPS_CARRIER_CODE => '1'
    ];

    public function __construct(
        protected CorvetteCentralOSCOConfig $corvetteCentralOSCOConfig
    ) {}

    //it's one of them, either UPS(Ground) or USPS(Priority Mail), we check first if USPS is possible, if it is, then we use it as Best Way,
    //BUT if it is not possible we use UPS if exists.
    public function calculateBestWay(RateRequest $request, array $rates): array
    {
        if ($this->_isUSPSPriorityPossible($request, $rates)) {
            return $this->_removePriorityByCarrier($rates, self::BEST_WAY_UPS_CARRIER_CODE, self::BEST_WAY_USPS_CARRIER_CODE);
        } else {
            return $this->_removePriorityByCarrier($rates, self::BEST_WAY_USPS_CARRIER_CODE, self::BEST_WAY_UPS_CARRIER_CODE);
        }
    }

    public function isEnabled(): bool
    {
        return $this->corvetteCentralOSCOConfig->isBestWayEnabled();
    }

    private function _removePriorityByCarrier(array $rates, string $carrierToRemove, string $carrierToUpdate): array
    {
        if (!empty($rates[$carrierToRemove])) {
           $keyToRemove = array_search((string) $this->_bestWayCarriersMethods[$carrierToRemove], array_column($rates[$carrierToRemove], 'ServiceType'));
           if ($keyToRemove !== false) {
               unset($rates[$carrierToRemove][$keyToRemove]);
           }
        }

        if (!empty($rates[$carrierToUpdate])) {
            $keyToUpdate = array_search((string) $this->_bestWayCarriersMethods[$carrierToUpdate], array_column($rates[$carrierToUpdate], 'ServiceType'));
            if ($keyToUpdate !== false) {
                $rates[$carrierToUpdate][$keyToUpdate]['ServiceDescription'] = self::BEST_WAY_LABEL;
            }
        }

        return $rates;
    }

    private function _isUSPSPriorityPossible(RateRequest $request, array $rates): bool
    {
        if (!($request->getPackageWeight() <= self::USPS_PRIORITY_PACKAGE_WEIGHT) || !($request->getPackageValue() <= self::USPS_PRIORITY_PACKAGE_VALUE)) {
            return false;
        }

        if (empty($rates[self::BEST_WAY_USPS_CARRIER_CODE])) {
            return false;
        }

        return array_search((string) $this->_bestWayCarriersMethods[self::BEST_WAY_USPS_CARRIER_CODE], array_column($rates[self::BEST_WAY_USPS_CARRIER_CODE], 'ServiceType')) !== false;
    }
}

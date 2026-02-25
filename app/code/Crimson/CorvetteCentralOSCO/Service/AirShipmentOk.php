<?php

namespace Crimson\CorvetteCentralOSCO\Service;

class AirShipmentOk
{

    public function check(array $oscoRates, array $noAirShipmentOption): array
    {
        if (empty($noAirShipmentOption)) {
            return [];
        }

        //clearing carriers
        $result = [];
        foreach ($oscoRates as $carrierCode => $carrierOptions) {
            if (!isset($noAirShipmentOption[$carrierCode])) {
                continue;
            }

            if (($key = array_search((string)$noAirShipmentOption[$carrierCode], array_column($carrierOptions, 'ServiceType'))) !== false) {
                $result[$carrierCode][] = $carrierOptions[$key];
                break;
            }
        }

        return $result;
    }
}

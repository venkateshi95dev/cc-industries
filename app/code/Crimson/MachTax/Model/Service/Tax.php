<?php

namespace Crimson\MachTax\Model\Service;

use Crimson\MachShipping\Model\Api\Shipping;
use Magento\Quote\Model\Quote\Address;
use Crimson\MachShipping\Model\Api\Result\Tax as TaxResult;

/**
 * Class Tax
 * @package Crimson\MachTax\Model\Service
 */
class Tax
{

    public function __construct(
        protected Shipping $machShippingApi
    ) {}

    public function getTax(Address $address): ?TaxResult
    {
        if ($address->getCountryId() !== "US") {
            return null;
        }

        try {
            $request = $this->machShippingApi->buildFreightRequest($address);
            $apiResult = $this->machShippingApi->getTax($request);
        } catch (\Exception $e) {
            return null;
        }

        return $apiResult;
    }
}

<?php

namespace Crimson\MachAddressVerification\Plugin\Tax;

use Crimson\MachAddressVerification\Model\Service\VerifyAddress;
use Crimson\MachTax\Model\Service\Tax;
use Magento\Quote\Model\Quote\Address;

class AddressVerification
{

    public function __construct(
        protected VerifyAddress $addressVerify
    ) {}

    /**
     * @param Tax     $subject
     * @param Address $address
     *
     * - Mach now needs the Address County value to get more accurate taxes,
     *   and ADDRESS_VERIFY returns that value.
     *   Tax amount could be different even inside the same State.
     * - Street value and Zip Code value are required to this call.
     * - Only US addresses
     *
     * @return array
     * @throws \Exception
     */
    public function beforeGetTax(Tax $subject, Address $address): array
    {
        if ($address->getCountryId() === "US") {
            $address = $this->addressVerify->verifyAddressToGetCountyValue($address);
        }

        return [$address];
    }
}

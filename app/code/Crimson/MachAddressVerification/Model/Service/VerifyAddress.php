<?php

namespace Crimson\MachAddressVerification\Model\Service;

use Crimson\MachAddressVerification\Model\Api\AddressVerification;
use Crimson\MachBase\Model\MachConfig;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\RateRequest;

/**
 * Class VerifyAddress
 * @package Crimson\MachAddressVerification\Model\Service
 */
class VerifyAddress
{

    public function __construct(
        protected MachConfig $machConfig,
        protected AddressVerification $addressVerification
    ) {}

    public function verifyAddressToGetResBusIndicatorValue(RateRequest $request): string
    {
        $addressData = $this->_prepareAddressToVerifyByRequest($request);
        if (empty($addressData['street_1'])) {
            return '';
        }

        //street is needed to verify Commercial/Residential value
        $result = $this->addressVerification->addressVerify($addressData);

        return is_array($result) && !empty($result[0]) && isset($result[0][AddressVerification::RESBUSFLAG_MACH_ARRAY_KEY])
            ? (string) $result[0][AddressVerification::RESBUSFLAG_MACH_ARRAY_KEY]
            : '';
    }

    private function _prepareAddressToVerifyByRequest(RateRequest $request): DataObject
    {
        $streets = explode(PHP_EOL, $request->getDestStreet() ?? '');

        return new DataObject(
            [
                'street_1'    => ucwords(strtolower(trim($streets[0] ?? ''))),
                'street_2'    => ucwords(strtolower(trim($streets[1] ?? ''))),
                'city'        => ucwords(strtolower(trim($request->getDestCity() ?? ''))),
                'region_id'   => $request->getDestRegionId(),
                'region_code' => $request->getDestRegionCode(),
                'zipcode'     => substr(trim($request->getDestPostcode() ?? ''), 0, 5),
            ]
        );
    }

    public function verifyAddressToGetCountyValue(Address $address): Address
    {
        //We reset the County value every time because we only set it to the Address
        //if we can validate the Address.
        $address->setCounty(null);
        $addressData = $this->_prepareAddressToVerifyByAddress($address);
        if (!empty($addressData['zipcode'])) {
            //We only need the County value, we pass true
            $result = $this->addressVerification->addressVerify($addressData, true);
            if (is_array($result) && !empty($result[AddressVerification::COUNTY_MACH_ARRAY_KEY])) {
                $address->setCounty($result[AddressVerification::COUNTY_MACH_ARRAY_KEY]);
            }
        }

        return $address;
    }

    /**
     * @param Address $address
     *
     * The array values for Mach should be like this:
     *
     * array(
     *     'street1'    => $address->getStreet1(),
     *     'street2'    => $address->getStreet2(),
     *     'city'       => $address->getCity(),
     *     'zipcode'    => $address->getPostcode(),
     *     'region_id'  => $address->getRegionId(),
     *     'country_id' => $address->getCountryId(),
     * )
     *
     * - Street value and Zip Code value are required to this call.
     *
     * @return DataObject
     */
    private function _prepareAddressToVerifyByAddress(Address $address): DataObject
    {
        $streets = [];
        $addressData = new DataObject ($address->getData());
        if (isset($addressData['street']) && !empty($addressData['street'])) {
            $streets = explode(PHP_EOL, $addressData->getStreet());
        }

        return new DataObject(
            [
                'street_1'    => ucwords(strtolower(trim($streets[0] ?? ''))),
                'street_2'    => ucwords(strtolower(trim($streets[1] ?? ''))),
                'city'        => ucwords(strtolower(trim($addressData->getCity() ?? ''))),
                'region_id'   => $addressData->getRegionId(),
                'region_code' => $addressData->getRegionCode(),
                'zipcode'     => substr(trim($addressData->getPostcode() ?? ''), 0, 5),
            ]
        );
    }
}

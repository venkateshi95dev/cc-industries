<?php

namespace Crimson\MachAddressVerification\Model\Validation;

use IWD\AddressValidation\Model\Validation\Address;

class AddressCustom extends Address
{

    /**
     * @param Address $address
     * @return bool
     */
    public function isEqualWithAddress(Address $address): bool
    {
        $countyId_1   = trim(strtolower($this->getCountryId() ?: ''));
        $postcode_1   = str_replace(' ', '', trim(strtolower($this->getPostcode() ?: ''))); // Remove spaces for compare
        $city_1       = trim(strtolower($this->getCity() ?: ''));
        $postal_1     =  trim(strtolower((string)$this->getPostalTown() ?: ''));
        $city_1_mod   = $postal_1 ? ($city_1 . ' ' . $postal_1) : null;
        $street_1     = trim(strtolower($this->getStreetNumber() . ' ' . $this->getStreet()));
        $regionCode_1 = trim(strtolower($this->getRegionCode() ?: ''));
        $region_1     = trim(strtolower($this->getRegion() ?: ''));

        $countyId_2   = trim(strtolower($address->getCountryId()));
        $postcode_2   = str_replace(' ', '', trim(strtolower($address->getPostcode() ?: ''))); // Remove spaces for compare
        $city_2       = trim(strtolower($address->getCity() ?: ''));
        $city_2_mod   = str_replace(array(', ', ','), ' ', $city_2);
        $street_2     = trim(strtolower($address->getStreet() ?: ''));
        $regionCode_2 = trim(strtolower($address->getRegionCode() ?: ''));
        $region_2     = trim(strtolower($address->getRegion() ?: ''));

        if ($countyId_1 != $countyId_2 ||
            $street_1 != $street_2 ||
            !empty($postcode_1) && !empty($postcode_2) && $postcode_1 != $postcode_2
        ) {
            return false;
        }

        // Check if city match or city with postal town in some countries
        if ($city_1 != $city_2 && $city_1_mod != $city_2_mod) {
            return false;
        }
        if (!empty($regionCode_1) && !empty($regionCode_2)) {
            return strtolower($regionCode_1) == strtolower($regionCode_2);
        }

        if (!empty($regionCode_1) && !empty($regionCode_2)) {
            return strtolower($region_1) == strtolower($region_2);
        }

        return true;
    }
}

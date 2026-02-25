<?php

namespace IWD\AddressValidation\Model\Usps;

/**
 * USPS Address Class
 * used across other class to create addresses represented as objects
 * @since 1.0
 * @author Vincent Gabriel
 */
class USPSAddressRest
{
    private $addressInfo = [];


    /**
     * Set the address2 property
     * @param string|int $value
     * @return object USPSAddress
     */
    public function setAddress($value)
    {
        return $this->setField('streetAddress', $value);
    }

    /**
     * Set the city property
     * @param string|int $value
     * @return object USPSAddress
     */
    public function setCity($value)
    {
        return $this->setField('city', $value);
    }

    /**
     * Set the state property
     * @param string|int $value
     * @return object USPSAddress
     */
    public function setState($value)
    {
        return $this->setField('state', $value);
    }

    /**
     * Set the zip4 property - zip code value represented by 4 integers
     * @param string|int $value
     * @return object USPSAddress
     */
    public function setZip5($value)
    {
        return $this->setField('ZIPCode', $value);
    }

    /**
     * Set the zip5 property - zip code value represented by 5 integers
     * @param string|int $value
     * @return object USPSAddress
     */
    public function setZip4($value)
    {
        return $this->setField('ZIPPlus4', $value);
    }

    /**
     * Set the firmname property
     * @param string|int $value
     * @return object USPSAddress
     */
    public function setFirmName($value)
    {
        return $this->setField('firm', $value);
    }

    /**
     * Add an element to the stack
     * @param string|int $key
     * @param string|int $value
     * @return object USPSAddress
     */
    public function setField($key, $value)
    {
        $this->addressInfo[$key] = $value;
        return $this;
    }

    /**
     * Returns a list of all the info we gathered so far in the current address object
     * @return array
     */
    public function getAddressInfo()
    {
        return $this->addressInfo;
    }

}

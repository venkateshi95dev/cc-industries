<?php

namespace IWD\AddressValidation\Model\Usps;

/**
 * USPS Address Verify Class
 * used to verify an address is valid
 * @since 1.0
 * @author Vincent Gabriel
 */
class USPSAddressVerifyRest extends USPSBaseRest
{
    /**
     * @var string - the api version used for this type of call
     */
    protected $apiVersion = 'Verify';

    /**
     * @var array - list of all addresses added so far
     */
    protected $addresses = [];

    /**
     * Perform the API call to verify the address
     * @return string
     */
    public function verify()
    {
        return $this->doRequest();
    }

    /**
     * returns array of all addresses added so far
     * @return array
     */
    public function getPostFields()
    {
        return $this->addresses;
    }

    /**
     * Add Address to the stack
     * @param USPSAddress $data
     * @param null $id
     */
    public function addAddress(USPSAddressRest $data, $id = null)
    {
        $this->addresses = $data->getAddressInfo();
    }
}

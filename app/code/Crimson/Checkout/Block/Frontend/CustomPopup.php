<?php

namespace Crimson\Checkout\Block\Frontend;

use IWD\AddressValidation\Block\Frontend\Popup;
use IWD\AddressValidation\Helper\Data;
use IWD\AddressValidation\Model\Validation\Validator;
use Magento\Checkout\Model\Session;
use Magento\Csp\Helper\CspNonceProvider;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\Quote;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote\Address;

/**
 * Class CustomPopup
 * @package Crimson\Checkout\Block\Frontend
 */
class CustomPopup extends Popup
{

    protected $_shippingAddress;

    public function __construct(
        Context $context,
        Data $helper,
        Validator $addressValidator,
        CspNonceProvider $cspNonceProvider,
        protected Session $session,
        protected Json $json,
        array $data
    )
    {
        parent::__construct($context, $helper, $addressValidator, $cspNonceProvider, $data);
        $this->_shippingAddress = $this->getQuoteShippingAddress();
    }

    /**
     * @return Address|null
     */
    public function getQuoteShippingAddress(): ?Address
    {
        try {
            $quote = $this->session->getQuote();
            if ($quote && $quote->getShippingAddress()) {
                return $quote->getShippingAddress();
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return bool|false|string
     */
    public function getAddress()
    {
        return $this->json->serialize($this->_getAddressData());
    }

    /**
     * @return array
     */
    protected function _getAddressData(): array
    {
        $address = [
            'street'       => "",
            'street_line1' => "",
            'street_line2' => "",
            'city'         => "",
            'country_id'   => "",
            'postcode'     => "",
            'region_id'    => "",
            'region'       => "",
        ];

        if (!$this->_shippingAddress) {
            return $address;
        }

        if (!empty($this->_shippingAddress->getStreet())) {
            $address["street"] = $this->_shippingAddress->getStreet();
            $address["street_line1"] = $this->_shippingAddress->getStreetLine(1);
            $address["street_line2"] = $this->_shippingAddress->getStreetLine(2);
        }

        if (!empty($this->_shippingAddress->getCity())) {
            $address["city"] = $this->_shippingAddress->getCity();
        }

        if (!empty($this->_shippingAddress->getCountryId())) {
            $address["country_id"] = $this->_shippingAddress->getCountryId();
        }

        if (!empty($this->_shippingAddress->getPostcode())) {
            $address["postcode"] = $this->_shippingAddress->getPostcode();
        }

        if (!empty($this->_shippingAddress->getRegionId())) {
            $address["region_id"] = $this->_shippingAddress->getRegionId();
        }

        if (!empty($this->_shippingAddress->getRegion())) {
            $address["region"] = $this->_shippingAddress->getRegion();
        }

        return $address;
    }

    /**
     * @return string
     */
    public function getSetQuoteAddressUrl(): string
    {
        return $this->_urlBuilder->getUrl('paypal_address_validation/address/validate');
    }

}

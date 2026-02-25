<?php
/**
 * @namespace   Crimson
 * @module      ${MODULE}
 * @author      Peter Talavera
 * @email       ptalavera@crimsonagility.com
 * @date        3/30/2019 6:32 AM
 * @brief
 */
namespace Crimson\Checkout\Helper;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Quote\Api\Data\EstimateAddressInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 * @package Crimson\Checkout\Helper
 */
class Data extends AbstractHelper
{
	CONST XPATH_CHECKOUT_CART_TRUCK_MESSAGE = 'checkout/cart/cart_truck_msg';
	CONST XPATH_PAYPAL_REVIEW_SHIPPING_ADDRESS_ERROR_MESSAGE = 'checkout/paypal_review/paypal_review_msg';
	CONST XPATH_ONLY_ALLOW_NOT_VALID_ADDRESS_IF_THERE_ARE_NOT_SUGGESTIONS = 'iwd_addressvalidation/general/only_allow_not_valid_address_if_there_are_not_suggestions';

    protected $_scopeConfig;

    protected $_checkoutSession;

    protected $_estimateAddress;

    /**
     * Data constructor.
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $_checkoutSession
     * @param EstimateAddressInterface $estimateAddress
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Session $_checkoutSession,
        EstimateAddressInterface $estimateAddress
    )
    {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->_checkoutSession = $_checkoutSession;
        $this->_estimateAddress = $estimateAddress;
    }

	/**
	 * @return string
	 */
    public function getTruckMsg(): string
    {
        return (string) $this->_scopeConfig->getValue(self::XPATH_CHECKOUT_CART_TRUCK_MESSAGE, ScopeInterface::SCOPE_STORE);
    }

	/**
	 * @return string
	 */
	public function getPayPalReviewShippingAddressError(): string
    {
		return (string) $this->_scopeConfig->getValue(self::XPATH_PAYPAL_REVIEW_SHIPPING_ADDRESS_ERROR_MESSAGE, ScopeInterface::SCOPE_WEBSITE);
	}

    public function getShippingAddress(): string
    {
        return $this->_estimateAddress->getCountryId();
    }

    /**
     * @return bool
     */
    public function getOnlyAllowNotValidAddressIfThereAreNotSuggestions(): bool
    {
        return (bool) $this->_scopeConfig->getValue(self::XPATH_ONLY_ALLOW_NOT_VALID_ADDRESS_IF_THERE_ARE_NOT_SUGGESTIONS, ScopeInterface::SCOPE_STORE);
    }
}

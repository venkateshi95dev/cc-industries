<?php

namespace Crimson\Checkout\Block\Express;

use IWD\AddressValidation\Model\Google\Validation;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Crimson\Checkout\Model\Service\CartBackorders;
use Crimson\Checkout\Model\Service\IsCartDropship;
use Crimson\Checkout\Helper\Data as CheckoutHelper;
use Magento\Framework\View\Element\Template\Context;
use Magento\InventoryConfigurationApi\Exception\SkuIsNotAssignedToStockException;
use Magento\Quote\Model\Quote\Address;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Cms\Api\GetBlockByIdentifierInterface;
use Crimson\MachOrder\Model\Config;
use Crimson\MachOrder\Model\Api\AddOrder;
use IWD\AddressValidation\Model\Validation\Validator as IWDValidation;
use Magento\Tax\Helper\Data;

/**
 * Class Review
 * @package Crimson\Checkout\Block\Express
 */
class Review extends \Magento\Paypal\Block\Express\Review
{
    CONST US_COUNTRY_ID = "US";

    /**
     * @var bool
     */
    protected $_visibleHsc = false;

    /**
     * @var bool
     */
    protected $_isDropship = false;

    /**
     * @var bool
     */
    protected $_isBackorders = false;

	/**
	 * @var bool
	 */
	protected $_isInValidAddress = false;

    /**
     * @var string
     */
    protected $_hscMessage = '';

    /**
     * @var CartBackorders
     */
    protected $cartBackorders;

    /**
     * @var IsCartDropship
     */
    protected $isCartDropship;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var GetBlockByIdentifierInterface
     */
    protected $getBlockByIdentifier;

	/**
	 * @var CheckoutHelper
	 */
	protected $checkoutHelper;

    /**
     * @var Validation|\IWD\AddressValidation\Model\Ups\Validation|\IWD\AddressValidation\Model\Usps\Validation
     */
    protected $iwdValidation;


    public function __construct(
        CartBackorders $cartBackorders,
        IsCartDropship $isCartDropship,
        Context $context,
        Data $taxHelper,
        \Magento\Customer\Model\Address\Config $addressConfig,
        PriceCurrencyInterface $priceCurrency,
        StoreManagerInterface $storeManager,
        GetBlockByIdentifierInterface $getBlockByIdentifier,
        CheckoutHelper $checkoutHelper,
        IWDValidation $iwdValidation,
        array $data = []
    )
    {
        parent::__construct($context, $taxHelper, $addressConfig, $priceCurrency, $data);
        $this->cartBackorders = $cartBackorders;
        $this->isCartDropship = $isCartDropship;
        $this->storeManager   = $storeManager;
        $this->getBlockByIdentifier = $getBlockByIdentifier;
        $this->checkoutHelper = $checkoutHelper;
        $this->iwdValidation = $iwdValidation->getValidator();
    }

    /**
     * If this returns false we don't add the HSC checkbox to the form
     * @return bool
     */
    public function getDisplayHscCheckbox(): bool
    {
        $quoteSubtotal = $this->_quote->getSubtotal() > AddOrder::ORDER_SUBTOTAL_HSC_VALUE;
        return $this->_isBackorders && !$this->_isDropship && $quoteSubtotal;
    }

    /**
     * @return bool
     */
    public function getIsHscVisible(): bool
    {
        return $this->_visibleHsc;
    }

	/**
	 * @return bool
	 */
	public function getIsInValidAddress(): bool
	{
		return $this->_isInValidAddress;
	}

	/**
	 * @return string
	 */
	public function getIsInValidAddressErrorMessage(): string
	{
		return $this->checkoutHelper->getPayPalReviewShippingAddressError();
	}

	/**
     * @return bool
     */
    public function verifyHscOtherElements(): bool
    {
        if (!$this->_quote->getShippingAddress()) {
            return false;
        }

        $quoteSubtotal = $this->_quote->getSubtotal() > AddOrder::ORDER_SUBTOTAL_HSC_VALUE;
        $quoteShippingMethod = $this->_quote->getShippingAddress()->getShippingMethod();
        $allowedMachGround = in_array($quoteShippingMethod, Config::MACH_UPS_GROUND_SHIPPING_METHOD_CODE) ||
            $quoteShippingMethod == Config::MACH_DOWN_SHIPPING_METHOD_CODE;

        return $allowedMachGround && $quoteSubtotal;
    }

    /**
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws SkuIsNotAssignedToStockException
     */
    public function verifyBackOrdersAndDropship(): bool
    {
        $this->_isBackorders = $this->cartBackorders->doesCartHaveBackOrders($this->_quote);
        $this->_isDropship = $this->isCartDropship->doesCartHaveDropships($this->_quote);

        return $this->_isBackorders && !$this->_isDropship;
    }

    /**
     * @return \Magento\Paypal\Block\Express\Review
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws SkuIsNotAssignedToStockException
     */
    protected function _beforeToHtml()
    {
        $this->_visibleHsc = false;
        $this->_isDropship = false;
        $this->_isBackorders = false;
        $methodInstance = $this->_quote->getPayment()->getMethodInstance();
        $this->setPaymentMethodTitle($methodInstance->getTitle());

        $this->setShippingRateRequired(true);
        if ($this->_quote->getIsVirtual()) {
            $this->setShippingRateRequired(false);
        } else {
            // prepare shipping rates
            $this->_address = $this->_quote->getShippingAddress();

            //checking shipping address IWD validation
	        if (!$this->validateAddress($this->_address)) {
		        $this->_isInValidAddress = true;
	        }

            //HSC - verifying Backorders and Dropship if it is a US quote, and other elements
            if ($this->_address->getCountryId() === self::US_COUNTRY_ID &&
                $this->verifyBackOrdersAndDropship() &&
                $this->verifyHscOtherElements()
            ) {
                $this->_visibleHsc = true;
            }

            $this->_hscMessage = $this->_getCartPageBackorderBlockContent();

            $groups = $this->_address->getGroupedAllShippingRates();
            if ($groups && $this->_address) {
                $this->setShippingRateGroups($groups);
                // determine current selected code & name
                foreach ($groups as $code => $rates) {
                    foreach ($rates as $rate) {
                        if ($this->_address->getShippingMethod() == $rate->getCode()) {
                            $this->_currentShippingRate = $rate;
                            break 2;
                        }
                    }
                }
            }

            $canEditShippingAddress = $this->_quote->getMayEditShippingAddress() && $this->_quote->getPayment()
                    ->getAdditionalInformation(\Magento\Paypal\Model\Express\Checkout::PAYMENT_INFO_BUTTON) == 1;
            // misc shipping parameters
            $this->setShippingMethodSubmitUrl(
                $this->getUrl("{$this->_controllerPath}/saveShippingMethod", ['_secure' => true])
            )->setCanEditShippingAddress(
                $canEditShippingAddress
            )->setCanEditShippingMethod(
                $this->_quote->getMayEditShippingMethod()
            );
        }

        $this->setEditUrl(
            $this->getUrl("{$this->_controllerPath}/edit")
        )->setPlaceOrderUrl(
            $this->getUrl("{$this->_controllerPath}/placeOrder", ['_secure' => true])
        );

        return parent::_beforeToHtml();
    }

    /**
     * @param Address $shippingAddress
     * @return bool
     */
    protected function validateAddress(Address $shippingAddress): bool
    {
        try {
            // preparing Quote Shipping Address to validate
            $address = $this->getAddressForValidation($shippingAddress);
            $this->iwdValidation->setAddressForValidation($address);

            // validation and response
            $this->iwdValidation->validate();
            $response = $this->iwdValidation->getValidationResponse()->toDataObject();

            return $response->getIsValid();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param Address $shippingAddress
     * @return array
     */
    private function getAddressForValidation(Address $shippingAddress): array
    {
        $address = [];
        $address["street"] = $shippingAddress->getStreetLine(1) . PHP_EOL . $shippingAddress->getStreetLine(2);
        $address["city"] = $shippingAddress->getCity();
        $address["country_id"] = $shippingAddress->getCountry();
        $address["region_id"] = $shippingAddress->getRegionId();
        $address["region"] = $shippingAddress->getRegion();
        $address["postcode"] = $shippingAddress->getPostcode();

        return $address;
    }

    /**
     * @return Phrase|string|null
     */
    protected function _getCartPageBackorderBlockContent()
    {
        try {
            $storeId = $this->storeManager->getStore(true)->getId();

            $block = $this->getBlockByIdentifier->execute('backorder_shipments_checkout', $storeId);

            return $block->getContent();
        } catch (\Exception $e) {
            return __('...');
        }
    }

    /**
     * @return string
     */
    public function getHscMessage(): string
    {
        return (string) $this->_hscMessage;
    }


}

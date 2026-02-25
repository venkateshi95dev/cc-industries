<?php

namespace Crimson\CartTopMessages\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Config
 * @package Crimson\CartTopMessages\Model
 */
class Config
{
    const DISCOUNT_AMOUNT = 850.00;
    const DISCOUNT_AMOUNT_NUMBER_TO_COMPARE = 600.00;
    const XPATH_ENABLED = 'checkout/cart/cart_top_enabled';
    const XPATH_CUSTOMER_GROUPS = 'checkout/cart/cart_top_messages_customer_groups';
    const XPATH_CART_TOP_DISCOUNT_AMOUNT = 'checkout/cart/discount_number';
    const XPATH_CART_TOP_DISCOUNT_AMOUNT_COMPARE = 'checkout/cart/discount_number_compare';
    const XPATH_CART_TOP_NOT_QUALIFIED_MESSAGE = 'checkout/cart/cart_top_not_qualified_message';
    const XPATH_CART_TOP_QUALIFIED_MESSAGE = 'checkout/cart/cart_top_qualified_message';
    const XPATH_CART_TOP_MODAL_TEXT = 'checkout/cart/cart_top_messages_modal_text';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface  $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig  = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    public function isEnabled() : bool
    {
        return (bool)$this->scopeConfig->getValue(self::XPATH_ENABLED, ScopeInterface::SCOPE_STORE);
    }
    /**
     * @return float
     */
    public function getDiscountAmount(): float
    {
        return (float)$this->scopeConfig->getValue(self::XPATH_CART_TOP_DISCOUNT_AMOUNT, ScopeInterface::SCOPE_STORE)
            ?: self::DISCOUNT_AMOUNT;
    }

    /**
     * @return float
     */
    public function getDiscountAmountNumberToCompare(): float
    {
        return (float)$this->scopeConfig->getValue(self::XPATH_CART_TOP_DISCOUNT_AMOUNT_COMPARE, ScopeInterface::SCOPE_STORE)
            ?: self::DISCOUNT_AMOUNT_NUMBER_TO_COMPARE;
    }

    /**
     * @return string
     */
    public function getTopCartMessageNotQualified(): string
    {
        return (string)$this->scopeConfig->getValue(self::XPATH_CART_TOP_NOT_QUALIFIED_MESSAGE, ScopeInterface::SCOPE_STORE)
            ?: '';
    }

    /**
     * @return string
     */
    public function getTopCartMessageQualified(): string
    {
        return (string)$this->scopeConfig->getValue(self::XPATH_CART_TOP_QUALIFIED_MESSAGE, ScopeInterface::SCOPE_STORE)
            ?: '';
    }

    /**
     * @return array
     */
    public function getTopCartCustomerGroupsForMessages(): array
    {
        $countryList = explode(',', $this->scopeConfig->getValue(self::XPATH_CUSTOMER_GROUPS, ScopeInterface::SCOPE_STORE));
        if ($countryList === false) {
            $countryList = [];
        }

        return $countryList;
    }

    /**
     * @return string
     */
    public function getTopCartModalText(): string
    {
        return (string)$this->scopeConfig->getValue(self::XPATH_CART_TOP_MODAL_TEXT, ScopeInterface::SCOPE_STORE)
            ?: '';
    }

}

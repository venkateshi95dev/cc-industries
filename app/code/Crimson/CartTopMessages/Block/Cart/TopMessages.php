<?php

namespace Crimson\CartTopMessages\Block\Cart;

use Crimson\CartTopMessages\Model\Config as CartTopConfig;
use Magento\Checkout\Model\Session as CartSession;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context as Context;

/**
 * Class TopMessages
 * @package Crimson\CartTopMessages\Block\Cart
 */
class TopMessages extends Template
{

    /**
     * @var CartSession
     */
    protected $cartSession;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var CartTopConfig
     */
    protected $cartTopConfig;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrencyInterface;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepositoryInterface;


    public function __construct(
        Context $context,
        CartSession $cartSession,
        Session $session,
        CartTopConfig $cartTopConfig,
        PriceCurrencyInterface $priceCurrencyInterface,
        GroupRepositoryInterface $groupRepositoryInterface,
        array $data = array()
    ) {
        $this->cartSession = $cartSession;
        $this->session = $session;
        $this->cartTopConfig = $cartTopConfig;
        $this->priceCurrencyInterface = $priceCurrencyInterface;
        $this->groupRepositoryInterface = $groupRepositoryInterface;

        parent::__construct($context, $data);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function checkDiscountResultQualify(): array
    {
        if (!$this->cartTopConfig->isEnabled())
            return [];
        $result = [];
        $quote = $this->cartSession->getQuote();
        if ($quote && $quote->getSubtotal() > 0) {
            $quoteSubTotal = $quote->getSubtotal();
            $discountAmount = $this->cartTopConfig->getDiscountAmount();
            $amount = $discountAmount - $quoteSubTotal;
            $amountToCompare = $this->cartTopConfig->getDiscountAmountNumberToCompare();

            if (!$this->session->isLoggedIn()
                || ($this->session->isLoggedIn() && $this->getCustomerGroupsForMessages())) {

                if ($amount < $amountToCompare && $amount > 0) {
                    $result = [
                        'qualify'         => false,
                        'subtotal'        => $this->priceCurrencyInterface->format($quoteSubTotal, false),
                        'amount'          => $this->priceCurrencyInterface->format($amount, false),
                        'discount_amount' => $this->priceCurrencyInterface->format($discountAmount, false)
                    ];
                } elseif ($amount <= 0) {
                    $result = [
                        'qualify'         => true,
                        'discount_amount' => $this->priceCurrencyInterface->format($discountAmount, false)
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function getCustomerGroupsForMessages(): bool
    {
        $result = false;
        $groupId = $this->session->getCustomer()->getGroupId();
        $customerGroups = $this->cartTopConfig->getTopCartCustomerGroupsForMessages();

        if (!empty($customerGroups) && in_array($groupId, $customerGroups)) {
            $result = true;
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getNotQualifiedMessage(): string
    {
        return $this->cartTopConfig->getTopCartMessageNotQualified();
    }

    /**
     * @return string
     */
    public function getQualifiedMessage(): string
    {
        return $this->cartTopConfig->getTopCartMessageQualified();
    }

    /**
     * @return string
     */
    public function getModalText(): string
    {
        return $this->cartTopConfig->getTopCartModalText();
    }
}

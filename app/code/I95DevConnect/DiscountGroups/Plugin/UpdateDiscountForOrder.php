<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Plugin;

use I95DevConnect\DiscountGroups\Helper\Data;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Psr\Log\LoggerInterface;

class UpdateDiscountForOrder
{
    /**
     * @var QuoteFactory
     */
    protected $quote;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Session
     */
    protected $_checkoutSession; // phpcs:ignore

    /**
     * @var Registry
     */
    protected $_registry; // phpcs:ignore

    public const AMOUNT_SUBTOTAL = 'subtotal';

    /**
     * @var Data
     */
    protected $discountGroupsHelper;

    /**
     * UpdateDiscountForOrder constructor
     *
     * @param Quote $quote
     * @param LoggerInterface $logger
     * @param Session $checkoutSession
     * @param Registry $registry
     * @param Data $discountGroupsHelper
     */
    public function __construct(
        Quote $quote,
        LoggerInterface $logger,
        Session $checkoutSession,
        Registry $registry,
        Data $discountGroupsHelper
    ) {
        $this->quote = $quote;
        $this->logger = $logger;
        $this->_checkoutSession = $checkoutSession;
        $this->_registry = $registry;
        $this->discountGroupsHelper = $discountGroupsHelper;
    }

    /**
     * Get shipping, tax, subtotal and discount amounts all together
     *
     * @param cart $cart
     * @param array $result
     * @return mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterGetAmounts($cart, $result) //NOSONAR
    {
        $total = $result;

        $isEnabled = $this->discountGroupsHelper->isDiscountGroupsEnabled();
        if (!$isEnabled) {
            return $total;
        }

        $currentQuote = $this->_checkoutSession->getQuote();
        $paymentMethod = $currentQuote->getPayment()->getMethod();
        $paypalMehodList = ['payflowpro',
            'payflow_link',
            'payflow_advanced',
            'braintree_paypal',
            'paypal_express_bml',
            'payflow_express_bml',
            'payflow_express',
            'paypal_express'];

        if (in_array($paymentMethod, $paypalMehodList)) {
            $total[self::AMOUNT_SUBTOTAL] = $total[self::AMOUNT_SUBTOTAL] + $currentQuote
                    ->getShippingAddress()
                    ->getDiscountGroupAmount();
        }

        return $total;
    }
}

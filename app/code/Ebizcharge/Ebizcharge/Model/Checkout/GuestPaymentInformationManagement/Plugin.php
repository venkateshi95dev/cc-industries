<?php

/**
 * Century Business Solutions
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the centurybizsolutions.com license that is
 * available through the URL:  https://www.centurybizsolutions/License.txt
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file to upgrade this extension to newer
 * version in the future please contact to CENTURY BUSINESS SOLUTIONS.
 *
 * @category    Ebizcharge
 * @package     Ebizcharge_Ebizcharge
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Model\Checkout\GuestPaymentInformationManagement;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\ConfigFactory;
use Exception;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Checkout\Api\PaymentProcessingRateLimiterInterface;
use Magento\Checkout\Api\PaymentSavingRateLimiterInterface;
use Magento\Checkout\Model\AddressComparatorInterface;
use Magento\Checkout\Model\GuestPaymentInformationManagement;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\GuestBillingAddressManagementInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Api\GuestPaymentMethodManagementInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Psr\Log\LoggerInterface as Logger;

/**
 * Guest Payment Information Management Plugin
 *
 * Class Plugin
 */
class Plugin extends GuestPaymentInformationManagement
{
    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $ebizchargeLogger;
    /**
     * @var ConfigFactory
     */
    protected ConfigFactory $configFactory;

    /**
     * Main Constructor
     *
     * @param GuestBillingAddressManagementInterface $billingAddressManagement
     * @param GuestPaymentMethodManagementInterface $paymentMethodManagement
     * @param GuestCartManagementInterface $cartManagement
     * @param PaymentInformationManagementInterface $paymentInformationManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param CartRepositoryInterface $cartRepository
     * @param Logger $logger
     * @param EbizchargeLogger $ebizchargeLogger
     * @param ConfigFactory $configFactory
     * @param PaymentProcessingRateLimiterInterface|null $paymentsRateLimiter
     * @param PaymentSavingRateLimiterInterface|null $savingRateLimiter
     * @param AddressComparatorInterface|null $addressComparator
     */
    public function __construct(
        GuestBillingAddressManagementInterface $billingAddressManagement,
        GuestPaymentMethodManagementInterface $paymentMethodManagement,
        GuestCartManagementInterface $cartManagement,
        PaymentInformationManagementInterface $paymentInformationManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        CartRepositoryInterface $cartRepository,
        Logger $logger,
        EbizchargeLogger $ebizchargeLogger,
        ConfigFactory $configFactory,
        ?PaymentProcessingRateLimiterInterface $paymentsRateLimiter = null,
        ?PaymentSavingRateLimiterInterface $savingRateLimiter = null,
        ?AddressComparatorInterface $addressComparator = null
    ) {
        parent::__construct(
            $billingAddressManagement,
            $paymentMethodManagement,
            $cartManagement,
            $paymentInformationManagement,
            $quoteIdMaskFactory,
            $cartRepository,
            $logger,
            $paymentsRateLimiter,
            $savingRateLimiter,
            $addressComparator
        );

        /** @var $ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
        /** @var $configFactory */
        $this->configFactory = $configFactory;
    }

    /**
     * Save Payment Information And Place Order
     *
     * @param mixed $cartId
     * @param mixed $email
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return int
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws InvalidTransitionException
     * @throws NoSuchEntityException
     */
    public function savePaymentInformationAndPlaceOrder(
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ): int {
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();

        if (!$configFactory->isActive($storeId)) {
            $this->ebizchargeLogger->addInfo(__("EBizCharge module has been disabled from Configuration."));
            return parent::savePaymentInformationAndPlaceOrder($cartId, $email, $paymentMethod, $billingAddress);
        }
        $this->savePaymentInformation($cartId, $email, $paymentMethod, $billingAddress);
        try {
            $orderId = $this->cartManagement->placeOrder($cartId);
        } catch (Exception $e) {
            throw new CouldNotSaveException(
                __($e->getMessage()),
                $e
            );
        }
        return $orderId;
    }

    /**
     * Save Payment Information
     *
     * @param mixed $cartId
     * @param mixed $email
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return bool
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws InvalidTransitionException
     * @throws CouldNotSaveException
     */
    public function savePaymentInformation(
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ): bool {
        parent::savePaymentInformation($cartId, $email, $paymentMethod, $billingAddress);

        /** Billing Address */
        if ($billingAddress) {
            $billingAddress->setEmail($email);
            $this->billingAddressManagement->assign($cartId, $billingAddress);
            $this->paymentMethodManagement->set($cartId, $paymentMethod);
        } else {
            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
            $this->cartRepository->getActive($quoteIdMask->getQuoteId())->getBillingAddress()->setEmail($email);
            $this->paymentMethodManagement->set($cartId, $paymentMethod);
        }
        return true;
    }
}

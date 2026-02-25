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

namespace Ebizcharge\Ebizcharge\Model\Checkout\PaymentInformationManagement;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\ConfigFactory;
use Exception;
use Magento\Checkout\Api\PaymentProcessingRateLimiterInterface;
use Magento\Checkout\Api\PaymentSavingRateLimiterInterface;
use Magento\Checkout\Model\AddressComparatorInterface;
use Magento\Checkout\Model\PaymentDetailsFactory;
use Magento\Checkout\Model\PaymentInformationManagement;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Quote\Api\BillingAddressManagementInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Psr\Log\LoggerInterface;

/**
 * Payment Information Management
 *
 * Class Plugin
 */
class Plugin extends PaymentInformationManagement
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
     * @param BillingAddressManagementInterface $billingAddressManagement
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     * @param CartManagementInterface $cartManagement
     * @param PaymentDetailsFactory $paymentDetailsFactory
     * @param CartTotalRepositoryInterface $cartTotalsRepository
     * @param EbizchargeLogger $ebizchargeLogger
     * @param ConfigFactory $configFactory
     * @param PaymentProcessingRateLimiterInterface|null $paymentRateLimiter
     * @param PaymentSavingRateLimiterInterface|null $saveRateLimiter
     * @param CartRepositoryInterface|null $cartRepository
     * @param AddressRepositoryInterface|null $addressRepository
     * @param AddressComparatorInterface|null $addressComparator
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        BillingAddressManagementInterface $billingAddressManagement,
        PaymentMethodManagementInterface $paymentMethodManagement,
        CartManagementInterface $cartManagement,
        PaymentDetailsFactory $paymentDetailsFactory,
        CartTotalRepositoryInterface $cartTotalsRepository,
        EbizchargeLogger $ebizchargeLogger,
        ConfigFactory $configFactory,
        ?PaymentProcessingRateLimiterInterface $paymentRateLimiter = null,
        ?PaymentSavingRateLimiterInterface $saveRateLimiter = null,
        ?CartRepositoryInterface $cartRepository = null,
        ?AddressRepositoryInterface $addressRepository = null,
        ?AddressComparatorInterface $addressComparator = null,
        ?LoggerInterface $logger = null
    ) {
        parent::__construct(
            $billingAddressManagement,
            $paymentMethodManagement,
            $cartManagement,
            $paymentDetailsFactory,
            $cartTotalsRepository,
            $paymentRateLimiter,
            $saveRateLimiter,
            $cartRepository,
            $addressRepository,
            $addressComparator,
            $logger
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
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return int
     * @throws CouldNotSaveException|NoSuchEntityException
     */
    public function savePaymentInformationAndPlaceOrder(
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ): int {
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();

        if (!$configFactory->isActive($storeId)) {
            $this->ebizchargeLogger->addInfo(__("EBizCharge module has been disabled from Configuration."));
            return parent::savePaymentInformationAndPlaceOrder($cartId, $paymentMethod, $billingAddress);
        }

        try {
            $this->savePaymentInformation($cartId, $paymentMethod, $billingAddress);
        } catch (InputException $e) {
            throw new CouldNotSaveException(
                __($e->getMessage()),
                $e
            );
        } catch (NoSuchEntityException $e) {
            $this->ebizchargeLogger->addCritical(__("Place order with cart ID: " . $cartId .
                " payment method no such entity exception: " . $e->getMessage()));
            throw new CouldNotSaveException(
                __($e->getMessage()),
                $e
            );
        } catch (InvalidTransitionException $e) {
            $this->ebizchargeLogger->addCritical(__("Place order with cart ID: " . $cartId .
                " invalid transaction exception exception: " . $e->getMessage()));

            throw new CouldNotSaveException(
                __($e->getMessage()),
                $e
            );
        }

        try {
            $orderId = $this->cartManagement->placeOrder($cartId);

        } catch (Exception $e) {
            $this->ebizchargeLogger->addCritical(__("Place order with cart ID: " . $cartId .
                " exception: " . $e->getMessage()));

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
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return bool
     */
    public function savePaymentInformation(
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ): bool {
        try {
            parent::savePaymentInformation($cartId, $paymentMethod, $billingAddress);
            if ($billingAddress) {
                $this->billingAddressManagement->assign($cartId, $billingAddress);
                $this->paymentMethodManagement->set($cartId, $paymentMethod);
            }
        } catch (CouldNotSaveException | LocalizedException $e) {
            $this->ebizchargeLogger->addCritical(__("Could not save Billing address " . $e->getMessage()));
            return false;
        }
        return true;
    }
}

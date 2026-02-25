<?php

namespace Crimson\Payware\Model;

use Crimson\MachBase\Model\MachConfig;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Checkout\Api\PaymentProcessingRateLimiterInterface;
use Magento\Checkout\Api\PaymentSavingRateLimiterInterface;
use Magento\Checkout\Model\GuestPaymentInformationManagement;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\GuestBillingAddressManagementInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Api\GuestPaymentMethodManagementInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class GuestPaymentInformationManagementCustom
 * @package Crimson\Payware\Model
 */
class GuestPaymentInformationManagementCustom extends GuestPaymentInformationManagement
{

    /**
     * @var PaymentProcessingRateLimiterInterface
     */
    private $paymentsRateLimiter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $saveRateLimitDisabled = false;

    public function __construct(
        GuestBillingAddressManagementInterface $billingAddressManagement,
        GuestPaymentMethodManagementInterface $paymentMethodManagement,
        GuestCartManagementInterface $cartManagement,
        PaymentInformationManagementInterface $paymentInformationManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        LoggerInterface $logger,
        CartRepositoryInterface $cartRepository,
        protected StoreManagerInterface $storeManager,
        ?PaymentProcessingRateLimiterInterface $paymentsRateLimiter = null,
        ?PaymentSavingRateLimiterInterface $savingRateLimiter = null
    )
    {
        parent::__construct($billingAddressManagement, $paymentMethodManagement, $cartManagement, $paymentInformationManagement, $quoteIdMaskFactory, $cartRepository, $logger, $paymentsRateLimiter, $savingRateLimiter);
        $this->paymentsRateLimiter = $paymentsRateLimiter
            ?? ObjectManager::getInstance()->get(PaymentProcessingRateLimiterInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function savePaymentInformationAndPlaceOrder(
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        if ($this->storeManager->getWebsite()->getCode() != MachConfig::ZIP_WEBSITE_CODE) {
            return parent::savePaymentInformationAndPlaceOrder($cartId, $email, $paymentMethod, $billingAddress);
        }

        $this->paymentsRateLimiter->limit();
        try {
            //Have to do this hack because of savePaymentInformation() plugins.
            $this->saveRateLimitDisabled = true;
            $this->savePaymentInformation($cartId, $email, $paymentMethod, $billingAddress);
        } finally {
            $this->saveRateLimitDisabled = false;
        }
        try {
            $orderId = $this->cartManagement->placeOrder($cartId);
        } catch (LocalizedException $e) {
            $this->getLogger()->critical(
                'Placing an order with quote_id ' . $cartId . ' is failed: ' . $e->getMessage()
            );
            throw new CouldNotSaveException(
                __($e->getMessage()),
                $e
            );
        } catch (\Exception $e) {
            $this->getLogger()->critical($e);
            throw new CouldNotSaveException(
                __('Invalid credit card. Please try to place the order again.'),
                $e
            );
        }

        return $orderId;
    }

    /**
     * Get logger instance
     *
     * @return LoggerInterface
     * @deprecated 100.1.8
     */
    private function getLogger()
    {
        if (!$this->logger) {
            $this->logger = ObjectManager::getInstance()->get(LoggerInterface::class);
        }
        return $this->logger;
    }
}

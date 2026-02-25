<?php

namespace Crimson\Payware\Model;

use Crimson\MachBase\Model\MachConfig;
use Magento\Checkout\Api\PaymentProcessingRateLimiterInterface;
use Magento\Checkout\Api\PaymentSavingRateLimiterInterface;
use Magento\Checkout\Model\PaymentDetailsFactory;
use Magento\Checkout\Model\PaymentInformationManagement;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\BillingAddressManagementInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class PaymentInformationManagementCustom
 * @package Crimson\Payware\Model
 */
class PaymentInformationManagementCustom extends PaymentInformationManagement
{

    /**
     * @var PaymentProcessingRateLimiterInterface
     */
    private $paymentRateLimiter;

    /**
     * @var bool
     */
    private $saveRateLimiterDisabled = false;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        BillingAddressManagementInterface $billingAddressManagement,
        PaymentMethodManagementInterface $paymentMethodManagement,
        CartManagementInterface $cartManagement,
        PaymentDetailsFactory $paymentDetailsFactory,
        CartTotalRepositoryInterface $cartTotalsRepository,
        protected StoreManagerInterface $storeManager,
        ?PaymentProcessingRateLimiterInterface $paymentRateLimiter = null,
        ?PaymentSavingRateLimiterInterface $saveRateLimiter = null
    )
    {
        parent::__construct($billingAddressManagement, $paymentMethodManagement, $cartManagement, $paymentDetailsFactory, $cartTotalsRepository, $paymentRateLimiter, $saveRateLimiter);
        $this->paymentRateLimiter = $paymentRateLimiter
            ?? ObjectManager::getInstance()->get(PaymentProcessingRateLimiterInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function savePaymentInformationAndPlaceOrder(
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        if ($this->storeManager->getWebsite()->getCode() != MachConfig::ZIP_WEBSITE_CODE) {
            return parent::savePaymentInformationAndPlaceOrder($cartId, $paymentMethod, $billingAddress);
        }

        $this->paymentRateLimiter->limit();
        try {
            //Have to do this hack because of plugins for savePaymentInformation()
            $this->saveRateLimiterDisabled = true;
            $this->savePaymentInformation($cartId, $paymentMethod, $billingAddress);
        } finally {
            $this->saveRateLimiterDisabled = false;
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

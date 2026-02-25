<?php

/**
 * Century Business Solutions.
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
 *
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 *
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Ui;

use Ebizcharge\Ebizcharge\Api\Data\ConfigModelInterface;
use Ebizcharge\Ebizcharge\Api\Data\PaymentInterface;
use Ebizcharge\Ebizcharge\Api\Data\SurchargeInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\ConfigFactory;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Ebizcharge\Ebizcharge\Model\OrderFactory;
use Ebizcharge\Ebizcharge\Model\Payment;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\MethodInterface;

/**
 * Checkout config provider for Ebizcharge.
 *
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * Code for the Payment Gateway.
     *
     * @const CODE
     */
    public const CODE = 'ebizcharge_ebizcharge';

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $customerFactory;
    /**
     * @var OrderFactory
     */
    protected OrderFactory $orderFactory;
    /**
     * @var CheckoutSession
     */
    protected CheckoutSession $checkoutSession;
    /**
     * @var ConfigFactory
     */
    private ConfigFactory $configFactory;
    /**
     * @var PaymentHelper
     */
    private PaymentHelper $paymentHelper;
    /**
     * @var Payment
     */
    private Payment $ebizPayment;
    /**
     * @var EbizchargeLogger
     */
    private EbizchargeLogger $ebizchargeLogger;
    /**
     * @var Cart
     */
    private Cart $cart;

    /**
     * @param Payment $ebizPayment
     * @param ConfigFactory $configFactory
     * @param CustomerFactory $customerFactory
     * @param Cart $cart
     * @param OrderFactory $orderFactory
     * @param PaymentHelper $paymentHelper
     * @param CheckoutSession $checkoutSession
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        Payment $ebizPayment,
        ConfigFactory $configFactory,
        CustomerFactory $customerFactory,
        Cart $cart,
        OrderFactory $orderFactory,
        PaymentHelper $paymentHelper,
        CheckoutSession $checkoutSession,
        EbizchargeLogger $ebizchargeLogger
    ) {
        // @var $configFactory
        $this->configFactory = $configFactory;
        // @var $paymentHelper
        $this->paymentHelper = $paymentHelper;
        // @var $ebizPayment
        $this->ebizPayment = $ebizPayment;
        // @var $ebizchargeLogger
        $this->ebizchargeLogger = $ebizchargeLogger;
        // @var $cart
        $this->cart = $cart;
        // @var $customerFactory
        $this->customerFactory = $customerFactory;
        // @var $orderFactory
        $this->orderFactory = $orderFactory;
        // Checkout Session
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Get Config.
     *
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getConfig(): array
    {
        $config = [];
        $customerFactory = $this->customerFactory->create();
        $configFactory = $this->configFactory->create();
        $storeId = $this->ebizPayment->getStoreId() ?? $configFactory->getStoreId();

        if (!$configFactory->isActive($storeId)) {
            $this->ebizchargeLogger->addInfo(__('In config Payment is not active'));

            return $config;
        }
        // $ebizMethodDetails = $this->getEbizMethodDetails();

        $phpSessionID = $this->checkoutSession->getSessionId() ?? '';
        $sessionIdParams = [
            'TransactionLookupKey' => $phpSessionID,
            'PHPSESSID' => $phpSessionID,
        ];
        // $merchantTransactionData = $this->customerFactory->create()->getMerchantTransactionData();
        $surchargeSettings = $customerFactory->getSurchargeSettings($storeId);
        // $checkoutPaymentWebformUrl = $this->orderFactory->create()->renderCheckoutWebHostedProFormUrl($storeId);
        $checkoutPaymentWebformUrl = '';

        $ebizSecurityToken = [];
        $is3DSecureEnabled = $customerFactory->is3DSecureEnabled($storeId);
        $sourceKey = $configFactory->getSourceKey($storeId);
        $quoteReservedOrderId = $this->getQuoteReservedOrderId();
        $isCustomerToken = $this->ebizPayment->hasToken($storeId);
        $ebizPaymentParams = [
            'payment' => [
                'ebizcharge' => [
                    'sourcekey' => $sourceKey,
                    'getDeleteURL' => $configFactory->getDeleteURL($storeId),
                    'useVault' => $isCustomerToken,
                    'getEbzcCustId' => $this->ebizPayment->getEbzcCustId(),
                    'storedCards' => $this->ebizPayment->getSavedCards($storeId),
                    'showSavedUpdatedCardsOnCheckout' => $configFactory->showSavedMethodsCheckout($storeId),
                    'storedAccounts' => $this->ebizPayment->getSavedBankAccounts($storeId),
                    'saveCard' => 1 == $configFactory->getPaymentSavePayment($storeId),
                    'config_save_bank_accounts' => 1 == $configFactory->getIsSaveBankAccounts($storeId),
                    'config_save_card' => 1 == $configFactory->getIsSaveCreditCards($storeId),
                    'requestCardCode' => 1 == $configFactory->getRequestCardCode($storeId),
                    'isAchActive' => $configFactory->isAchActive($storeId),
                    'isCreditCardActive' => $configFactory->isCreditCardEnabled($storeId),
                    'getAchAccountTypes' => ['checking', 'savings'],
                    'isRecurringEnabled' => $configFactory->isRecurringEnabled($storeId),
                    'hasToken' => $isCustomerToken,
                    'code' => PaymentInterface::CODE,
                    'isAvsCvvZipEnabled' => $configFactory->isAvsCvvZipEnabled($storeId),
                    'cardAvsCvvValidationUrl' => $configFactory->getCardAVSValidationURL(["store_id"=>$storeId]),
                    'surchargeEnabled' => $surchargeSettings[SurchargeInterface::EBIZ_SURCHARGE_ENABLED]
                        ?? $customerFactory->isSurchargeEnabled($storeId),
                    'surchargeSettings' => $surchargeSettings,
                    'isSurchargeCalculated' => 0,
                    'surchargeGrandTotal' => 0,
                    'calculateSurchargeAjaxUrl' => $configFactory->getCalculateSurchargeAjaxUrl(["store_id"=>$storeId]),
                    'surchargeSessionDataUrl' => $configFactory->getSurchargeSessionDataUrl(["store_id"=>$storeId]),
                    'surchargeTypeDailyDiscount' => SurchargeInterface::EBIZ_SURCHARGE_TYPE_ID_DAILY_DISCOUNT,
                    'ebiz3DSecureEnabled' => $is3DSecureEnabled,
                    'ebizSecurityToken' => $ebizSecurityToken,
                    'quoteReservedOrderId' => $quoteReservedOrderId,
                    'save3DSecureDataUrl' => $configFactory->getSave3DSecureDataUrl(["store_id"=>$storeId]),
                    'paymentFormType' => $configFactory->getPaymentFormType($storeId),
                    'tokenizeCardsOnly' => $configFactory->IsCardsTokenizeOnly($storeId),
                    'placeSubscriptionsUrl' => $configFactory->getPlaceSubscriptionsUrl(),
                    'isSameBillingShippingAddresses' => $configFactory->isSameBillingAndShippingAddresses($storeId),
                    'successPageUrl' => $configFactory->getSuccessPageUrl(["store_id"=>$storeId]),
                    // 'checkoutWebHostedFormUrl' => $configFactory->getCheckoutWebHostedFormUrl($sessionIdParams),
                    'checkoutWebHostedFormUrl' => '',
                    'logoUrl' => $configFactory->getLogoUrl($storeId),
                    'cartUrl' => $configFactory->getCartUrl($storeId),
                    'recaptcha' => $configFactory->getRecaptchaForCheckout($storeId),
                    'recaptchaUrl' => ConfigModelInterface::GOOGLE_RECAPTCHA_VERIFY_URL,
                ],
            ],
        ];

        return array_merge_recursive($config, $ebizPaymentParams);
    }

    /**
     * Get Quote Reserved Order Id.
     *
     * @return null|mixed|string
     */
    private function getQuoteReservedOrderId()
    {
        $quoteReservedOrderId = '';

        try {
            $quoteReservedOrderId = $this->cart->getQuote()->getReservedOrderId();
            if (!$quoteReservedOrderId) {
                $quote = $this->cart->getQuote()->reserveOrderId()->save();
                $quoteReservedOrderId = $quote->getReservedOrderId();
            }
        } catch (\Exception $exception) {
            $this->ebizchargeLogger->addCritical($exception->getMessage());
        }

        return $quoteReservedOrderId;
    }

    /**
     * Get Ebizcharge Method Detail.
     */
    private function getEbizMethodDetails(): ?MethodInterface
    {
        try {
            return $this->paymentHelper->getMethodInstance(PaymentInterface::CODE);
        } catch (\Exception $e) {
            $this->ebizchargeLogger->addCritical(__(
                'Exception occurred during getting method instance Error:'.$e->getMessage()
            ));

            return null;
        }
    }
}

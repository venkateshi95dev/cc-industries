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


namespace Ebizcharge\Ebizcharge\Plugin\Checkout\Model;

use Closure;
use Ebizcharge\Ebizcharge\Api\Data\ConfigModelInterface;
use Ebizcharge\Ebizcharge\Api\Data\PaymentInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\ConfigFactory as EbizConfigFactory;
use JsonException;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Validation\ValidationResult;
use Magento\Payment\Model\PaymentMethodList;
use Magento\ReCaptchaUi\Model\IsCaptchaEnabled;
use Magento\ReCaptchaValidation\Model\ReCaptchaFactory;
use Magento\ReCaptchaValidation\Model\Validator;
use Magento\ReCaptchaValidationApi\Api\Data\ValidationConfigInterface;
use Magento\ReCaptchaValidationApi\Model\ErrorMessagesProvider;
use ReCaptcha\ReCaptcha;
use Magento\Framework\App\RequestInterface;

/**
 * Enable ReCaptcha validation for REST ful web API.
 */
class RestValidationPlugin
{

    /**
     * @var IsCaptchaEnabled
     */
    protected IsCaptchaEnabled $isCaptchaEnabled;
    /**
     * @var EbizConfigFactory
     */
    protected EbizConfigFactory $ebizConfigFactory;
    /**
     * @var Curl
     */
    protected Curl $curl;
    /**
     * @var CheckoutSession
     */
    protected CheckoutSession $checkoutSession;
    /**
     * @var ErrorMessagesProvider
     */
    private ErrorMessagesProvider $errorMessagesProvider;
    /**
     * @var ReCaptchaFactory
     */
    private ReCaptchaFactory $reCaptchaFactory;
    /**
     * @var EbizchargeLogger
     */
    private EbizchargeLogger $ebizchargeLogger;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $requestInterface;

    /**
     * @param ErrorMessagesProvider $errorMessagesProvider
     * @param IsCaptchaEnabled $isCaptchaEnabled
     * @param EbizConfigFactory $ebizConfigFactory
     * @param CheckoutSession $checkoutSession
     * @param EbizchargeLogger $ebizchargeLogger
     * @param RequestInterface $requestInterface
     * @param Curl $curl
     */
    public function __construct(
        ErrorMessagesProvider $errorMessagesProvider,
        IsCaptchaEnabled      $isCaptchaEnabled,
        EbizConfigFactory     $ebizConfigFactory,
        CheckoutSession       $checkoutSession,
        EbizchargeLogger      $ebizchargeLogger,
        RequestInterface      $requestInterface,
        Curl                  $curl
    )
    {

        $this->errorMessagesProvider = $errorMessagesProvider;
        $this->isCaptchaEnabled = $isCaptchaEnabled;
        $this->ebizConfigFactory = $ebizConfigFactory;
        $this->curl = $curl;
        $this->ebizchargeLogger = $ebizchargeLogger;
        $this->checkoutSession = $checkoutSession;
        $this->requestInterface = $requestInterface;
    }


    /**
     * @param Validator $validator
     * @param Closure $proceed
     * @param string $reCaptchaResponse
     * @param ValidationConfigInterface $validationConfig
     * @return ValidationResult
     * @throws LocalizedException
     * @throws JsonException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundIsValid(
        Validator                 $validator,
        Closure                   $proceed,
        string                    $reCaptchaResponse,
        ValidationConfigInterface $validationConfig
    ): ValidationResult
    {
        $proceed($reCaptchaResponse, $validationConfig);

        /** @var ReCaptcha $reCaptcha */
        $validationErrors = [];
        $configFactory = $this->ebizConfigFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);

        if ($isEbizChargeActive) {
            $recaptchaEnabledForCheckout = $configFactory->getRecaptchaForCheckout();

            $this->ebizchargeLogger->addInfo(__("Checkout recaptcha is enabled " . $recaptchaEnabledForCheckout));

            if (!empty($recaptchaEnabledForCheckout)) {

                $postData = [
                    'secret' => $validationConfig->getPrivateKey(),
                    'response' => $reCaptchaResponse
                ];

                $this->ebizchargeLogger->addInfo(__("Sending request to captcha for validation. " . $reCaptchaResponse));
                //  dump( $this->requestInterface->getParams(), $reCaptchaResponse,  $this->checkoutSession->getQuote()->getPayment()->getMethod());throw new LocalizedException(__("excption"));
                $this->curl->post(ConfigModelInterface::GOOGLE_RECAPTCHA_VERIFY_URL, http_build_query($postData));
                $reeCaptchaResponse = json_decode($this->curl->getBody(), false, 512, JSON_THROW_ON_ERROR);
                $validationResult = $proceed($reCaptchaResponse, $validationConfig);

                foreach ($validationResult->getErrors() as $errorCode) {
                    $validationErrors[$errorCode] = $this->errorMessagesProvider->getErrorMessage($errorCode);
                }
                // 'score-threshold-not-met' error is present in response even if some technical issue happened.
                if (count($validationErrors) > 0) {
                    unset($validationErrors[ReCaptcha::E_SCORE_THRESHOLD_NOT_MET]);
                    $validationErrors = [];
                }
            }
        }
        // return $this->validationResultFactory->create(['errors' => $validationErrors]);
        return new ValidationResult($validationErrors);
    }

    /**
     * @param PaymentMethodList $subject
     * @param array $paymentMethodList
     * @param $storeId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterGetActiveList(PaymentMethodList $subject, array $paymentMethodList, $storeId = null): array
    {
        $configFactory = $this->ebizConfigFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);

        if ($isEbizChargeActive) {
            $quote = $this->checkoutSession->getQuote();
            $billingCountry = $quote->getBillingAddress() ? $quote->getBillingAddress()->getCountryId() : null;
            $shippingCountry = $quote->getBillingAddress() ? $quote->getShippingAddress()->getCountryId() : null;
            $isSpecificCountryAllowed = $this->ebizConfigFactory->create()->isSpecificCountryAllowed($storeId);
            $specificAllowedCountries = $this->ebizConfigFactory->create()->getSpecificAllowedCountries($storeId);
            $allowedPaymentMethods = [];


            if (count($paymentMethodList) > 0) {
                foreach ($paymentMethodList as $paymentMethod) {
                    if ($paymentMethod->getCode() === PaymentInterface::EBIZCHARGE_PAYMENT_METHOD) {
                        if ($isSpecificCountryAllowed) {
                            $specificAllowedCountries = explode(",", $specificAllowedCountries);
                            if (!in_array($billingCountry, $specificAllowedCountries, true)
                                || !in_array($shippingCountry, $specificAllowedCountries, true)
                            ) {
                                continue;
                            }
                        }
                    }
                    $allowedPaymentMethods[] = $paymentMethod;
                }
            }
            $paymentMethodList = $allowedPaymentMethods;
        }

        return $paymentMethodList;
    }


}

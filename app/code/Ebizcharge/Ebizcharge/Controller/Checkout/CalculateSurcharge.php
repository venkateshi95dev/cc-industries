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

namespace Ebizcharge\Ebizcharge\Controller\Checkout;


use Ebizcharge\Ebizcharge\Api\Data\SurchargeInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Ebizcharge\Ebizcharge\Model\RecurringFactory;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Helper\Data;

/**
 * Ajax call to calculate cart surcharge
 *
 * Class CalculateSurcharge
 */
class CalculateSurcharge extends Action
{
    /**
     * @var RecurringFactory
     */
    protected RecurringFactory $recurringFactory;
    /**
     * @var TranApi
     */
    private TranApi $soapApiModel;
    /**
     * @var JsonFactory
     */
    private JsonFactory $jsonFactory;
    /**
     * @var CheckoutSession
     */
    private CheckoutSession $checkoutSession;
    /**
     * @var Data
     */
    private Data $priceHelper;
    /**
     * @var EbizchargeLogger
     */
    private EbizchargeLogger $ebizchargeLogger;
    /**
     * @var CustomerFactory
     */
    private CustomerFactory $customerFactory;

    /**
     * @param Data $priceHelper
     * @param Context $context
     * @param TranApi $soapApiModel
     * @param JsonFactory $jsonFactory
     * @param CheckoutSession $checkoutSession
     * @param EbizchargeLogger $ebizchargeLogger
     * @param RecurringFactory $recurringFactory
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        Data             $priceHelper,
        Context          $context,
        TranApi          $soapApiModel,
        JsonFactory      $jsonFactory,
        CheckoutSession  $checkoutSession,
        EbizchargeLogger $ebizchargeLogger,
        RecurringFactory $recurringFactory,
        CustomerFactory  $customerFactory
    )
    {
        /** Parent Constructor */
        parent::__construct($context);

        /** @var  priceHelper */
        $this->priceHelper = $priceHelper;
        /** @var  jsonFactory */
        $this->jsonFactory = $jsonFactory;
        /** @var  soapApiModel */
        $this->soapApiModel = $soapApiModel;
        /** @var  checkoutSession */
        $this->checkoutSession = $checkoutSession;
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;

        $this->customerFactory = $customerFactory;

        $this->recurringFactory = $recurringFactory;

    }

    /**
     * To check and calculate surcharge on cart
     *
     * @return ResponseInterface|Json|ResultInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $jsonFactory = $this->jsonFactory->create();
        $response = SurchargeInterface::DEFAULT_AJAX_CALCULATE_SURCHARGE_RESPONSE;

        if (!$this->_request->isAjax()) {
            $jsonFactory->setData($response);
            return $jsonFactory;
        }
        $response = [
            SurchargeInterface::EBIZ_SURCHARGE_AMOUNT => 0,
            SurchargeInterface::EBIZ_SURCHARGE_PERCENTAGE => 0,
            SurchargeInterface::EBIZ_SURCHARGE_PERCENTAGE_TEXT => "",
            SurchargeInterface::EBIZ_SURCHARGE_AMOUNT_WITH_SIGN => 0
        ];
        $quote = $this->checkoutSession->getQuote() ?? null;
        if ($quote) {
            $quote->setEcSurchargeAmount(0);
            $quote->setEcSurchargePercentage(0);
            $quote->setEcSurchargeIneligible(0);
            $quote->save()
            ;
        }


        try {

            /** Request params */
            $paymentParams = $this->getRequest()->getParam('payment');
            $paymentMethodId = isset($paymentParams['ebzc_method_id']) ? $paymentParams['ebzc_method_id'] : "";
            $cardNumber = isset($paymentParams['cc_number']) ? $paymentParams['cc_number'] : "";
            $customerId = $quote->getCustomerId() ?? "";
            $customerInternalId = isset($paymentParams['ebzc_cust_id']) ? $paymentParams['ebzc_cust_id'] : "";

            if ($customerId) {
                $customer = $this->customerFactory->create()->load($customerId);
                if ($customer && $customer->getId()) {
                    $customerInternalId = $customer->getEcCustInternalId() ?? "";
                }
            }

            if (!empty($paymentMethodId)) {
                $cardNumber = "";
            }

            /** API params */
            $params = [
                'amount' => (string)$this->checkoutSession->getQuote()->getGrandTotal(),
                'cardNumber' => $cardNumber,
                'ccType' => isset($paymentParams['cc_type']) ? $paymentParams['cc_type'] : "",
                'ccOwner' => isset($paymentParams['cc_owner']) ? $paymentParams['cc_owner'] : "",
                'ccExpMonth' => isset($paymentParams['cc_exp_month']) ? $paymentParams['cc_exp_month'] : "",
                'ccExpYear' => isset($paymentParams['cc_exp_year']) ? $paymentParams['cc_exp_year'] : "",
                'cardZipCode' => isset($paymentParams['cc_avs_zip']) ? $paymentParams['cc_avs_zip'] : "",
                'customerInternalId' => $customerInternalId,
                'ccAvsStreet' => isset($paymentParams['cc_avs_street']) ? $paymentParams['cc_avs_street'] : "",
                'paymentMethodId' => $paymentMethodId,
            ];


            if (!empty($params["cardNumber"]) || (!empty($params["paymentMethodId"]) && !empty($params["cardZipCode"]))) {
                /** Unset previous surcharge data */
                $this->checkoutSession->unsSurchargeData();

                /** Calculate surcharge response */
                $response = $this->soapApiModel->calculateSurchargeAmount($params);

                /** If surcharge is enabled then set data in session */
                if (isset($response[SurchargeInterface::EBIZ_SURCHARGE_ENABLED]) &&
                    $response[SurchargeInterface::EBIZ_SURCHARGE_ENABLED]) {
                    /** Surcharge Data */
                    $surchargeAmount = $response[SurchargeInterface::EBIZ_SURCHARGE_AMOUNT] ?? null;
                    $ineligible = $response[SurchargeInterface::EBIZ_SURCHARGE_INELIGIBLE] ?? false;
                    $surchargePercentage = isset($response[SurchargeInterface::EBIZ_SURCHARGE_PERCENTAGE]) ?
                        $response[SurchargeInterface::EBIZ_SURCHARGE_PERCENTAGE] : 0;

                    $surchargeAmount = !$ineligible ?
                        $surchargeAmount : 0;
                    $surchargeAmountWithSign = $surchargeAmount !== null ? $this->priceHelper->currency(
                        $surchargeAmount,
                        true,
                        false
                    ) : null;

                    /** Surcharge Session Data */
                    $response[SurchargeInterface::EBIZ_SURCHARGE_AMOUNT] = $surchargeAmount;
                    $response[SurchargeInterface::EBIZ_SURCHARGE_PERCENTAGE] =
                        isset($response[SurchargeInterface::EBIZ_SURCHARGE_PERCENTAGE]) ?
                            $response[SurchargeInterface::EBIZ_SURCHARGE_PERCENTAGE] . '%' : null;
                    $response[SurchargeInterface::EBIZ_SURCHARGE_AMOUNT_WITH_SIGN] = $surchargeAmountWithSign;

                    //$surchargeSessionData = [
                    //    'surchargeAmount' => $surchargeAmount,
                    //    'surchargeCaption' => $response['surchargeCaption'] ?? '',
                    //    'surchargePercentage' => isset($response['surchargePercentage']) ?
                    //        $response['surchargePercentage'] . '%' : null,
                    //    'surchargeAmountWithSign' => $surchargeAmountWithSign
                    //];
                    $this->recurringFactory->create()->setRecurringAndSurchargeToQuoteItem($quote, (float)$surchargeAmount, (float)$surchargePercentage);

                    $this->checkoutSession->setSurchargeData($response);
                    $jsonFactory->setData($response);
                }
            }


        } catch (Exception $exception) {
            /** Unset previous surcharge data */
            $this->checkoutSession->unsSurchargeData();

            if ($quote) {
                $quote->setEcSurchargeAmount(0);
                $quote->setEcSurchargePercentage(0);
                $quote->setEcSurchargeIneligible(0);
                $quote->save();
            }
            $response = SurchargeInterface::DEFAULT_AJAX_CALCULATE_SURCHARGE_RESPONSE;
            $jsonFactory->setData($response);
            $this->ebizchargeLogger->addCritical(__("Surcharge Exception. Error:" . $exception->getMessage()));
            /** Unset previous surcharge data */
            $this->checkoutSession->unsSurchargeData();
        }


        return $jsonFactory;
    }
}

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

namespace Ebizcharge\Ebizcharge\Observer;

use Ebizcharge\Ebizcharge\Api\Data\ConfigModelInterface;
use Ebizcharge\Ebizcharge\Api\Data\GraphQL\GraphQLInterface;
use Ebizcharge\Ebizcharge\Api\Data\PaymentInterface as EbizPaymentInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\ConfigFactory;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Ebizcharge\Ebizcharge\Model\Payment;
use Ebizcharge\Ebizcharge\Model\TranApiFactory as SoapApiFactory;
use Ebizcharge\Ebizcharge\Helper\Data as EbizHelper;
use Exception;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Model\QuoteFactory;

/**
 * This class assigns preliminary form data to $infoInstance
 *
 * Class PaymentDataAssignObserver
 */
class PaymentDataAssignObserver extends AbstractDataAssignObserver
{


    protected SoapApiFactory $soapApiFactory;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $_requestInterface;

    /**
     * @var QuoteFactory
     */
    protected QuoteFactory $_quoteFactory;

    /**
     * @var Payment
     */
    protected Payment $_ebizPaymentModel;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var ConfigFactory
     */
    protected ConfigFactory $configFactory;

    /**
     * @var string
     */
    protected string $_paymentOption = "";

    /**
     * @var string
     */
    protected string $_paymentOptionType = "";

    /**
     * @var array
     */
    protected array $paymentData = [];

    /**
     * @var RequestInterface
     */
    protected RequestInterface $_request;
    /**
     * @var Card Type
     */
    private $cardType;


    /**
     * @param SoapApiFactory $soapApiFactory
     * @param CustomerFactory $customerFactory
     * @param RequestInterface $requestInterface
     * @param ConfigFactory $configFactory
     * @param QuoteFactory $quoteFactory
     * @param Payment $ebizPaymentModel
     * @param EbizchargeLogger $ebizchargeLogger
     * @param RequestInterface $request
     */
    public function __construct(
        SoapApiFactory   $soapApiFactory,
        CustomerFactory  $customerFactory,
        RequestInterface $requestInterface,
        ConfigFactory    $configFactory,
        QuoteFactory     $quoteFactory,
        Payment          $ebizPaymentModel,
        EbizchargeLogger $ebizchargeLogger,
        RequestInterface $request
    )
    {
        /** @var $soapApiFactory */
        $this->soapApiFactory = $soapApiFactory;
        /** @var $_ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var $_customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var $_requestInterface */
        $this->_requestInterface = $requestInterface;
        /** @var $_quoteFactory */
        $this->_quoteFactory = $quoteFactory;
        /** @var $_ebizPaymentModel */
        $this->_ebizPaymentModel = $ebizPaymentModel;
        /** @var $configFactory */
        $this->configFactory = $configFactory;
        /** @var $_request */
        $this->_request = $request;
    }

    /**
     * Execute Method
     *
     * @param Observer $observer
     * @return PaymentDataAssignObserver
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);

        if($isEbizChargeActive) {
            $payment = $observer->getPaymentModel();
            $data = $this->readDataArgument($observer);
            $quoteId = $payment->getData('quote_id');

            $additionalData = $data->getData(EbizPaymentInterface::KEY_ADDITIONAL_DATA);
            $additionalData['quote_id'] = isset($quoteId) ? $quoteId : 0;
            $additionalData['save_card_anyway'] = $this->_request->getParam('save_card_anyway') ? 1 : 0;
            //$additionalData['ebzc_avs_zip'] = $this->_request->getParam('save_card_anyway') ? 1: 0;

            if (!is_array($additionalData) || empty($additionalData)) {
                return $this;
            }
            $additionalData = new DataObject($additionalData);
            $paymentMethod = $this->readMethodArgument($observer);

            if (!$payment instanceof InfoInterface) {
                $payment = $paymentMethod->getInfoInstance();
            }

            if (!$payment instanceof InfoInterface) {
                throw new LocalizedException(__('Payment model does not provided.'));
            }

            $paymentInfo = $this->readPaymentModelArgument($observer);
            $paymentInfos = $paymentInfo->getAdditionalInformation();
            $graphQLData = [];

            foreach ($paymentInfos as $pKey => $pData) {
                if (str_contains($pKey, "_data")) {
                    $graphQLData['graphql'] = $pData;
                }
            }

            /**
             * if web form is passed from GraphQL
             */
            if (isset($graphQLData["graphql"]['ebiz_webform']['AuthAmount'])) {
                $payment->setAdditionalInformation('ebzc_option', PaymentInterface::EBIZCHARGE_METHOD_GRAPHQL_TYPE_WEB_FORM);
                $payment->setAdditionalInformation('ebzc_option_type', PaymentInterface::EBIZCHARGE_METHOD_GRAPHQL_TYPE_WEB_FORM);

                return $this;
            }
            /**
             * if web form is passed from GraphQL
             */
            if (isset($graphQLData["graphql"]['ebiz_method']['method_id'])) {
                $payment->setAdditionalInformation('ebzc_option', PaymentInterface::EBIZCHARGE_METHOD_GRAPHQL_TYPE_SAVED);
                $payment->setAdditionalInformation('ebzc_option_type', PaymentInterface::EBIZCHARGE_METHOD_GRAPHQL_TYPE_SAVED);
                $payment->setAdditionalInformation('method_id', $graphQLData["graphql"]['ebiz_method']['method_id']);
                $payment->setAdditionalInformation(
                    'ebiz_cust_token',
                    $graphQLData["graphql"]['ebiz_method']['ebiz_cust_token']
                );

                return $this;
            }

            /**
             * Assign Payment Data
             * to Payment configuration
             */
            $this->assignPaymentData($paymentInfo, $additionalData);
        }
        return $this;
    }

    /**
     * Assign Payment Data
     *
     * Assigns preliminary form data (ebizcharge.js - getData())
     * to $infoInstance, which is the payment object.
     * First Load Cards from #14 and #15 and assign to
     * Customer and set for eBiz #16
     *
     * @param InfoInterface $infoInstance
     * @param DataObject $additionalData
     * @return void
     * @throws LocalizedException
     */
    public function assignPaymentData(InfoInterface $infoInstance, DataObject $additionalData): void
    {
        /**
         * Config Factory
         */
        $configFactory = $this->configFactory->create();

        /** @var  $additionalInfoData */
        $additionalInfoData = (array)$additionalData->getData();

        $quoteId = isset($additionalInfoData["quote_id"]) ? $additionalInfoData["quote_id"] : 0;
        $currentQuote = $this->_quoteFactory->create()->load($quoteId);

        /** set Additional Information */
        $infoInstance->setAdditionalInformation($additionalInfoData);
        $storeId = $this->_ebizPaymentModel->getStoreId();
        //$saveCard = false;
        $isConfigEnableSaveCards = $this->configFactory->create()->getIsSaveCreditCards($storeId);
        $isConfigEnableSaveBankAccounts = $this->configFactory->create()->getIsSaveBankAccounts($storeId);
        $infoInstance->setAdditionalInformation('config_save_card', $isConfigEnableSaveCards);

        /** @var $soapApiModel */
        $soapApiModel = $this->soapApiFactory->create();
        $currentArea = $this->getArea();
        $ebizOption = $additionalData->getEbzcOption() ?? "";

        $ebizOption = (string)$ebizOption;

        /** @var $ebizOptionType */
        $ebizOptionType = (string)$ebizOption;

        /** @var $paymentToken */
        $paymentToken = $additionalData->getDataByKey('paymentToken') ?? null;
        $configSaveCard = $additionalData->getDataByKey('config_save_card') ?? false;
        $configSaveBankAccount = $additionalData->getDataByKey('config_save_bank_accounts') ?? false;

        /** @var $saveCard */
        $saveCard = $additionalData->getDataByKey('ebzc_save_payment') ?? false;

        /** @var $ebzcAvsStreet */
        $ebzcAvsStreet = $additionalData->getDataByKey('ebzc_avs_street') ?? null;
        /** @var $ebzcAvsZip */
        $ebzcAvsZip = $additionalData->getDataByKey('ebzc_avs_zip') ?? null;
        /** @var $_ebzc_save_payment */
        $_ebzc_save_payment = $additionalData->getDataByKey('ebzc_save_payment') ?? null;
        /** @var $paylater_value */
        $paylater_value = $additionalData['ebzc_paylater_payment'] ?? false;

        $infoInstance->setAdditionalInformation('payment_token', $paymentToken);
        $infoInstance->setAdditionalInformation('save_card', $saveCard);
        $infoInstance->setAdditionalInformation('ebzc_paylater_payment', $paylater_value);

        if (!$this->_paymentOption) {
            $this->_paymentOption = $ebizOption;
        }


        if (!$this->_paymentOptionType) {
            $this->_paymentOptionType = (string)$additionalData->getEbzcOptionType();

            $achNumber = $additionalData->getAchNumber() ? $additionalData->getAchNumber() :
                $additionalData->getCcNumber();
            $achHolder = $additionalData->getAchHolder() ? $additionalData->getAchHolder() :
                $additionalData->getCcOwner();
            $achRoute = $additionalData->getAchRoute() ? $additionalData->getAchRoute() :
                $additionalData->getAchRouting();

            $this->paymentData = [
                'number' => $achNumber,
                'holder' => $achHolder,
                'routing' => $achRoute
            ];
        }

        /** @var  $ccType */
        $methodCardType = $this->getCardType($additionalData->getCcType());

        /** @var  $ccType */
        $ccType = $this->getCcType($additionalData);
        $paymentFormData = [];

        /**
         * if array exists and input is from GraphQL
         */
        if (!$this->_paymentOption && key_exists(GraphQLInterface::GRAPHQL_WEBFORM_INPUT_PAYMENT_PARAMS_FORM_TYPE, $additionalInfoData)) {

            $paymentFormData = isset($additionalInfoData["ebiz_webform"]) ? $additionalInfoData["ebiz_webform"] : [];
            $customerId = isset($additionalInfoData["ebiz_webform"]["user_id"]) ? $additionalInfoData["ebiz_webform"]["user_id"] : 0;
            $payByType = isset($additionalInfoData["ebiz_webform"]["PayByType"]) ? $additionalInfoData["ebiz_webform"]["PayByType"] : "cc";

            $ebzcCustomerId = $customerId;
            $customer = $this->_customerFactory->create()->load($customerId);

            if ($customer && $customer->getId()) {
                $ebzcCustomerId = $customer->getEcCustId() ?? 0;
            }
            $paymentFormData["customer_id"] = $ebzcCustomerId;
            $additionalData->setEbzcCustId($ebzcCustomerId);
            $this->_paymentOption = EbizPaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_HOSTED_FORM;
            $this->_paymentOptionType = $payByType;
            $additionalData->setTransactionInfo(json_encode($paymentFormData));
            $additionalData->setEbzcOption($this->_paymentOption);
            $additionalData->setEbzcOptionType($this->_paymentOptionType);
            $infoInstance->setEbzcOption($this->_paymentOption);
            $infoInstance->setEbzcOptionType($this->_paymentOptionType);


        }


        /**
         * if Payment type Option is New
         */
        if (strtolower($this->_paymentOption) === strtolower(EbizPaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_NEW)) {
            /** if Ebizcharge option is new  */
            $this->_ebizchargeLogger->addInfo(__('Ebiz Payment option new is selected '));

            /**
             * if payment option Type is ACH
             */
            if (strtolower($this->_paymentOptionType) === strtolower(EbizPaymentInterface::ACH)) {

                $methodCardType = $this->getCardType("check");

                $infoInstance
                    ->setCcType($methodCardType)
                    ->setCcOwner($additionalData->getAchType() . ' ' . $this->paymentData['holder'])
                    ->setCcLast4(substr($this->paymentData['number'] ?? "", -4))
                    ->setCcNumber($this->paymentData['number'] ?? "")
                    ->setCcCid($additionalData->getCcCid())
                    ->setAchRoute($this->paymentData['routing'] ?? "")
                    ->setAchType($additionalData->getAchType())
                    ->setEbzcOption($additionalData->getEbzcOption())
                    ->setEbzcOptionType($this->_paymentOptionType)
                    ->setEbzcCustId($additionalData->getEbzcCustId())
                    ->setEbzcSavePayment($additionalData->getEbzcSavePayment());

                $infoInstance->setAdditionalInformation('ebzc_option_type', $this->_paymentOptionType);
                $infoInstance->setAdditionalInformation('ebzc_option', $additionalData->getEbzcOption());
                $infoInstance->setAdditionalInformation('cc_type', $methodCardType);
                $infoInstance->setAdditionalInformation('ach_type', $additionalData->getAchType());
                $infoInstance->setAdditionalInformation('ach_route', $this->paymentData['routing']);
                $infoInstance->setAdditionalInformation('ach_routing', $this->paymentData['routing']);
                $infoInstance->setAdditionalInformation('ebzc_save_payment', $additionalData->getEbzcSavePayment());
                $infoInstance->setAdditionalInformation('ebzc_cust_id', $additionalData->getEbzcCustId());

                /** Addtional data is having ACH logging */
                $this->_ebizchargeLogger->addInfo(__('Selected Payment Method Type is :  ' . EbizPaymentInterface::ACH));


            } else {

                $infoInstance->setCcType($configFactory->getShortCcType($methodCardType))
                    ->setCcOwner($additionalData->getCcOwner())
                    ->setCcLast4(substr($additionalData->getCcNumber(), -4))
                    ->setCcNumber($additionalData->getCcNumber())
                    ->setCcCid($additionalData->getCcCid())
                    ->setCcExpMonth($additionalData->getCcExpMonth())
                    ->setCcExpYear($additionalData->getCcExpYear())
                    ->setCcType2($configFactory->getLongCcType($additionalData->getCcType()))
                    ->setCcSsIssue($additionalData->getCcSsIssue())
                    ->setCcSsStartMonth($additionalData->getCcSsStartMonth())
                    ->setCcSsStartYear($additionalData->getCcSsStartYear())
                    ->setEbzcOption($additionalData->getEbzcOption())
                    ->setEbzcOptionType($this->_paymentOptionType)
                    ->setEbzcCustId($additionalData->getEbzcCustId())
                    ->setEbzcAvsZip($additionalData->getEbzcAvsZip())
                    ->setEbzcAvsStreet($ebzcAvsStreet)
                    ->setEbzcSavePayment($additionalData->getEbzcSavePayment());
            }

            $infoInstance->setAdditionalInformation('ebzc_option_type', $this->_paymentOptionType);
            $infoInstance->setAdditionalInformation('exp_month', $additionalData->getCcExpMonth());
            $infoInstance->setAdditionalInformation('exp_year', $additionalData->getCcExpYear());
            $infoInstance->setAdditionalInformation('cc_type', $configFactory->getShortCcType($methodCardType));
            $infoInstance->setAdditionalInformation('cc_type_2', $configFactory->getLongCcType($additionalData->getCcType()));
            $infoInstance->setAdditionalInformation('cc_exp_month', $additionalData->getCcExpMonth());
            $infoInstance->setAdditionalInformation('cc_exp_year', $additionalData->getCcExpYear());
            $infoInstance->setAdditionalInformation('ebzc_option', $additionalData->getEbzcOption());
            $infoInstance->setAdditionalInformation('ebzc_cust_id', $additionalData->getEbzcCustId());
            $infoInstance->setAdditionalInformation('ebzc_avs_zip', $additionalData->getEbzcAvsZip());
            $infoInstance->setAdditionalInformation('ebzc_avs_street', $ebzcAvsStreet);
            $infoInstance->setAdditionalInformation('ebzc_method_id', $additionalData->getEbzcMethodId());
            $infoInstance->setAdditionalInformation('ebzc_save_payment', $additionalData->getEbzcSavePayment());

            /**
             *
             * if Payment Option Type is
             * Payment Type is Saved
             */
        } elseif (strtolower($this->_paymentOption) ===
            strtolower(EbizPaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_SAVED)) {

            /** Addtional data is saved logging */
            $this->_ebizchargeLogger->addInfo(__('Payment additional Data is set to Saved'));

            $soapApiModel->setData('savedMethodId', $additionalData->getEbzcMethodId());
            $customerId = $additionalData->getEbzcCustId();
            $customer = $this->_customerFactory->create()->loadByEbizCustomerId($customerId);
            $customerToken = $customer->getEcCustToken();
            $paymentMethodId = $additionalData->getEbzcMethodId();

            /** @var $paymentMethod */
            $paymentMethod = $soapApiModel->getCustomerPaymentMethodProfile(
                $customerToken,
                $paymentMethodId
            );

            $methodCardType = $this->getCardType(isset($paymentMethod->CardType) ? $paymentMethod->CardType : "");

            if (!$paymentMethod) {
                throw new LocalizedException(__("Please provide valid Payment Method Id."));
            }

            /** if the data is in shape of ACH */
            if (strtolower($paymentMethod->MethodType) === strtolower(EbizPaymentInterface::EBIZCHARGE_METHOD_TYPE_CHECK)) {
                $achType = $additionalData->getAchType() ? $additionalData->getAchType() : $paymentMethod->AccountType;
                $methodCardType = $this->getCardType($this->_paymentOptionType);

                $infoInstance
                    ->setEbzcOption($additionalData->getEbzcOption())
                    ->setEbzcOptionType($this->_paymentOptionType)
                    ->setCcType($achType)
                    ->setCcType2($achType)
                    ->setEbzcMethodId($additionalData->getEbzcMethodId())
                    ->setEbzcCustId($customerId)
                    ->setCcOwner($paymentMethod->MethodName)
                    ->setCcLast4(substr($paymentMethod->Account, -4))
                    ->setCcNumber($paymentMethod->Account)
                    ->setEbzcSavePayment(
                        $additionalData->getEbzcSavePayment()
                    );
                $infoInstance->setCcType($methodCardType);

                $achType = $additionalData->getAchType() ? $additionalData->getAchType() : $paymentMethod->AccountType;
                $achRoute = $additionalData->getAchRouting() ? $additionalData->getAchRouting() :
                    $paymentMethod->Routing;
                $infoInstance->setAdditionalInformation('ebzc_option_type', $this->_paymentOptionType);
                $infoInstance->setAdditionalInformation('ach_type', $achType);
                $infoInstance->setAdditionalInformation('cc_type', $achType);
                $infoInstance->setAdditionalInformation('cc_type_2', $achType);
                $infoInstance->setAdditionalInformation('ach_route', $achRoute);
                $infoInstance->setAdditionalInformation('ach_routing', $achRoute);
                $infoInstance->setAdditionalInformation('ebzc_cust_id', $customerId);

            } else {

                $paymentMethodName = $paymentMethod->MethodName;
                $this->setCardType($paymentMethod);
                $paymentMethodJson = json_decode($paymentMethodName);

                if (is_object($paymentMethodJson)) {
                    $paymentMethodName = $paymentMethodJson->b ?? $paymentMethodName;
                }
                /** @var  $paymentMethodDates */
                $paymentMethodDates = explode("-", $paymentMethod->CardExpiration);
                $expYear = isset($paymentMethodDates[0]) ? $paymentMethodDates[0] : "";
                $expMonth = isset($paymentMethodDates[1]) ? $paymentMethodDates[1] : "";

                $ebzcAvsStreet = $paymentMethod->AvsStreet ?? "";
                $ebzcAvsZip = $paymentMethod->AvsZip ?? "";

                $infoInstance
                    ->setEbzcOption($additionalData->getEbzcOption())
                    ->setEbzcOptionType($this->_paymentOptionType)
                    ->setEbzcMethodId($additionalData->getEbzcMethodId())
                    ->setEbzcCustId($customerId)
                    ->setEbzcAvsStreet($ebzcAvsStreet)
                    ->setEbzcAvsZip($ebzcAvsZip)
                    ->setCcType($configFactory->getShortCcType($this->setCardType($paymentMethod)))
                    ->setCcType2($configFactory->getLongCcType($this->setCardType($paymentMethod)))
                    ->setCcOwner($paymentMethodName)
                    ->setCcLast4(substr($paymentMethod->CardNumber ?? "", -4))
                    ->setCcNumber($paymentMethod->CardNumber ?? '')
                    ->setCcExpMonth($expMonth)
                    ->setCcExpYear($expYear)
                    ->setCcCid($additionalData->getCcCid())
                    ->setEbzcSavePayment(
                        $additionalData->getEbzcSavePayment()
                    );
                $infoInstance->setAdditionalInformation('exp_month', $expMonth);
                $infoInstance->setAdditionalInformation('exp_year', $expYear);
                $infoInstance->setAdditionalInformation('cc_type', $this->setCardType($paymentMethod));
                $infoInstance->setAdditionalInformation('cc_type_2', $configFactory->getLongCcType($this->setCardType($paymentMethod)));
                $infoInstance->setAdditionalInformation('cc_exp_month', $expMonth);
                $infoInstance->setAdditionalInformation('cc_exp_year', $expYear);
                $infoInstance->setAdditionalInformation('ebzc_avs_zip', $ebzcAvsZip);
                $infoInstance->setAdditionalInformation('ebzc_avs_street', $ebzcAvsStreet);
            }

            $infoInstance->setAdditionalInformation('ebzc_option_type', $this->_paymentOptionType);
            $infoInstance->setAdditionalInformation('ebzc_option', $additionalData->getEbzcOption());
            $infoInstance->setAdditionalInformation('cc_type', $methodCardType);
            $infoInstance->setAdditionalInformation('cc_type_2', $methodCardType);
            $infoInstance->setAdditionalInformation('ebzc_cust_id', $customerId);
            $infoInstance->setAdditionalInformation('ebzc_method_id', $additionalData->getEbzcMethodId());
            $infoInstance->setAdditionalInformation('ebzc_save_payment', $additionalData->getEbzcSavePayment());


            /** in case of
             * update Ebz Option
             * Type Update
             */
        } elseif (strtolower($this->_paymentOption) ===
            strtolower(EbizPaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_UPDATE)) {

            /** Addtional data is updated logging */
            $this->_ebizchargeLogger->addInfo(__('additional data is updated'));
            $soapApiModel->setData('savedMethodId', $additionalData->getEbzcMethodId());
            try {
                $customerId = $additionalData->getEbzcCustId();
                $customer = $this->_customerFactory->create()->loadByEbizCustomerId($customerId);
                $customerToken = $customer->getEcCustToken();
                $ebizPaymentMethodId = $additionalData->getEbzcMethodId();

                /** @var load latest Payment Method $paymentMethod */
                $paymentMethod = $soapApiModel->getCustomerPaymentMethodProfile(
                    $customerToken,
                    $ebizPaymentMethodId
                );
                $methodCardType = $this->getCardType(isset($paymentMethod) ? $paymentMethod->CardType : null);

            } catch (Exception $ex) {
                $this->_ebizchargeLogger->addError(__(
                    'Oops error occurred during Update Payment Method at Ebizcharge Payment Gateway : ' .
                    $ex->getMessage()
                ));
                throw new LocalizedException(__(
                    'Oops error occurred during Update Payment Method at Ebizcharge Payment Gateway : ' .
                    $ex->getMessage()
                ));
            }

            $paymentMethodName = $paymentMethod->MethodName;
            $paymentMethodJson = json_decode($paymentMethodName);

            if (is_object($paymentMethodJson)) {
                $paymentMethodName = $paymentMethodJson->b ?? $paymentMethodName;
            }
            /** @var  $paymentMethodDates */
            $paymentMethodDates = explode("-", $paymentMethod->CardExpiration);
            $expYear = isset($paymentMethodDates[0]) ? $paymentMethodDates[0] : "";
            $expMonth = isset($paymentMethodDates[1]) ? $paymentMethodDates[1] : "";
            $expYear = $infoInstance->getAdditionalInformation("cc_exp_year") ?? $expYear;
            $expMonth = $infoInstance->getAdditionalInformation("cc_exp_month") ?? $expMonth;

            $infoInstance->setEbzcOption($additionalData->getEbzcOption())
                ->setEbzcMethodId($additionalData->getEbzcMethodId())
                ->setEbzcCustId($customerId)
                ->setCcType($this->setCardType($paymentMethod))
                ->setCcType2($configFactory->getLongCcType($this->setCardType($paymentMethod)))
                ->setCcOwner($paymentMethodName)
                ->setCcLast4(substr($paymentMethod->CardNumber, -4))
                ->setCcNumber($paymentMethod->CardNumber)
                ->setEbzcOptionType($this->_paymentOptionType)
                ->setCcExpMonth($expMonth)
                ->setCcExpYear($expYear)
                ->setCcCid($additionalData->getCcCid())
                ->setEbzcAvsStreet($ebzcAvsStreet)
                ->setEbzcAvsZip($additionalData->getEbzcAvsZip())
                ->setEbzcSavePayment($additionalData->getEbzcSavePayment());

            $infoInstance->setAdditionalInformation('ebzc_option_type', $this->_paymentOptionType);
            $infoInstance->setAdditionalInformation('cc_type', $this->setCardType($paymentMethod));
            $infoInstance->setAdditionalInformation('cc_type_2', $configFactory->getLongCcType($this->setCardType($paymentMethod)));
            $infoInstance->setAdditionalInformation('exp_month', $expMonth);
            $infoInstance->setAdditionalInformation('exp_year', $expYear);
            $infoInstance->setAdditionalInformation('cc_exp_month', $expMonth);
            $infoInstance->setAdditionalInformation('cc_exp_year', $expYear);
            $infoInstance->setAdditionalInformation('ebzc_option', $additionalData->getEbzcOption());
            $infoInstance->setAdditionalInformation('ebzc_cust_id', $customerId);
            $infoInstance->setAdditionalInformation('ebzc_method_id', $additionalData->getEbzcMethodId());
            $infoInstance->setAdditionalInformation('ebzc_save_payment', $additionalData->getEbzcSavePayment());
            $infoInstance->setAdditionalInformation('ebzc_avs_street', $ebzcAvsStreet);
            $infoInstance->setAdditionalInformation('ebzc_avs_zip', $ebzcAvsZip);

            /**
             *
             * if Payment Option is
             * type of Pay Later
             */
        } elseif (strtolower($this->_paymentOption) ===
            strtolower(EbizPaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_PAY_LATER)) {

            $infoInstance->setEbzcOption($additionalData->getEbzcOption())
                ->setEbzcCustId($additionalData->getEbzcCustId())
                ->setCcType(EbizPaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_PAY_LATER);

            $infoInstance->setAdditionalInformation('ebzc_option', $additionalData->getEbzcOption());
            $infoInstance->setAdditionalInformation('ebzc_option_type', $this->_paymentOptionType);
            $infoInstance->setAdditionalInformation('cc_type', $this->_paymentOption);
            $infoInstance->setAdditionalInformation('cc_type_2', $configFactory->getLongCcType($this->_paymentOption));
            $infoInstance->setAdditionalInformation('ebzc_cust_id', $additionalData->getEbzcCustId());
            $infoInstance->setAdditionalInformation('ebzc_method_id', $additionalData->getEbzcMethodId());
            $infoInstance->setAdditionalInformation('ebzc_save_payment', $additionalData->getEbzcSavePayment());
            $infoInstance->setAdditionalInformation('ebzc_paylater_payment', $additionalData->getEbzcPaylaterPayment());

            /**
             *
             * If Payment Option type is Download Order
             */
        } elseif (strtolower($ebizOptionType) ===
            strtolower(EbizPaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_DOWNLOAD_ORDER)) {

            $infoInstance->setAdditionalInformation('ebzc_cust_id', $additionalData->getDataByKey('mage_cust_id'));

            $infoInstance->setAdditionalInformation(
                'ebzc_method_id',
                $additionalData->getDataByKey('ebzc_method_id') ?? null
            );
            $infoInstance->setAdditionalInformation(
                'ebzc_save_payment',
                $additionalData->getDataByKey('ebzc_save_payment')
            );

            $infoInstance->setAdditionalInformation('ebzc_option', $additionalData->getDataByKey('ebzc_option'));
            $infoInstance->setAdditionalInformation('ebzc_option_type', $this->_paymentOptionType);
            $infoInstance->setAdditionalInformation('ebzc_avs_street', $additionalData->getDataByKey('AvsStreet'));
            $infoInstance->setAdditionalInformation('ebzc_avs_zip', $additionalData->getDataByKey('AvsZip'));
            $infoInstance->setAdditionalInformation('additional_information', $additionalData->getData());
            $infoInstance->setAdditionalInformation('cc_type', $this->_paymentOption);
            $infoInstance->setAdditionalInformation('cc_type_2', $configFactory->getLongCcType($this->_paymentOption));

            /**
             *
             * If Payment Option type is Web Hosted Form
             */
        } elseif (strtolower($this->_paymentOption) ===
            strtolower(EbizPaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_HOSTED_FORM)) {

            $transactionInfo = (array)json_decode($additionalData->getTransactionInfo());
            $tokenizeOnly = $additionalData->getEbzcOptionType();
            $infoInstance->setAdditionalInformation('ebzc_option', $additionalData->getEbzcOption());
            $infoInstance->setAdditionalInformation('ebzc_option_type', $this->_paymentOptionType);
            $infoInstance->setAdditionalInformation('ebzc_cust_id', $additionalData->getEbzcCustId());

            $infoInstance->setTransactionInfo($additionalData->getTransactionInfo());
            $infoInstance->setAdditionalInformation('transaction_info', $additionalData->getTransactionInfo());
            $infoInstance->setAdditionalInformation('cc_type', $this->_paymentOption);
            $infoInstance->setAdditionalInformation('cc_type_2', $configFactory->getLongCcType($this->_paymentOption));


            if (strtolower($tokenizeOnly) === strtolower(EbizPaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_HOSTED_FORM_TOKENIZED_ONLY)) {
                $paymentMethodId = isset($transactionInfo["PmToken"]) ? $transactionInfo["PmToken"] : "";
                $infoInstance->setEbzcOption($additionalData->getEbzcOption())
                    ->setEbzcMethodId($paymentMethodId);
                $infoInstance->setAdditionalInformation('ebzc_method_id', $paymentMethodId);
                $infoInstance->setAdditionalInformation('ebzc_save_payment', $additionalData->getEbzcSavePayment());
            }
            /**
             *
             * If Payment Option type is Download Order
             */
        } elseif (strtolower($this->_paymentOption) ===
            strtolower(EbizPaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_RECURRING)) {

            $soapApiModel->setData('savedMethodId', $additionalData->getEbzcMethodId());

            $paymentMethod = $soapApiModel->getCustomerPaymentMethodProfile(
                $additionalData->getEbzcCustId(),
                $additionalData->getEbzcMethodId()
            );

            $methodCardType = $this->getCardType(isset($paymentMethod->CardType) ? $paymentMethod->CardType : null);


            if (isset($paymentMethod->MethodType) && $paymentMethod->MethodType ===
                EbizPaymentInterface::EBIZCHARGE_METHOD_TYPE_CHECK) {
                $ccNumber = $paymentMethod->Account;
            } else {
                $ccNumber = $paymentMethod->CardNumber;
            }
            $ccLastNumbers = substr($ccNumber, -4);

            if (empty($additionalData->getCcExpMonth())) {
                if (isset($paymentMethod->CardExpiration)) {
                    $cardExpiration = explode('-', $paymentMethod->CardExpiration);
                    $additionalData->setCcExpMonth($cardExpiration[1]);
                    $additionalData->setCcExpYear($cardExpiration[0]);
                } else {
                    $additionalData->setCcExpMonth(null);
                    $additionalData->setCcExpYear(null);
                }
            }

            $infoInstance
                ->setEbzcOption($additionalData->getEbzcOption())
                ->setEbzcMethodId($additionalData->getEbzcMethodId())
                ->setEbzcCustId($additionalData->getEbzcCustId())
                ->setCcType($methodCardType)
                ->setCcType2($configFactory->getLongCcType($methodCardType))
                ->setCcOwner($paymentMethod->MethodName)
                ->setCcLast4($ccNumber)
                ->setCcNumber($ccLastNumbers)
                ->setCcExpMonth($additionalData->getCcExpMonth())
                ->setCcExpYear($additionalData->getCcExpYear())
                ->setCcCid($additionalData->getCcCid())
                ->setEbzcOptionType($this->_paymentOptionType)
                ->setEbzcAvsStreet($additionalData->getEbzcAvsStreet())
                ->setEbzcAvsZip($additionalData->getEbzcAvsZip())
                ->setEbzcSavePayment($additionalData->getEbzcSavePayment());

            $infoInstance->setAdditionalInformation('exp_month', $additionalData->getCcExpMonth());
            $infoInstance->setAdditionalInformation('cc_type', $methodCardType);
            $infoInstance->setAdditionalInformation('cc_type_2', $configFactory->getLongCcType($this->_paymentOption));
            $infoInstance->setAdditionalInformation('exp_year', $additionalData->getCcExpYear());
            $infoInstance->setAdditionalInformation('ebzc_option', $additionalData->getDataByKey('ebzc_option'));
            $infoInstance->setAdditionalInformation(
                'ebzc_option_new',
                $additionalData->getDataByKey('ebzc_option_new')
            );
            $infoInstance->setAdditionalInformation(
                'ebzc_option_existing',
                $additionalData->getDataByKey('ebzc_option_existing')
            );
            $infoInstance->setAdditionalInformation('ebzc_cust_id', $additionalData->getDataByKey('ebzc_cust_id'));
            $infoInstance->setAdditionalInformation('mage_cust_id', $additionalData->getDataByKey('mage_cust_id'));
            $infoInstance->setAdditionalInformation(
                'ebzc_method_id',
                $additionalData->getDataByKey('ebzc_method_id')
            );
            $infoInstance->setAdditionalInformation(
                'excludeAmount',
                $additionalData->getDataByKey('excludeAmount')
            );
            $infoInstance->setAdditionalInformation('ebzc_option_type', $this->_paymentOptionType);

        } else {
            $infoInstance->setEbzcSavePayment($additionalData->getEbzcSavePayment());
            $infoInstance->setAdditionalInformation('ebzc_save_payment', $additionalData->getEbzcSavePayment());
        }
    }


    /**
     * @return mixed
     */
    public function getArea()
    {
        return EbizHelper::getAreaCode();
    }


    /**
     * @param $paymentMethodCardType
     * @return void
     */
    public function getCardType($paymentMethodCardType = null)
    {

        if ($paymentMethodCardType && $paymentMethodCardType !== 'check') {

            $savedCardType = $paymentMethodCardType ?? "";
            $savedCardType = !empty($savedCardType) ? strtolower($savedCardType) : "";

            if ($savedCardType === strtolower(ConfigModelInterface::CREDIT_CARD_TYPE_VISA_CARD_PREFIX) || $savedCardType === strtolower(ConfigModelInterface::CREDIT_CARD_TYPE_VISA_CARD_SHORT_PREFIX)) {
                $this->cardType = ConfigModelInterface::CREDIT_CARD_TYPE_VISA_CARD_PREFIX;
            } elseif ($savedCardType === strtolower(ConfigModelInterface::CREDIT_CARD_TYPE_MASTER_CARD_PREFIX) || $savedCardType === strtolower(ConfigModelInterface::CREDIT_CARD_TYPE_MASTER_CARD_SHORT_PREFIX)) {
                $this->cardType = ConfigModelInterface::CREDIT_CARD_TYPE_MASTER_CARD_PREFIX;
            } elseif ($savedCardType === strtolower(ConfigModelInterface::CREDIT_CARD_TYPE_AMERICAN_EXPRESS_PREFIX) || $savedCardType === strtolower(ConfigModelInterface::CREDIT_CARD_TYPE_AMERICAN_EXPRESS_SHORT_PREFIX)) {
                $this->cardType = ConfigModelInterface::CREDIT_CARD_TYPE_AMERICAN_EXPRESS_PREFIX;
            } elseif ($savedCardType === strtolower(ConfigModelInterface::CREDIT_CARD_TYPE_DISCOVER_CARD_PREFIX) || $savedCardType === strtolower(ConfigModelInterface::CREDIT_CARD_TYPE_DISCOVER_CARD_SHORT_PREFIX)) {
                $this->cardType = ConfigModelInterface::CREDIT_CARD_TYPE_DISCOVER_CARD_PREFIX;
            }
        } else if ($paymentMethodCardType === 'check') {
            $this->cardType = EbizPaymentInterface::ACH;
        } else {
            $this->cardType = EbizPaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_SAVED;
        }

        /** logging the card Type */
        $this->_ebizchargeLogger->addInfo(__('Current Card Type is : ' . $this->cardType));


        return $this->cardType;
    }

    /**
     * Set Cart Type
     *
     * @param mixed $paymentMethod
     * @return string
     */
    public function setCardType($paymentMethod)
    {
        $cardType = "";

        if (!empty($paymentMethod) && isset($paymentMethod->CardType)) {
            if ($paymentMethod->CardType === ConfigModelInterface::CREDIT_CARD_TYPE_VISA_CARD_PREFIX
                || $paymentMethod->CardType === ConfigModelInterface::CREDIT_CARD_TYPE_VISA_CARD_SHORT_PREFIX
            ) {
                $cardType = ConfigModelInterface::CREDIT_CARD_TYPE_VISA_CARD_PREFIX;
            } elseif ($paymentMethod->CardType === ConfigModelInterface::CREDIT_CARD_TYPE_MASTER_CARD_PREFIX
                || $paymentMethod->CardType === ConfigModelInterface::CREDIT_CARD_TYPE_MASTER_CARD_SHORT_PREFIX
            ) {
                $cardType = ConfigModelInterface::CREDIT_CARD_TYPE_MASTER_CARD_PREFIX;
            } elseif ($paymentMethod->CardType === ConfigModelInterface::CREDIT_CARD_TYPE_AMERICAN_EXPRESS_PREFIX
                || $paymentMethod->CardType === ConfigModelInterface::CREDIT_CARD_TYPE_AMERICAN_EXPRESS_SHORT_PREFIX
            ) {
                $cardType = ConfigModelInterface::CREDIT_CARD_TYPE_AMERICAN_EXPRESS_PREFIX;
            } elseif ($paymentMethod->CardType === ConfigModelInterface::CREDIT_CARD_TYPE_DISCOVER_CARD_PREFIX
                || $paymentMethod->CardType === ConfigModelInterface::CREDIT_CARD_TYPE_DISCOVER_CARD_SHORT_PREFIX
            ) {
                $cardType = ConfigModelInterface::CREDIT_CARD_TYPE_DISCOVER_CARD_PREFIX;
            }
        } elseif (!empty($paymentMethod) && $paymentMethod->MethodType === 'check') {
            $cardType = EbizPaymentInterface::ACH;
        } else {
            $cardType = EbizPaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_SAVED;
        }

        /** logging the card Type */
        $this->_ebizchargeLogger->addInfo(__('Current Card Type is : ' . $cardType));

        return $cardType;
    }

    /**
     * Get Cc Type
     *
     * @param mixed $additionalData
     * @return string
     */
    public function getCcType($additionalData = null): string
    {
        $selectedCardType = "";
        $cardType = (string)($additionalData->getCcType() ?? "");

        switch ($cardType) {
            case ConfigModelInterface::CREDIT_CARD_TYPE_VISA_CARD_MAGE_PREFIX:
            case ConfigModelInterface::CREDIT_CARD_TYPE_VISA_CARD_SHORT_PREFIX:
                $selectedCardType = ConfigModelInterface::CREDIT_CARD_TYPE_VISA_CARD_PREFIX;
                break;
            case ConfigModelInterface::CREDIT_CARD_TYPE_AMERICAN_EXPRESS_MAGE_PREFIX:
            case ConfigModelInterface::CREDIT_CARD_TYPE_AMERICAN_EXPRESS_SHORT_PREFIX:
                $selectedCardType = ConfigModelInterface::CREDIT_CARD_TYPE_AMERICAN_EXPRESS_PREFIX;
                break;
            case ConfigModelInterface::CREDIT_CARD_TYPE_MASTER_CARD_MAGE_PREFIX:
            case ConfigModelInterface::CREDIT_CARD_TYPE_MASTER_CARD_SHORT_PREFIX:
                $selectedCardType = ConfigModelInterface::CREDIT_CARD_TYPE_MASTER_CARD_PREFIX;
                break;
            case ConfigModelInterface::CREDIT_CARD_TYPE_DISCOVER_CARD_MAGE_PREFIX:
            case ConfigModelInterface::CREDIT_CARD_TYPE_DISCOVER_CARD_SHORT_PREFIX:
                $selectedCardType = ConfigModelInterface::CREDIT_CARD_TYPE_DISCOVER_CARD_PREFIX;
                break;
            case ConfigModelInterface::CREDIT_CARD_TYPE_BANK_OF_AMERICA_MAGE_PREFIX:
            case ConfigModelInterface::CREDIT_CARD_TYPE_BANK_OF_AMERICA_SHORT_PREFIX:
                $selectedCardType = ConfigModelInterface::CREDIT_CARD_TYPE_BANK_OF_AMERICA_PREFIX;
                break;
            default:
                $selectedCardType = $cardType;
        }

        return $selectedCardType;
    }

    /**
     * Get Selected Card Type
     *
     * @param string $cardType
     * @return string
     */
    public function getSelectedCardType($cardType = ""): string
    {
        /** @var  $selectedCardType */
        $selectedCardType = "";

        switch ($cardType) {
            case ConfigModelInterface::CREDIT_CARD_TYPE_VISA_CARD_MAGE_PREFIX:
                $selectedCardType = ConfigModelInterface::CREDIT_CARD_TYPE_VISA_CARD_PREFIX;
                break;
            case ConfigModelInterface::CREDIT_CARD_TYPE_AMERICAN_EXPRESS_MAGE_PREFIX:
                $selectedCardType = ConfigModelInterface::CREDIT_CARD_TYPE_AMERICAN_EXPRESS_PREFIX;
                break;
            case ConfigModelInterface::CREDIT_CARD_TYPE_MASTER_CARD_MAGE_PREFIX:
                $selectedCardType = ConfigModelInterface::CREDIT_CARD_TYPE_MASTER_CARD_PREFIX;
                break;
            case ConfigModelInterface::CREDIT_CARD_TYPE_DISCOVER_CARD_MAGE_PREFIX:
                $selectedCardType = ConfigModelInterface::CREDIT_CARD_TYPE_DISCOVER_CARD_PREFIX;
                break;
            case ConfigModelInterface::CREDIT_CARD_TYPE_BANK_OF_AMERICA_MAGE_PREFIX:
                $selectedCardType = ConfigModelInterface::CREDIT_CARD_TYPE_BANK_OF_AMERICA_PREFIX;
                break;
            default:
                $selectedCardType = $cardType;
        }

        return $selectedCardType;
    }


}

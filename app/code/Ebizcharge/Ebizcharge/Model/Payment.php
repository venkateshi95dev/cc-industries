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

namespace Ebizcharge\Ebizcharge\Model;

use Ebizcharge\Ebizcharge\Api\Data\OrderInterface as EbizOrderInterface;
use Ebizcharge\Ebizcharge\Api\Data\PaymentInterface;
use Ebizcharge\Ebizcharge\Api\Data\SurchargeInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\TranApi as SoapApiModel;
use Exception;
use Magento\Backend\Model\Auth\Session as AdminSession;
use Magento\Backend\Model\Session\Quote as BackendQuote;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\PaymentException;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Api\Data\CartInterface as CartInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\TransactionSearchResultInterface;
use Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Payment model
 * Handles all the payment functions - authorize, capture, refund, etc
 *
 * Class Payment
 */
class Payment extends AbstractPaymentModel
{
    /**
     * @var array
     */
    public array $paymentFormTypes = [];
    /**
     * @var bool
     */
    protected bool $_canSaveCc = true;
    /**
     * Auth Mode var
     *
     * @var string
     */
    protected string $authMode = 'auto';
    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $ebizchargeLogger;
    /**
     * @var AdminSession
     */
    protected AdminSession $backendAuthSession;
    /**
     * @var CustomerSession
     */
    protected CustomerSession $customerSession;
    /**
     * @var SoapApiModel
     */
    protected TranApi $soapApiModel;
    /**
     * @var Config
     */
    protected Config $ebizConfig;
    /**
     * @var BackendQuote
     */
    protected BackendQuote $backendQuote;
    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $customerFactory;
    /**
     * @var CheckoutSession
     */
    protected CheckoutSession $checkoutSession;
    /**
     * @var ProductFactory
     */
    protected ProductFactory $productFactory;
    /**
     * @var OrderFactory
     */
    protected OrderFactory $orderFactory;
    /**
     * @var RequestInterface
     */
    protected RequestInterface $requestInterface;
    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManagerInterface;
    /**
     * @var QuoteRepository
     */
    protected QuoteRepository $quoteRepository;
    /**
     * @var transactionBuilder
     */
    protected $_transactionBuilder;
    /**
     * @var array
     */
    protected array $additionalInfo = [];
    /**
     * @var bool
     */
    protected bool $isSavePaymentMethod = false;
    /**
     * @var string
     */
    protected string $paymentType = "credit_card";
    /**
     * @var array
     */
    protected array $paymentMethodParams = [];
    /**
     * @var string
     */
    protected string $customerId = "0";
    /**
     * @var string
     */
    protected string $paymentActionType = "new";
    /**
     * @var null
     */
    protected $cardNumber = null;
    /**
     * @var bool
     */
    protected $isRecurring = false;
    /**
     * @var TransactionSearchResultInterfaceFactory
     */
    private TransactionSearchResultInterfaceFactory $transactionSearchResultFactory;

    /**
     * @param AdminSession $backendAuthSession
     * @param BackendQuote $backendQuote
     * @param Config $ebizConfig
     * @param CustomerSession $customerSession
     * @param ProductFactory $productFactory
     * @param OrderFactory $orderFactory
     * @param CustomerFactory $customerFactory
     * @param CheckoutSession $checkoutSession
     * @param RequestInterface $request
     * @param StoreManagerInterface $storeManager
     * @param TranApi $soapApiModel
     * @param EbizchargeLogger $ebizchargeLogger
     * @param QuoteRepository $quoteRepository
     * @param TransactionSearchResultInterfaceFactory $transactionSearchResultFactory
     */
    public function __construct(
        AdminSession $backendAuthSession,
        BackendQuote $backendQuote,
        Config $ebizConfig,
        CustomerSession $customerSession,
        ProductFactory $productFactory,
        OrderFactory $orderFactory,
        CustomerFactory $customerFactory,
        CheckoutSession $checkoutSession,
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        SoapApiModel $soapApiModel,
        EbizchargeLogger $ebizchargeLogger,
        QuoteRepository $quoteRepository,
        TransactionSearchResultInterfaceFactory $transactionSearchResultFactory
    ) {
        /**
         * constructing parent
         */
        parent::__construct();

        /** @var $backendAuthSession */
        $this->backendAuthSession = $backendAuthSession;
        /** @var $backendQuote */
        $this->backendQuote = $backendQuote;
        /** @var $ebizConfig */
        $this->ebizConfig = $ebizConfig;
        /** @var $customerSession */
        $this->customerSession = $customerSession;
        /** @var $soapApiModel */
        $this->soapApiModel = $soapApiModel;
        /** @var $ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
        /** @var $customerFactory */
        $this->customerFactory = $customerFactory;
        /** @var $checkoutSession */
        $this->checkoutSession = $checkoutSession;
        /** @var $productFactory */
        $this->productFactory = $productFactory;
        /** @var $orderFactory */
        $this->orderFactory = $orderFactory;
        /** @var $requestInterface */
        $this->requestInterface = $request;
        /** @var $storeManagerInterface */
        $this->storeManagerInterface = $storeManager;
        /** @var $quoteRepository */
        $this->quoteRepository = $quoteRepository;
        /** @var $transactionSearchResultFactory */
        $this->transactionSearchResultFactory = $transactionSearchResultFactory;

        /**
         * Web Form Types
         */
        $this->paymentFormTypes = [
            PaymentInterface::EBIZCHARGE_METHOD_GRAPHQL_WEBFORM_TYPE_RPM_CHECKOUT_GUEST,
            PaymentInterface::EBIZCHARGE_METHOD_GRAPHQL_WEBFORM_TYPE_RPM_CHECKOUT_USER,
            PaymentInterface::EBIZCHARGE_METHOD_GRAPHQL_WEBFORM_TYPE_CHECKOUT_ADD_PAYMENT_METHOD,
            PaymentInterface::EBIZCHARGE_METHOD_GRAPHQL_WEBFORM_PAYMENT_TYPE_CHECKOUT_WEBFORM,
            PaymentInterface::EBIZCHARGE_METHOD_GRAPHQL_WEBFORM_TYPE_CHECKOUT_PAYMENT_EMAIL_FORM,
            EbizOrderInterface::EBIZCHARGE_WEBFORM_CHECKOUT_REGISTERED_USER_FORM_TYPE
        ];
    }

    /**
     * Refunds payment
     *
     * Making the Refund back to Customer
     *
     * @param InfoInterface $payment
     * @param mixed $amount
     * @return Payment
     * @throws LocalizedException
     * @throws Exception
     */
    public function refund(InfoInterface $payment, $amount = 0)
    {
        /**
         * Init Transaction
         */
        $this->soapApiModel->initTransactionAPI($payment->getOrder()->getStoreId());

        if (!$payment->getLastTransId()) {
            $this->ebizchargeLogger->addError(__('Unable to find previous transaction to reference'));
            throw new LocalizedException(__('Unable to find previous transaction to reference'));
        }

        /**
         * @var  $transactions
         * refund
         */

        $order = $payment->getOrder();
        $amount = $order->getTotalPaid();
        $isRecurring = false;
        $isRefund = true;
        $this->soapApiModel->setData('command', PaymentInterface::EBIZCHARGE_COMMAND_REFUND);
        $this->soapApiModel->setData('amount', $amount);
        $orderNumber = $order->getIncrementId();
        $paymentDescription = "Order #" . $orderNumber;
        $this->soapApiModel->setTotalOrderedQty((float)$order->getTotalQtyOrdered() ?? 0);

        if ($this->ebizConfig->getPaymentDescription()) {
            $paymentDescription = str_replace(
                '[orderid]',
                $orderNumber,
                $this->ebizConfig->getPaymentDescription()
            );
        }

        $this->soapApiModel->setData('description', $paymentDescription);
        /** @var $paymentInfoParams */
        $paymentInfoParams = $this->getPaymentMethodData($payment);

        /**
         * Set Transaction Data
         */
        $this->soapApiModel->setTransactionData($payment, $isRefund, $paymentInfoParams);

        /**
         * Run Transaction
         * Pay Refund
         */
        $this->soapApiModel->runTransaction($payment, $isRecurring, [], $isRefund);

        /**
         * Saving Payment Refund
         * as a Transaction
         */
        $this->assignAndSaveTransactionResponse($payment);

        /**
         * unset Payment additional Information
         */
        $this->unsetPaymentAdditionalInfo($payment);

        return $this;
    }

    /**
     * Get Store Id
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->getStore()->getId();
    }

    /**
     * Get Store Manager
     *
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getStore()
    {
        return $this->storeManagerInterface->getStore();
    }

    /**
     * Get Payment Method Data
     *
     * @param InfoInterface|null $payment
     * @return array
     */
    public function getPaymentMethodData(?InfoInterface $payment = null)
    {
        /** @var $paymentAdditionalInfo */
        $paymentAdditionalInfo = (array)$payment->getAdditionalInformation();
        $this->paymentMethodParams = $paymentAdditionalInfo;
        return $this->paymentMethodParams;
    }
    // phpcs:enable

    /**
     * Assign Payment and Save Transactions
     *
     * @param InfoInterface|null $payment
     * @param null|mixed $ebizOptionType
     * @param array $transactionData
     * @param bool $isError
     * @return $this|false
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function assignAndSaveTransactionResponse(
        ?InfoInterface $payment = null,
        mixed $ebizOptionType = null,
        array $transactionData = [],
        bool $isError = false
    ) {
        if (!$payment) {
            return $this;
        }
        /**
         * Ebiz Payment Option Type
         */
        if ($ebizOptionType === PaymentInterface::EBIZCHARGE_METHOD_GRAPHQL_TYPE_WEB_FORM) {
            /** @var  $authorizedData */
            $authorizedData = $this->prepareGraphQLAuthorizedData($payment);
        } elseif ($ebizOptionType === PaymentInterface::PAYMENT_ENV_TYPE_FRONTEND) {
            $authorizedData = $this->prepareTransactionData($transactionData);
        } elseif ($ebizOptionType === PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_HOSTED_FORM) {
            $authorizedData = $this->prepareWebHostFormAuthorizedData($payment);
        } else {
            /** @var  $authorizedData */
            $authorizedData = $this->soapApiModel->getAuthorizeData();
        }
        //  dump($authorizedData); throw new LocalizedException(__("webform exception"));

        /** @var  $orderPaymentData */
        $orderPaymentData = $authorizedData;

        $transactionRefNumber = $orderPaymentData[
            PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_REFERENCE_NUMBER
        ] ?? "";
        $transactionType = $payment->getAdditionalInformation("ebzc_option");
        $ccType = $payment->getAdditionalInformation("cc_type");
        $ccType2 = $payment->getAdditionalInformation("cc_type_2");
        $transType = $orderPaymentData["command"] ?? "";
        $setLastTransId = $transactionRefNumber . '-' . $transType;
        $resultCode = isset($orderPaymentData["resultcode"]) ? $orderPaymentData["resultcode"] : "D";
        $authCode = isset($orderPaymentData["auth_code"]) ? $orderPaymentData["auth_code"] : "";
        $authAmount = isset($orderPaymentData["auth_amount"]) ? $orderPaymentData["auth_amount"] : "";

        $ebzcPaymentMethodId = $payment->getAdditionalInformation("ebzc_method_id") ?? "0";
        $ebizSavePayment = $payment->getAdditionalInformation("save_card");

        $cardCodeResultCode = $orderPaymentData["card_code_result_code"] ?? "";
        $cardCodeResult = isset($orderPaymentData["card_code_result"]) ? $orderPaymentData["card_code_result"] : "";

        $avsResult = isset($orderPaymentData["avs_result"]) ? $orderPaymentData["avs_result"] : "";
        $avsResultCode = isset($orderPaymentData["avs_result_code"]) ? $orderPaymentData["avs_result_code"] : "";

        $ebizAvsStreet = $orderPaymentData[PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_AVS_STREET] ?? "";
        $batchRefNumber = $orderPaymentData[PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_BATCH_REFERENCE_NUMBER] ?? "";
        $batchNumber = $orderPaymentData[PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_BATCH_NUMBER] ?? "";

        $cvvResultCode = $orderPaymentData[PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CARD_CODE] ?? "";

        $ebizCustomerNumber = $payment->getAdditionalInformation("ebzc_cust_id");

        $authAmount = isset($authorizedData['auth_amount']) ? $authorizedData['auth_amount'] :  0;

        /**
         * Setting EBizCharge Payment Data
         */
        $payment
            ->setCcApproval($authorizedData['authcode'])
            ->setCcTransId($authorizedData['refnum'])
            ->setEbzcOption($payment->getAdditionalInformation("ebzc_option"))
            ->setEbzcOptionType($payment->getAdditionalInformation("ebzc_option_type"))
            ->setQuoteId($payment->getAdditionalInformation("quote_id"))
            ->setCcAvsStatus($authorizedData['avs_result_code'])
            ->setEbzcCustId($ebizCustomerNumber)
            ->setEbzcBatchRefNumber($batchRefNumber)
            ->setCcStatus($resultCode)
            ->setPaymentToken($ebzcPaymentMethodId)
            ->setEbzcMethodId($ebzcPaymentMethodId)
            ->setCcType2($ccType2)
            ->setCcType($ccType)
            ->setAmountOrdered($authAmount)
            ->setBaseAmountOrdered($authAmount)
            ->setAmountAuthorized($authAmount)
            ->setBaseAmountAuthorized($authAmount)
            ->setLastTransId($transactionRefNumber)
            ->setEbzcAvsStreet($authorizedData['avs_result'])
            ->setCcCidStatus($authorizedData['cvv2_result_code'])
            ->setEbzcCustomerNumber($authorizedData['custnum'])
            ->setEbzcResultCode($authorizedData['resultcode'])
            ->setEbzcResult($authorizedData['result'])
            ->setEbzcPaymentReferenceNumber($authorizedData['refnum'])
            ->setEbzcPaymentIsDuplicate($authorizedData['is_duplicate'])
            ->setEbzcPaymentErrorCode($authorizedData['errorcode'])
            ->setEbzcPaymentError($authorizedData['error'])
            ->setEbzcPaymentCommand($authorizedData['command'])
            ->setEbzcCardLevelResult_code($authorizedData['card_level_result_code'])
            ->setEbzcCardLevelResult($authorizedData['card_level_result'])
            ->setEbzcCardCodeResult($authorizedData['card_code_result'])
            ->setEbzcCardCodeResultCode($authorizedData['card_code_result_code'])
            ->setEbzcBatchRefNumber($authorizedData['batch_ref_num'])
            ->setEbzcBatchNumber($authorizedData['batch_num'])
            ->setEbzcAuthcode($authorizedData['auth_code'])
            ->setEbzcAvsResult($authorizedData['avs_result'])
            ->setEbzcAvsResultCode($authorizedData['avs_result_code'])
            ->setEbzcAuthAmount($authorizedData['auth_amount'])
            ->setEbzcPaymentStatus($authorizedData['payment_status'])
            ->setEbzcPaymentStatusCode($authorizedData['payment_status_code'])
            ->setBillToCustomer($ebizCustomerNumber)
        ;

        /* add the special ebzc fields to the database */
          $payment->getMethodInstance()->getInfoInstance()
        ->setEbzcCustId($ebizCustomerNumber);
        $payment->getMethodInstance()
            ->getInfoInstance()
            ->setEbzcMethodId($ebzcPaymentMethodId);

        $payment->getMethodInstance()
            ->getInfoInstance()
            ->setPaymentToken($ebzcPaymentMethodId)
        ;
        $payment->getMethodInstance()
            ->getInfoInstance()
            ->setEbzcSavePayment($payment->getAdditionalInformation('ebzc_save_payment'));
        $payment->getMethodInstance()
            ->getInfoInstance()
            ->setEbzcOption($payment->getAdditionalInformation('ebzc_option'));

        $payment->getMethodInstance()
            ->getInfoInstance()
            ->setEbzcAvsStreet($payment->getAdditionalInformation('ebzc_avs_street'));
        $payment->getMethodInstance()
            ->getInfoInstance()
            ->setEbzcAvsZip($payment->getAdditionalInformation('ebzc_avs_zip'));
        $payment->getMethodInstance()
            ->getInfoInstance()
            ->setCcType($payment->getAdditionalInformation('cc_type'));
        $payment->getMethodInstance()
            ->getInfoInstance()
            ->setCcType2($payment->getAdditionalInformation('cc_type_2'));

        $payment->getMethodInstance()
            ->getInfoInstance()
            ->setBillToCustomer($ebizCustomerNumber)
            ;

        /**
         * if is error occurred then void the transaction
         */
        if ($isError === true) {
            return false;
        }

        $transType = PaymentInterface::PAYMENT_TRANSACTION_TYPE_AUTHORIZE;
        $transApi = $this->soapApiModel;

        $transactionId = $transApi->getData('refnum');

        /** @var  $transData */
        $transData = [
            "resultcode" => $transApi->getData('resultcode') ??
                PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_ERROR,
            "refnum" => $transactionId ?? "",
            "transtype" => $resultDataResponse = $transApi->getData('transtype') ??
                PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_ERROR
        ];
        // dump($payment); throw new LocalizedException(__("exception"));
        /**
         * prepare and render Response
         */
        $this->prepareAndRenderResponse($payment, $transData, $transType);

        return $this;
    }

    /**
     * Prepare GraphQL Authorized Data
     *
     * @param InfoInterface|null $payment
     * @return array
     */
    public function prepareGraphQLAuthorizedData(?InfoInterface $payment = null)
    {
        /** @var  $graphQLPaymentParams */
        $graphQLPaymentParams = $this->paymentInfoParams["payment_params"] ?? [];

        /** @var  $graphQLWebFormPaymentParams */
        $graphQLWebFormPaymentParams = [];

        if (isset($graphQLPaymentParams['ebiz_webform'])) {
            $graphQLWebFormPaymentParams = $graphQLPaymentParams['ebiz_webform'];
        }
        /** @var  $resultCode */
        $resultCode = PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_DECLINED;

        if (
            isset($graphQLWebFormPaymentParams['TranResult']) &&
            $graphQLWebFormPaymentParams['TranResult'] === "Approved"
        ) {
            $resultCode = PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_AUTHENTICATED;
        }
        /** @var  $sessionCustomerId */
        $sessionCustomerId = $graphQLWebFormPaymentParams['user_id'] ?? '';

        /**
         * Transaction Auth Data
         */
        $transactionAuthData = [
            'batch_num' => '',
            'avs_result' => '',
            'is_duplicate' => '',
            'cvv2_result' => '',
            'vpas_result_code' => '',
            'convertedamount' => '',
            'convertedamountcurrency' => '',
            'conversionrate' => '',
            'error' => '',
            'errorcode' => '',
            'custnum' => $graphQLPaymentParams['ebzc_cust_id'] ?? $sessionCustomerId,
            'authcode' => $graphQLWebFormPaymentParams['AuthCode'] ?? '',
            'refnum' => $graphQLWebFormPaymentParams['TranRefNum'] ?? '',
            'avs_result_code' => '',
            'cvv2_result_code' => '',
            'resultcode' => $resultCode,
            'result' => $graphQLWebFormPaymentParams['TranResultCode'] ?? '',
            'command' => $graphQLPaymentParams['command'] ?? PaymentInterface::EBIZCHARGE_COMMAND_SALE,
            'avs' => '',
            'cvv2' => '',
            'acsurl' => '',
            'pareq' => '',
            'card_level_result_code' => '',
            'card_level_result' => '',
            'card_code_result' => '',
            'card_code_result_code' => '',
            'batch_ref_num' => '',
            'auth_code' => $graphQLWebFormPaymentParams['AuthCode'] ?? '',
            'auth_amount' => $graphQLWebFormPaymentParams['AuthAmount'] ?? '',
            'payment_status' => $graphQLWebFormPaymentParams['TranResult'] ?? '',
            'payment_status_code' => $graphQLWebFormPaymentParams['TranResult'] ?? '',
        ];

        return $transactionAuthData;
    }

    /**
     * Prepare Transaction Data
     *
     * @param array $transactionData
     * @return array|string[]
     */
    public function prepareTransactionData($transactionData = [])
    {
        return [
            'refnum' => isset($transactionData['ref_num']) ? $transactionData['ref_num'] : '',
            'cvv2_result_code' => $transactionData['card_code_result_code'] ?? '',
            'custnum' => isset($transactionData['customer_token']) ? $transactionData['customer_token'] : '',
            'resultcode' => isset($transactionData['result_code']) ? $transactionData['result_code'] : '',
            'result' => isset($transactionData['result']) ? $transactionData['result'] : '',
            'is_duplicate' => isset($transactionData['is_duplicate']) ? $transactionData['is_duplicate'] : '',
            'errorcode' => isset($transactionData['error_code']) ? $transactionData['error_code'] : '',
            'error' => isset($transactionData['error']) ? $transactionData['error'] : '',
            'command' => PaymentInterface::EBIZCHARGE_COMMAND_SALE,
            'card_level_result_code' => isset($transactionData['']) ? $transactionData[''] : '',
            'card_level_result' => isset($transactionData['']) ? $transactionData[''] : '',
            'card_code_result' => $transactionData['card_code_result'] ?? '',
            'card_code_result_code' => $transactionData['card_code_result_code'] ?? '',
            'batch_ref_num' => isset($transactionData['batch_ref_num']) ? $transactionData['batch_ref_num'] : '',
            'batch_num' => isset($transactionData['batch_num']) ? $transactionData['batch_num'] : '',
            'auth_code' => isset($transactionData['auth_code']) ? $transactionData['auth_code'] : '',
            'authcode' => isset($transactionData['auth_code']) ? $transactionData['auth_code'] : '',
            'avs_result' => isset($transactionData['avs_result']) ? $transactionData['avs_result'] : '',
            'avs_result_code' => isset($transactionData['avs_result_code']) ? $transactionData['avs_result_code'] : '',
            'auth_amount' => isset($transactionData['auth_amount']) ? $transactionData['auth_amount'] : '',
            'payment_status' => isset($transactionData['status']) ? $transactionData['status'] : '',
            'payment_status_code' => isset($transactionData['status_code']) ? $transactionData['status_code'] : ''
        ];
    }

    /**
     * @param InfoInterface|null $payment
     * @return array
     * @throws Exception
     */
    public function prepareWebHostFormAuthorizedData(?InfoInterface $payment = null)
    {
        if (!$payment) {
            return $this;
        }
        $additionInfo = $payment->getAdditionalInfo();
        $paymentOptionType = isset($this->paymentInfoParams["ebzc_option_type"]) ? $this->paymentInfoParams["ebzc_option_type"] : "";
        $ebizOption = isset($this->paymentInfoParams["ebzc_option"]) ? $this->paymentInfoParams["ebzc_option"] : "";
        $ebizOptionType = isset($this->paymentInfoParams["ebzc_option_type"]) ? $this->paymentInfoParams["ebzc_option_type"] : "";
        $webHostedTransactionParams = (array)json_decode(isset($this->paymentInfoParams["transaction_info"]) ? $this->paymentInfoParams["transaction_info"] : "");

        $storeId = $this->getStore()->getId();

        /**
         * Setting the surcharge amount if surcharge enabled
         */
        $this->setSurchargeTotals($payment, $webHostedTransactionParams);


        $storeId = $this->ebizConfig->getStoreId();
        $order = $payment->getOrder();
        $authAmount = $order->getGrandTotal();
        $customerToken = isset($webHostedTransactionParams["CustToken"]) && !empty($webHostedTransactionParams["CustToken"]) ? $webHostedTransactionParams["CustToken"] : "";
        $ebizCustomerId = isset($webHostedTransactionParams["ebzc_cust_id"]) && !empty($webHostedTransactionParams["ebzc_cust_id"]) ? $webHostedTransactionParams["ebzc_cust_id"] : "";
        $paymentMethodId = isset($webHostedTransactionParams["PmToken"]) && !empty($webHostedTransactionParams["PmToken"]) ? $webHostedTransactionParams["PmToken"] : "";

        $webHostedFormPaymentParams['TranResult'] = "Approved";
        $webHostedTransactionParams["MaskedCC"] = isset($webHostedTransactionParams["MaskedCC"]) && !empty($webHostedTransactionParams["MaskedCC"]) ? $webHostedTransactionParams["MaskedCC"] : "";
        $transResultCode = isset($webHostedTransactionParams["TranResultCode"]) && !empty($webHostedTransactionParams["TranResultCode"]) ? $webHostedTransactionParams["TranResultCode"] : "";

        $accountNumber = $webHostedTransactionParams["MaskedCC"] ?? "";
        /** @var  $paymentMethod */
        $paymentMethod = $this->customerFactory->create()->getPaymentMethodProfileById($paymentMethodId, $customerToken);
        $pmCardNumber = isset($paymentMethod["CardNumber"]) ? $paymentMethod["CardNumber"] : $accountNumber;
        $pmMethodType = isset($paymentMethod["MethodType"]) ? $paymentMethod["MethodType"] : "";
        $pmMethodID = isset($paymentMethod["MethodID"]) ? $paymentMethod["MethodID"] : $paymentMethodId;
        $pmMethodName = isset($paymentMethod["MethodName"]) ? $paymentMethod["MethodName"] : "";
        $pmAccountHolderName = isset($paymentMethod["AccountHolderName"]) ? $paymentMethod["AccountHolderName"] : "";
        $pmAvsStreet = isset($paymentMethod["AvsStreet"]) ? $paymentMethod["AvsStreet"] : "";
        $pmAvsZip = isset($paymentMethod["AvsZip"]) ? $paymentMethod["AvsZip"] : "";
        $pmAccount = isset($paymentMethod["Account"]) ? $paymentMethod["Account"] : "";
        $pmAccountType = isset($paymentMethod["AccountType"]) ? $paymentMethod["AccountType"] : "";
        $pmCardExpiry = explode("-", isset($paymentMethod["CardExpiration"]) ? $paymentMethod["CardExpiration"] : "");
        $pmCcExpiryYear = isset($pmCardExpiry[0]) ? $pmCardExpiry[0] : "";
        $pmCcExpiryMonth = isset($pmCardExpiry[1]) ? $pmCardExpiry[1] : "";

        /** @var  $webHostedFormPaymentParams */
        $webHostedFormPaymentParams = [];
        $paymentError = "";
        $paymentErrorCode = "";
        //authorize_capture, authorize
        $paymentAction = $this->ebizConfig->getPaymentAction($storeId);
        $paymentCommand = "sale";


        if ($paymentAction === "authorize_capture") {
            $paymentCommand = PaymentInterface::EBIZCHARGE_COMMAND_CAPTURE;
        }
        if ($paymentAction === "authorize") {
            $paymentCommand = PaymentInterface::EBIZCHARGE_COMMAND_AUTHONLY;
        }
        $webHostedFormPaymentParams['command'] = $paymentCommand;
        $webHostedFormPaymentParams['AuthCode'] = "0000";
        $webHostedFormPaymentParams['AuthAmount'] = (string)$authAmount;
        $webHostedFormPaymentParams['MaskedCC'] = isset($webHostedTransactionParams["MaskedCC"]) && !empty($webHostedTransactionParams["MaskedCC"]) ? $webHostedTransactionParams["MaskedCC"] : $paymentMethodId;


        $customerToken = isset($webHostedTransactionParams["CustToken"]) && !empty($webHostedTransactionParams["CustToken"]) ? $webHostedTransactionParams["CustToken"] : "";
        $ebizCustomerId = isset($webHostedTransactionParams["ebzc_cust_id"]) && !empty($webHostedTransactionParams["ebzc_cust_id"]) ? $webHostedTransactionParams["ebzc_cust_id"] : "";
        $paymentMethodId = isset($webHostedTransactionParams["PmToken"]) && !empty($webHostedTransactionParams["PmToken"]) ? $webHostedTransactionParams["PmToken"] : "";
        $payByType = isset($webHostedTransactionParams["PayByType"]) && !empty($webHostedTransactionParams["PayByType"]) ? $webHostedTransactionParams["PayByType"] : $pmMethodType;

        //var_dump($this->paymentInfoParams, $webHostedTransactionParams);
        $isSavePayment = "new";

        if (strtolower($paymentOptionType) === strtolower(PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_HOSTED_FORM_TOKENIZED_ONLY)) {
            if (isset($webHostedTransactionParams["PayByType"]) && strtolower($webHostedTransactionParams["PayByType"]) === "cc") {
                $ccType = isset($webHostedTransactionParams["CCType"]) && !empty($webHostedTransactionParams["CCType"]) ? $webHostedTransactionParams["CCType"] : $pmMethodType;
                $ccType2 = $this->ebizConfig->getLongCcType($ccType);

                $payment->setCcNumber($pmCardNumber)
                    ->setCcOwner($pmAccountHolderName)
                    ->setCcOwner($pmAccountHolderName)
                    ->setEbizOptionType($pmMethodType)
                    ->setEbzcOption("cc")
                    ->setEbzcMethodId($pmMethodID)
                    ->setEbzcCustId($ebizCustomerId)
                    ->setCcType($ccType)
                    ->setCcType2($ccType2)
                    ->setCcLast4($webHostedFormPaymentParams['MaskedCC'])
                    ->setCcExpYear($pmCcExpiryYear)
                    ->setCcExpMonth($pmCcExpiryMonth)
                    ->setEbzcAvsStreet($pmAvsStreet)
                    ->setEbzcAvsZip($pmAvsZip)
                    ->setCcTransId("00000")
                    ->setEbzAuthAmount($authAmount);
                $isSavePayment = "saved";
                $ebizOption = "cc";
                $ebizOptionType = $pmMethodType;
                $this->paymentInfoParams["ebiz_option"] = $ebizOption;
            }
            if (isset($webHostedTransactionParams["PayByType"]) && strtolower($webHostedTransactionParams["PayByType"]) === "echeck") {
                $payment->setCcNumber($pmAccount)
                    ->setCcOwner($pmAccountHolderName)
                    ->setEbizOptionType($pmMethodType)
                    ->setEbzcOption($pmMethodType)
                    ->setEbzcMethodId($pmMethodID)
                    ->setCcLast4($webHostedFormPaymentParams['MaskedCC'])
                    ->setEbzcCustId($ebizCustomerId)
                    ->setCcType($pmAccountType)
                    ->setCcType2($this->ebizConfig->getLongCcType($pmAccountType))
                    ->setCcTransId("00000")
                    ->setEbzAuthAmount($authAmount);
                $isSavePayment = "saved";
                $ebizOption = "echeck";
                $ebizOptionType = $pmMethodType;
                $this->paymentInfoParams["ebiz_option"] = $ebizOption;
            }

            //entity_id | ebzc_option | cc_type | cc_last_4 | cc_exp_year | cc_exp_month | cc_trans_id | ebzc_cust_id | ebzc_method_id | ebzc_auth_amount

            $transType = PaymentInterface::PAYMENT_TRANSACTION_TYPE_AUTHORIZE;
            $transApi = $this->soapApiModel;
            $transApi->setData("refnum", $paymentMethodId);
            $transApi->setData("transtype", $transType);
            $transApi->setData("resultcode", PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_AUTHENTICATED);

            //  var_dump($this->paymentInfoParams, $paymentMethod);

            if (!$customerToken) {
                $webHostedFormPaymentParams['TranResult'] = "Error";
                $paymentError = "Payment authorized error";
                $paymentErrorCode = "Error";
                $webHostedFormPaymentParams['AuthCode'] = "ERROR";
                $transApi->setData("refnum", "");
                $transApi->setData("transtype", PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_ERROR);
                $transApi->setData("resultcode", PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_ERROR);
            }
        } else {
            $webHostedFormPaymentParams['AuthAmount'] = isset($webHostedTransactionParams["AuthAmount"]) && !empty($webHostedTransactionParams["AuthAmount"]) ? $webHostedTransactionParams["AuthAmount"] : $authAmount;
            $webHostedFormPaymentParams['TranRefNum'] = isset($webHostedTransactionParams["TranRefNum"]) && !empty($webHostedTransactionParams["TranRefNum"]) ? $webHostedTransactionParams["TranRefNum"] : "";
            $webHostedFormPaymentParams['AuthCode'] = isset($webHostedTransactionParams["AuthCode"]) && !empty($webHostedTransactionParams["AuthCode"]) ? $webHostedTransactionParams["AuthCode"] : "";
            $webHostedFormPaymentParams['TranResult'] = isset($webHostedTransactionParams["TranResult"]) && !empty($webHostedTransactionParams["TranResult"]) ? $webHostedTransactionParams["TranResult"] : "";
            $webHostedFormPaymentParams['TranResultCode'] = isset($webHostedTransactionParams["TranResultCode"]) && !empty($webHostedTransactionParams["TranResultCode"]) ? $webHostedTransactionParams["TranResultCode"] : PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_AUTHENTICATED;

            if (isset($webHostedTransactionParams["PayByType"]) && strtolower($webHostedTransactionParams["PayByType"]) === "echeck") {
                $payment->setCcNumber($pmAccount)
                    ->setCcOwner($pmAccountHolderName)
                    ->setEbizOptionType($pmMethodType)
                    ->setEbzcOption($pmMethodType)
                    ->setEbzcMethodId($pmMethodID)
                    ->setCcLast4($webHostedFormPaymentParams['MaskedCC'])
                    ->setEbzcCustId($ebizCustomerId)
                    ->setCcType($pmAccountType)
                    ->setCcType2($this->ebizConfig->getLongCcType($pmAccountType))
                    ->setCcTransId($webHostedFormPaymentParams['TranRefNum'])
                    ->setEbzAuthAmount($authAmount);
                $isSavePayment = "saved";
                $ebizOption = "echeck";
                $ebizOptionType = $pmMethodType;
                $this->paymentInfoParams["ebiz_option"] = $ebizOption;
            }
            if (isset($webHostedTransactionParams["PayByType"]) && strtolower($webHostedTransactionParams["PayByType"]) === "cc") {
                $ccType = isset($webHostedTransactionParams["CCType"]) && !empty($webHostedTransactionParams["CCType"]) ? $webHostedTransactionParams["CCType"] : $pmMethodType;

                $payment->setCcNumber($pmCardNumber)
                    ->setCcOwner($pmAccountHolderName)
                    ->setCcOwner($pmAccountHolderName)
                    ->setEbizOptionType($pmMethodType)
                    ->setEbzcOption("cc")
                    ->setEbzcMethodId($pmMethodID)
                    ->setEbzcCustId($ebizCustomerId)
                    ->setCcType($ccType)
                    ->setCcType2($this->ebizConfig->getLongCcType($ccType))
                    ->setCcLast4($webHostedFormPaymentParams['MaskedCC'])
                    ->setCcExpYear($pmCcExpiryYear)
                    ->setCcExpMonth($pmCcExpiryMonth)
                    ->setEbzcAvsStreet($pmAvsStreet)
                    ->setEbzcAvsZip($pmAvsZip)
                    ->setCcTransId($webHostedFormPaymentParams['TranRefNum'])
                    ->setEbzAuthAmount($authAmount);
                $isSavePayment = "saved";
                $ebizOption = "cc";
                $ebizOptionType = $pmMethodType;
                $this->paymentInfoParams["ebiz_option"] = $ebizOption;
            }


            $transType = PaymentInterface::PAYMENT_TRANSACTION_TYPE_AUTHORIZE;
            $transApi = $this->soapApiModel;
            $transApi->setData("refnum", $webHostedFormPaymentParams['TranRefNum']);
            $transApi->setData("transtype", $transType);
            $transApi->setData("resultcode", $webHostedFormPaymentParams['TranResultCode']);

            if (!$customerToken && $transResultCode !== PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_AUTHENTICATED) {
                $webHostedFormPaymentParams['TranResult'] = "Error";
                $paymentError = "Payment authorized error";
                $paymentErrorCode = "Error";
                $webHostedFormPaymentParams['AuthCode'] = "ERROR";
                $transApi->setData("refnum", "");
                $transApi->setData("transtype", PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_ERROR);
                $transApi->setData("resultcode", PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_ERROR);
            }
        }

        $paymentTokenNumber = $customerToken . "~" . $pmMethodID ?? "000000~0000";

        $payment->getMethodInstance()
            ->getInfoInstance()
            ->setEbzcMethodId($pmMethodID);
        $payment->getMethodInstance()
            ->getInfoInstance()
            ->setEbzcSavePayment($isSavePayment);
        $payment->getMethodInstance()
            ->getInfoInstance()
            ->setEbzcOption($ebizOption);
        $payment->getMethodInstance()
            ->getInfoInstance()
            ->setEbzcOptionType($ebizOptionType);
        $payment->getMethodInstance()
            ->getInfoInstance()
            ->setEbzcAvsStreet($pmAvsStreet);
        $payment->getMethodInstance()
            ->getInfoInstance()
            ->setEbzcAvsZip($pmAvsZip);
        $payment->setAdditionalInformation('ebzc_option', $ebizOption);
        $payment->setAdditionalInformation('ebzc_option_type', $ebizOptionType);
        $payment->setAdditionalInformation('ebzc_method_id', $pmMethodID);
        $payment->setAdditionalInformation('token_number', $paymentTokenNumber);
        $payment->setAdditionalInformation('cc_type_2', $this->ebizConfig->getLongCcType($ccType));


        /** @var  $resultCode */
        $resultCode = PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_ERROR;
        if (
            isset($webHostedFormPaymentParams['TranResult']) &&
            $webHostedFormPaymentParams['TranResult'] === "Approved"
        ) {
            $resultCode = PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_AUTHENTICATED;
        }

        /**
         * Transaction Auth Data
         */
        $transactionAuthData = [
            'batch_num' => '',
            'avs_result' => '',
            'payment_method_id' => $paymentMethodId,
            'is_duplicate' => '',
            'cvv2_result' => '',
            'vpas_result_code' => '',
            'convertedamount' => '',
            'convertedamountcurrency' => '',
            'conversionrate' => '',
            'error' => $paymentError,
            'errorcode' => $paymentErrorCode,
            'custnum' => $webHostedFormPaymentParams['custnum'] ?? $ebizCustomerId,
            'authcode' => $webHostedFormPaymentParams['AuthCode'] ?? '',
            'refnum' => $webHostedFormPaymentParams['TranRefNum'] ?? '',
            'avs_result_code' => '',
            'cvv2_result_code' => '',
            'resultcode' => $resultCode,
            'result' => $webHostedFormPaymentParams['TranResultCode'] ?? '',
            'command' => $webHostedFormPaymentParams['command'] ?? PaymentInterface::EBIZCHARGE_COMMAND_SALE,
            'avs' => '',
            'cvv2' => '',
            'cc_type_2' => $this->ebizConfig->getLongCcType($ccType),
            'acsurl' => '',
            'pareq' => '',
            'card_level_result_code' => '',
            'card_level_result' => '',
            'card_code_result' => '',
            'card_code_result_code' => '',
            'batch_ref_num' => '',
            'auth_code' => $webHostedFormPaymentParams['AuthCode'] ?? '',
            'auth_amount' => $webHostedFormPaymentParams['AuthAmount'] ?? '',
            'payment_status' => $webHostedFormPaymentParams['TranResult'] ?? '',
            'payment_status_code' => $webHostedFormPaymentParams['TranResult'] ?? '',
        ];
        $webHostedFormPaymentParams["RefNum"] = $transactionAuthData["refnum"];
        $webHostedFormPaymentParams["ResultCode"] = $transactionAuthData["resultcode"];
        $webHostedFormPaymentParams["Result"] = $transactionAuthData["result"];
        $webHostedFormPaymentParams["CustNum"] = $transactionAuthData["custnum"];
        $webHostedFormPaymentParams["AuthAmount"] = $transactionAuthData["auth_amount"];
        $webHostedFormPaymentParams["Status"] = $transactionAuthData["payment_status"];
        $webHostedFormPaymentParams["AuthCode"] = $transactionAuthData["auth_code"];
        $webHostedFormPaymentParams["StatusCode"] = $transactionAuthData["payment_status_code"];

        $this->soapApiModel->setTransactionData($payment);

        return $transactionAuthData;
    }

    /**
     * @param InfoInterface|null $payment
     * @param $webHostedTransactionParams
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function setSurchargeTotals(?InfoInterface $payment = null, $webHostedTransactionParams = [])
    {
        try {
            $storeId = $this->ebizConfig->getStoreId();
            $quote = $this->checkoutSession->getQuote();

            $surchargeSettings = $this->customerFactory->create()->getSurchargeSettings($storeId);
            $isSurchargeEnabled = (bool)$surchargeSettings[SurchargeInterface::EBIZ_SURCHARGE_ENABLED];
            // dump($webHostedTransactionParams, $quote->debug()); throw new LocalizedException(__("except"));

            if ($payment && $quote->getId() && $isSurchargeEnabled !== false) {
                $surchargeAmount = 0;
                /**
                 * Quote item id
                 */
                $quote = $this->checkoutSession->getQuote();
                $quoteId = $quote->getId();
                $quoteRepository = $this->quoteRepository->get($quoteId);
                $this->ebizchargeLogger->addInfo(__("Surcharge Amount " . $surchargeAmount . ", quoteID: " . $quote->getId() . " enable: " . $isSurchargeEnabled));

                $storeId = $quote->getStoreId();

                $surchargePercentage = $surchargeSettings[SurchargeInterface::EBIZ_SURCHARGE_PERCENTAGE] ?? 0;
                $quoteGrandTotal = $quote->getGrandTotal() ?? 0;
                $quoteBaseGrandTotal = $quote->getBaseGrandTotal() ?? 0;

                $authAmount = isset($webHostedTransactionParams["AuthAmount"]) ? $webHostedTransactionParams["AuthAmount"] : 0;
                $extraAuthAmount = (float)$authAmount - (float)$quoteGrandTotal;

                if ($extraAuthAmount > 0) {
                    $surchargeAmount = $extraAuthAmount;
                }
                $this->ebizchargeLogger->addInfo(__("Surcharge Amount " . $surchargeAmount));

                if ($surchargeAmount > 0) {

                    /**
                     * set Surcharge Amount for Quote
                     */

                    $quoteRepository->setEcSurchargeAmount($surchargeAmount)
                        ->setEcSurchargePercentage($surchargePercentage)
                        ->save();
                    $currentOrder = $payment->getOrder();
                    /**
                     * set Surcharge Amount for Order
                     */
                    $currentOrder->setEcSurchargeAmount($surchargeAmount)
                        ->setEcSurchargePercentage($surchargePercentage)
                        ->save();
                    /**
                     * set Surcharge Amount for Payment
                     */
                    $payment->setEcSurchargeAmount($surchargeAmount)
                        ->setEcSurchargePercentage($surchargePercentage);
                }
            }
        } catch (Exception $exception) {
            $this->ebizchargeLogger->addCritical(__("Exception occurred during adding surcharge error: " . $exception->getMessage()));
            throw new LocalizedException(__("Exception occurred during adding surcharge error: " . $exception->getMessage()));
        }
    }

    /**
     * @param InfoInterface|null $payment
     * @param $resultResponse
     * @param $transType
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function prepareAndRenderResponse(
        ?InfoInterface $payment = null,
        $resultResponse = null,
        $transType = null
    ) {
        if (!$payment) {
            return;
        }
        try {

            /** @var  $authorizedDataResultCode */
            $authorizedDataResultCode = isset($resultResponse['resultcode']) ? (string)$resultResponse['resultcode'] :
                PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_ERROR;
            $authorizedTranType = isset($resultResponse['transtype']) ? (string)$resultResponse['transtype'] : "order";
            $storeId = $this->getStoreId() ?? "0";

            // $resultResponse['refnum'] = $payment->getParentTransactionId();


            /** if payment is authenticated */
            if ($authorizedDataResultCode === PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_AUTHENTICATED) {
                $payment->setLastTransId($resultResponse['refnum']);
                // $payment->setBaseAmountPaid($payment->getBaseAmountAuthorized());
                // $payment->setAmountPaid($payment->getAmountOrdered());
                $payment->setBaseShippingCaptured($payment->getShippingAmount());
                $payment->setShippingCaptured($payment->getShippingAmount());
                // $payment->setBaseAmountPaidOnline($payment->getBaseAmountAuthorized());
                $payment->setEbzcMethodId($payment->getAdditionalInformation("ebzc_method_id"));

                if (
                    !$payment->getParentTransactionId()
                    || $resultResponse['refnum'] !== $payment->getParentTransactionId()
                ) {
                    $payment->setTransactionId($resultResponse['refnum']);
                }

                /** closing the transaction
                 * either capture or authorize amount
                 * from Config Level
                 */
                $payment->setIsTransactionClosed(0)
                    ->setTransactionAdditionalInfo('trans_id', $resultResponse['refnum']);

                /** setting Payment Approved
                 * and make Transaction
                 */
                $payment->setStatus($transType);

                $savePaymentMethod = $this->getIsSavePaymentMethod($payment) ?? false;
                $isSaveGuestUsers = $this->ebizConfig->isSaveGuestUsers($storeId);
                if (!$this->isSavePaymentMethod) {
                    $this->isSavePaymentMethod = (bool)$savePaymentMethod;
                }

                if (!$this->customerId && $isSaveGuestUsers) {
                    $this->customerId = $payment->getOrder()->getQuoteId() ?? "0";
                }
                /**
                 * if Customer ID then add new payment Method
                 */
                if (
                    $this->customerId &&
                    $this->paymentActionType === PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_NEW &&
                    $this->isSavePaymentMethod &&
                    $authorizedTranType !== "invoice"
                ) {
                    /**
                     * Add new Payment Method
                     */
                    $methodResponse = $this->addNewCustomerPaymentMethod($this->customerId, $payment);

                    $methodId = $methodResponse['response']['payment_method_id'] ??
                        ($methodResponse['response']['bank_method_id'] ?? 0);

                    $payment->getMethodInstance()->getInfoInstance()->setEbzcMethodId($methodId);
                    $payment->setEbzcMethodId($methodId);
                    $payment->getMethodInstance()->getInfoInstance()->setEbzcCustId($this->customerId);
                    $payment->setEbzcCustId($this->customerId);
                    $payment->setAdditionalInformation("ebzc_method_id", $methodId);
                    $payment->setAdditionalInformation("ebzc_cust_id", $this->customerId);
                    $payment->setAdditionalInformation("payment_token", $methodId);
                    $payment->setAdditionalInformation("paymentToken", $methodId);


                    if ($this->isRecurring === true) {
                        /**
                         * check if recurring exists
                         */
                        $isRecurringExists = $this->soapApiModel->isRecurringExists($payment);

                        if (!$isRecurringExists) {

                            /** push recurring in case of the
                             * Transaction, recurring
                             * enabled and Is recurring exits
                             */
                            $transactionData = $this->soapApiModel->getTransactionData();
                            $customerTransactionParams = $this->soapApiModel->prepareCustomerTransactionParams(
                                $payment,
                                $this->isRecurring
                            );
                            $paymentMethodId = $methodId;

                            if ($payment->getExcludeAmount() > 0) {
                                $customerTransactionParams['Details']['Description'] = 'This is a recurring order #' .
                                    $customerTransactionParams['Details']['OrderID'] . ' Amount [' .
                                    $payment->getExcludeAmount() .
                                    '] already paid. Remaining amount will be charge now.';
                                $customerTransactionParams['Details']['Amount'] =
                                    ($customerTransactionParams['Details']['Amount'] - $payment->getExcludeAmount());
                            }

                            /** @var  $orderRecurringParams */
                            $orderRecurringParams = [
                                'payment' => $payment,
                                'transaction' => $transactionData,
                                'method_id' => $paymentMethodId,
                                'customer_transaction_params' => $customerTransactionParams
                            ];

                            /**create a $recurring order */
                            $recurring = $this->soapApiModel->creatRecurringOrder($orderRecurringParams);
                            $this->ebizchargeLogger->addInfo(__("Success, the recurring order has been added "));
                        }
                    }
                }

                $isPaymentAuthorized = true;
            } elseif ($authorizedDataResultCode === PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_DECLINED) {
                $this->ebizchargeLogger->addError(__($this->soapApiModel->getPaymentError()['error']));
                throw new LocalizedException(__('Payment authorization transaction has been declined:  ' .
                    $this->soapApiModel->getPaymentError()['error']));
            } elseif ($authorizedDataResultCode === PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_ERROR) {

                /** logging the error */
                $this->ebizchargeLogger->addError(__('Payment authorization error:  ' .
                    $this->soapApiModel->getPaymentError()['error'] . '(' .
                    $this->soapApiModel->getPaymentError()['errorcode'] . ')'));
                throw new LocalizedException(__('Payment authorization error:  ' .
                    $this->soapApiModel->getPaymentError()['error'] . '(' .
                    $this->soapApiModel->getPaymentError()['errorcode'] . ')'));
            } else {
                /** logging the error */
                $this->ebizchargeLogger->addError(__('Payment authorization error:  ' .
                    $this->soapApiModel->getPaymentError()['error'] . '(' .
                    $this->soapApiModel->getPaymentError()['errorcode'] . ')'));
                throw new LocalizedException(__('Payment authorization error:  ' .
                    $this->soapApiModel->getPaymentError()['error'] . '(' .
                    $this->soapApiModel->getPaymentError()['errorcode'] . ')'));
            }
        } catch (PaymentException $exception) {
            $this->ebizchargeLogger->addCritical("Payment authorization error: " . $exception->getMessage());
            throw new LocalizedException(__('Payment authorization error:  ' .
                $this->soapApiModel->getPaymentError()['error'] . '(' .
                $this->soapApiModel->getPaymentError()['errorcode'] . ')'));
        }
    }

    /**
     * Get Is Save Payment Method
     *
     * @param InfoInterface $payment
     * @return bool
     * @throws NoSuchEntityException
     */
    public function getIsSavePaymentMethod(?InfoInterface $payment = null): bool
    {
        /** @var $isSavePayment */
        $isSavePayment = false;
        $paymentAdditionalInfo = $payment->getAdditionalInformation() ?? [];

        $ebizOptionType = isset($paymentAdditionalInfo['ebzc_option_type']) && $paymentAdditionalInfo['ebzc_option_type']
            ? $paymentAdditionalInfo['ebzc_option_type'] : "credit_card";
        $isSavePaymentMethodSelected = isset($paymentAdditionalInfo['ebzc_save_payment']) &&
        $paymentAdditionalInfo['ebzc_save_payment'] ? $paymentAdditionalInfo['ebzc_save_payment'] : false;

        $storeId = $this->getStoreId();
        $isConfigSaveAllowed = false;
        $configSaveCardsEnabled = $this->ebizConfig->saveCard($storeId);
        $configSaveBankAccountsEnabled = $this->ebizConfig->getIsSaveBankAccounts($storeId);

        if (strtolower($ebizOptionType) === strtolower(PaymentInterface::ACH)) {
            if ($configSaveBankAccountsEnabled) {
                $isConfigSaveAllowed = true;
            }
        }
        if (strtolower($ebizOptionType) === strtolower(PaymentInterface::EBIZCHARGE_METHOD_TYPE_CREDIT_CARD)) {
            if ($configSaveCardsEnabled) {
                $isConfigSaveAllowed = true;
            }
        }
        if ($isSavePaymentMethodSelected && $isConfigSaveAllowed) {
            $isSavePayment = true;
        }

        return $isSavePayment;
    }

    /**
     * Add New Customer Payment Method
     *
     * @param mixed $customerId
     * @param InfoInterface|null $payment
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function addNewCustomerPaymentMethod(mixed $customerId = null, ?InfoInterface $payment = null)
    {
        /** @var  $newPaymentMethod */
        $newPaymentMethod = [];

        /** @var  $customerFactory */
        $customerFactory = $this->customerFactory->create()->load($customerId);
        /** @var  $customer */
        $customer = $customerFactory->load($customerId);
        if ($this->isSavePaymentMethod) {
            if (strtolower($this->paymentType) === strtolower(PaymentInterface::EBIZCHARGE_METHOD_TYPE_CREDIT_CARD)) {
                $newPaymentMethod = $customerFactory->addNewPaymentMethod($customerId, $this->paymentMethodParams);
            } elseif (strtolower($this->paymentType) === strtolower(PaymentInterface::EBIZCHARGE_METHOD_TYPE_ACH)) {
                $newPaymentMethod = $customerFactory->addCustomerBankAccount($customer, $this->paymentMethodParams);
            }
        }
        return $newPaymentMethod;
    }

    /**
     * Unset Payment Additional Info
     *
     * @param InfoInterface|null $payment
     * @return void
     * @throws LocalizedException
     */
    public function unsetPaymentAdditionalInfo(?InfoInterface $payment = null)
    {
        /** @var  $ccNumber */
        $ccNumber = $payment->getCcNumber() ?? $payment->getAdditionalInformation(
            PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_CC_NUMBER
        );
        $this->prepareMaskedCardNumber($ccNumber);
        $payment->setAdditionalInformation(
            PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_CC_NUMBER,
            $this->cardNumber
        );
        $payment->setAdditionalInformation(PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_CC_ID, "");
        //$payment->setAdditionalInformation(PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_CC_EXP_MONTH, "");
        //$payment->setAdditionalInformation(PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_CC_EXP_YEAR, "");
        //$this->ebizchargeLogger->addCritical(__("Payment Info: "), 100, $payment->getAdditionalInformation());
    }

    /**
     * Prepare Masked Card Number
     *
     * @param mixed $cardNumber
     * @return void
     */
    public function prepareMaskedCardNumber($cardNumber = null)
    {
        $this->cardNumber = "";
        if (!$this->cardNumber) {
            $length = strlen($cardNumber);
            if ($length > 0) {
                $this->cardNumber = substr_replace(
                    $cardNumber,
                    str_repeat('X', $length - 4),
                    0,
                    $length - 4
                );
            }
        }
    }

    /**
     * Get Cvv Card Code Response Codes
     *
     * @return array
     */
    public function getCvvCardCodeResponseCodes(): array
    {
        return $this->cvvCardCodePaymentResponses;
    }

    /**
     * Get Cavv Card Code Response Codes
     *
     * @return array
     */
    public function getCavvCardCodeResponseCodes(): array
    {
        return $this->cavvCardCodePaymentResponses;
    }

    /**
     * Get Avs Card Code Response Codes
     *
     * @return array
     */
    public function getAvsCardCodeResponseCodes(): array
    {
        return $this->avsCardCodePaymentResponses;
    }

    /**
     * Get Declined Card Code Response Codes
     *
     * @return array
     */
    public function getDeclinedCardCodeResponseCodes(): array
    {
        return $this->declineCardCodePaymentResponses;
    }

    /**
     * Set Payment Params
     *
     * @param array $paymentParams
     */
    public function setPaymentParams($paymentParams = [])
    {
        /** @var  paymentInfoParams */
        $this->paymentInfoParams = $paymentParams;
    }

    /**
     * Is Warned Cvv Avs Response
     *
     * @param string $cvvCardCodeResultCode
     * @param string $avsCardCodeResultCode
     * @return bool
     */
    public function isWarnedCvvAvsResponse(
        string $cvvCardCodeResultCode = "",
        string $avsCardCodeResultCode = ""
    ): bool {
        /** @var  $isWarnedResponse */
        $isWarnedResponse = true;
        if (
            $this->isValidCvvResponseCode($cvvCardCodeResultCode) &&
            $this->isValidAvsResponseCode($avsCardCodeResultCode)
        ) {
            $isWarnedResponse = false;
        }
        return $isWarnedResponse;
    }

    /**
     * Is Valid Cvv Card Code
     *
     * @param string $cardCodeCvvCode
     * @return bool
     */
    public function isValidCvvResponseCode(string $cardCodeCvvCode = ""): bool
    {
        /** @var  $isValidCvv */
        $isValidCvv = true;

        /** @var  $unValidCvvResponses */
        $unValidCvvResponses = $this->cvvCardCodePaymentResponses;

        if (count($unValidCvvResponses) > 0) {
            foreach ($unValidCvvResponses as $key => $unValidRespons) {
                if (strtolower($cardCodeCvvCode) === strtolower("m")) {
                    $isValidCvv = true;
                    break;
                }
                if (strtolower($cardCodeCvvCode) === strtolower($key)) {
                    $isValidCvv = false;
                }
            }
        }
        return $isValidCvv;
    }

    /**
     * Is Valid Avs Response Code
     *
     * @param string $cardCodeAvsCode
     * @return bool
     */
    public function isValidAvsResponseCode(string $cardCodeAvsCode = ""): bool
    {
        /** @var  $isValidAvs */
        $isValidAvs = true;
        $unValidAvsResponses = $this->avsCardCodePaymentResponses;

        if (count($unValidAvsResponses) > 0) {
            foreach ($unValidAvsResponses as $key => $unValidResponse) {
                if (strtolower($cardCodeAvsCode) === strtolower("yyy")) {
                    $isValidAvs = true;
                    break;
                }
                if (strtolower($cardCodeAvsCode) === strtolower($key)) {
                    $isValidAvs = false;
                }
            }
        }
        return $isValidAvs;
    }

    /**
     * Get Payment Params
     *
     * @return array
     */
    public function getPaymentParams(): array
    {
        return $this->paymentInfoParams;
    }

    /**
     * Captures payment.
     *
     * @param InfoInterface $payment
     * @param null|mixed $amount
     * @return Payment
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function capture(InfoInterface $payment, $amount = null)
    {
        /**
         * Get Store ID
         */
        $order = $payment->getOrder();
        $storeId = $order->getStoreId();

        if ($order->getEntityId()) {
            $order = $this->orderFactory->create()->load($order->getEntityId());
        }
        $isRecurring = $this->isRecurringActive($order);
        $this->isRecurring = $isRecurring;
        $surchargePercentage = (float)($order->getEcSurchargePercentage() ?? 0);
        $surchargeAmount = (float)($order->getEcSurchargePercentage() ?? 0);

        /** @var $amount */
        $amount = (float)($amount ?? $order->getTotalDue());
        $totalDue = (float)$order->getTotalDue();
        $grandTotal = (float)$order->getGrandTotal();
        $orderedItems = $order->getAllVisibleItems() ?? [];

        /** init the transactions  */
        $this->soapApiModel->initTransactionAPI($storeId);
        $this->soapApiModel->setTotalOrderedQty((float)($order->getTotalQtyOrdered() ?? 0));
        $isInvoice = $this->soapApiModel->isToInvoice($orderedItems);
        $orderPayment = $order->getPayment();
        $additionalInformation = $orderPayment ? $orderPayment->getAdditionalInformation() : [];
        $transactionInfo = isset($additionalInformation["transaction_info"]) ? (array)json_decode($additionalInformation["transaction_info"]) : [];
        $transactionId = isset($transactionInfo["TranRefNum"]) && !empty($transactionInfo["TranRefNum"]) ? $transactionInfo["TranRefNum"] : "";

        /* if we don't have a tran sid than we are need to authorize */
        if (!$payment->getParentTransactionId()) {
            $orderPayment = $order->getPayment();
            $additionalInformation = $orderPayment ? $orderPayment->getAdditionalInformation() : [];
            $transactionInfo = isset($additionalInformation["transaction_info"]) ? (array)json_decode($additionalInformation["transaction_info"]) : [];

            if (is_array($transactionInfo)) {
                $amount = isset($transactionInfo["AuthAmount"]) ? $transactionInfo["AuthAmount"] : $amount;
            }

            $command = PaymentInterface::EBIZCHARGE_COMMAND_SALE;
            $this->authMode = $command;
            $this->soapApiModel->setData('command', $command);
            $this->soapApiModel->setData('amount', $amount);
            $this->soapApiModel->setData('discount', abs((float)$order->getDiscountAmount()));

            //  if((float)$amount !== (float)$grandTotal) {
            $order->setEcSurchargeAmount((float)$amount - (float)$grandTotal)->save();
            $order->getPayment()->setEcSurchargeAmount((float)$amount - (float)$grandTotal)->save();
            //  }
            /** authorize method to run transaction */
            return $this->authorize($payment, $amount, $command);
        }

        /* we have already captured the original auth,  we need to do full sale */
        if (
            $payment->getLastTransId() &&
            $payment->getOrder()->getTotalDue() > 0 &&
            $grandTotal === $totalDue
        ) {
            $command = PaymentInterface::EBIZCHARGE_COMMAND_CAPTURE;
        } else {
            $command = PaymentInterface::EBIZCHARGE_COMMAND_QUICK_SALE;
        }


        /**
         * if ($surchargeAmount > 0 && !$checkSurchargeCaptured) {
         * $order->setGrandTotal($order->getGrandTotal() + $surchargeAmount);
         * $order->setBaseGrandTotal($order->getBaseGrandTotal() + $surchargeAmount);
         * $payment->setOrder($order);
         * }
         */
        return $this->quickSale($payment, $amount, $command);
    }

    /**
     * @param $order
     * @return bool
     */
    public function isRecurringActive($order = null)
    {
        $isRecurring = false;
        if (!$this->ebizConfig->isRecurringEnabled()) {
            return $isRecurring;
        }
        $orderItems = $order->getAllVisibleItems();

        if (count($orderItems) > 0) {
            foreach ($orderItems as $orderItem) {
                /** @var  $productOptions */
                $productOptions = $orderItem->getProductOptions();
                $infoBuyRequest = isset($productOptions['info_buyRequest']) ? $productOptions['info_buyRequest'] : [];

                if (
                    isset($infoBuyRequest['recurring']) && isset($infoBuyRequest['recurring']['rec_frequency']) &&
                    !empty($infoBuyRequest['recurring']['rec_frequency'])
                ) {
                    $isRecurring = true;
                }
            }
        }

        return $isRecurring;
    }

    /**
     * Authorize
     *
     * @param InfoInterface|null $payment
     * @param mixed $amount
     * @param mixed $command
     * @return $this|Payment|false
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function authorize(?InfoInterface $payment = null, $amount = null, $command = null)
    {
        $store = $this->getStore();
        $storeId = $payment->getOrder()->getStoreId();
        $customerToken = "";
        $paymentMethodId = "";
        $isSavedPayment = false;

        /** initializing the Transactions Api */
        $this->soapApiModel->initTransactionAPI($storeId);
        /**
         *   fetching Order $order
         */
        $order = $payment->getOrder();


        /**
         * is recurring is Active
         */
        $isRecurring = $this->isRecurringActive($order);
        $this->isRecurring = $isRecurring;
        //$isPreAuthTransactionEnabled = $this->ebizConfig->getPreAuthTransactionEnabled($storeId);

        /** @var $paymentInfoParams */
        $paymentInfoParams = $this->getPaymentMethodData($payment);

        $additionalInformations = $payment->getAdditionalInformation();

        $ebizPaymentOption = isset($paymentInfoParams['ebzc_option']) ? $paymentInfoParams['ebzc_option'] : '';
        $paymentMethodType = $paymentInfoParams['ebzc_option_type'] ??
            PaymentInterface::EBIZCHARGE_METHOD_TYPE_CREDIT_CARD;
        $this->paymentType = $paymentMethodType;
        $saveCards = $paymentInfoParams['ebzc_save_payment'] ?? false;

        /** @var  $isPciTransactionEnabled */
        $isPciTransactionEnabled = $this->ebizConfig->getPciComplianceEnabled();
        $envType = $this->checkoutSession->getEnvType();
        $this->soapApiModel->setTotalOrderedQty((float)($order->getTotalQtyOrdered() ?? 0));

        //  dump($payment->getMethodInstance()->order()->debug()); throw new LocalizedException(__("exception"));
        /**
         * if pci compliance enabled
         */
        if ($isPciTransactionEnabled && $envType === PaymentInterface::PAYMENT_ENV_TYPE_FRONTEND) {

            /** @var  $transactionData */
            $transactionData = $this->checkoutSession->getTransactionData();

            /**
             * Assigning the transaction response
             */
            $this->assignAndSaveTransactionResponse($payment, $envType, $transactionData);
            /**
             * unset Payment additional Information
             */
            $this->unsetPaymentAdditionalInfo($payment);

            if (is_array($transactionData) && count($transactionData) > 0) {
                if (
                    isset($transactionData['result_code']) && $transactionData['result_code'] ===
                    PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_AUTHENTICATED
                ) {

                    /** @var  $paymentMethodId */
                    $paymentMethodId = $transactionData['payment_method_id'] ?? 0;

                    /**
                     * Assigning the transaction response
                     */
                    $this->assignAndSaveTransactionResponse($payment, $envType, $transactionData);
                    /**
                     * unset Payment additional Information
                     */
                    $this->unsetPaymentAdditionalInfo($payment);

                    if ($isRecurring) {
                        /** @var $customerTransactionParams */
                        /** @var  $paymentMethodId */
                        $paymentMethodId = $transactionData['payment_method_id'] ?? 0;
                        $transactionData["isRecurring"] = $isRecurring;
                        $transactionData["Command"] = $command;

                        $recurringOrderParams = [
                            'payment' => $payment,
                            'method_id' => $paymentMethodId,
                            'customer_transaction_params' => $transactionData
                        ];

                        /** @var  create a $recurring */
                        $recurring = $this->soapApiModel->addRecurringItemAtGateway($recurringOrderParams);

                        if ($recurring["error"] === false) {
                            $this->ebizchargeLogger->addInfo(__("Success, the recurring order has been added "));
                            return $this;
                        }
                    }
                } elseif (
                    isset($transactionData['result_code']) && $transactionData['result_code'] ===
                    PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_ERROR
                ) {
                    $isError = true;
                    $this->assignAndSaveTransactionResponse($payment, $envType, $transactionData, $isError);
                    /**
                     * unset Payment additional Information
                     */
                    $this->unsetPaymentAdditionalInfo($payment);
                    $transactionRefNumber = $transactionData['ref_num'] ?? "";
                    $payment->setCcTransId($transactionRefNumber);

                    $this->void($payment);

                    $error = $transactionData['error'] ?? "Unknown error";
                    throw new LocalizedException(__("Payment authorization transaction has been declined: " .
                        $error));
                }
            } else {
                $error = $transactionData['error'] ?? "Unknown error";
                throw new LocalizedException(__("Payment authorization transaction has been declined: " .
                    $error));
            }
        }

        /**
         * set general payment data
         */
        $this->soapApiModel->setPaymentData($payment, $amount);

        /** @var  $incrementId */
        $incrementId = $order->getIncrementId();

        $paymentDescription = "Order #" . $incrementId;

        if ($this->ebizConfig->getPaymentDescription()) {
            $paymentDescription = str_replace(
                '[orderid]',
                $incrementId,
                $this->ebizConfig->getPaymentDescription()
            );
        }
        $this->soapApiModel->setData('description', $paymentDescription);

        /**
         * Downloading orders
         * from admin side
         */
        if ($ebizPaymentOption === PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_DOWNLOAD_ORDER) {
            return $this->downloadOrders($payment, $amount);
        }

        /** @var $customerId */
        $customerId = $order->getCustomerId();

        /** setting transaction data */
        $this->soapApiModel->setTransactionData($payment, false, $paymentInfoParams);

        /** if order is placed
         * and we have order
         * detail
         */
        if ($order) {
            $billingAddress = $order->getBillingAddress()->getData();
            $additionalInformations['postcode'] = $billingAddress['postcode'];
            $additionalInformations['street'] = $billingAddress['street'];

            /**--- set order general data ---*/
            $this->soapApiModel->setOrderData($payment);
            $this->soapApiModel->setOrderShipping($order);
            $this->soapApiModel->setOrderBilling($order);

            if ($this->soapApiModel->getOrderData()['customerId'] == null) {
                $this->soapApiModel->setGuestCustomer();
            }
        }

        /**
         * if authorized
         * Only or Capture or
         * Refund Amount
         */
        if ($command && $command === PaymentInterface::EBIZCHARGE_COMMAND_CAPTURE) {
            $command = PaymentInterface::EBIZCHARGE_COMMAND_CAPTURE;
        } elseif ($command && $command === PaymentInterface::EBIZCHARGE_COMMAND_REFUND) {
            $command = PaymentInterface::EBIZCHARGE_COMMAND_REFUND;
        } elseif ($command && PaymentInterface::EBIZCHARGE_METHOD_TYPE_VOID) {
            $command = PaymentInterface::EBIZCHARGE_METHOD_TYPE_VOID;
        } else {
            if (
                $this->ebizConfig->isAuthorizeOnly() === MethodInterface::ACTION_AUTHORIZE &&
                $this->authMode !== 'capture'
            ) {
                $command = PaymentInterface::EBIZCHARGE_COMMAND_AUTHONLY;
            } else {
                $command = PaymentInterface::EBIZCHARGE_COMMAND_SALE;
            }
        }

        /**
         * Setting Command for Transaction
         */
        $this->soapApiModel->setCommand($command);

        /**
         * @var check if isPaymentAuthenticated
         * $isPaymentAuthenticated
         */
        $isPaymentAuthenticated = false;

        if (strtolower($ebizPaymentOption) === strtolower(PaymentInterface::EBIZCHARGE_METHOD_GRAPHQL_TYPE_WEB_FORM)) {
            /**
             * Payment Info Params
             */
            $this->paymentInfoParams = $paymentInfoParams;
            /**
             * Assign Transaction Response
             */
            $this->assignAndSaveTransactionResponse($payment, $ebizPaymentOption);
            /**
             * unset Payment additional Information
             */
            $this->unsetPaymentAdditionalInfo($payment);

            return $this;
        }

        /**
         * if payment option is Web Hosted From
         */
        if (strtolower($ebizPaymentOption) === strtolower(PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_HOSTED_FORM)) {
            if ($payment) {
                /**
                 * Payment Info Params
                 */
                $this->paymentInfoParams = $paymentInfoParams;
                /**
                 * Assign Transaction Response
                 */
                $this->assignAndSaveTransactionResponse($payment, $ebizPaymentOption);

                if ($this->ebizConfig->isAuthorizeOnly() !== MethodInterface::ACTION_AUTHORIZE) {
                    $totalDue = $payment->getOrder()->getGrandTotal();

                    $this->quickSale($payment, $totalDue);
                }

                if ($this->isRecurring === true) {
                    /**
                     * check if recurring exists
                     */
                    $isRecurringExists = $this->soapApiModel->isRecurringExists($payment);

                    if (!$isRecurringExists) {

                        /** push recurring in case of the
                         * Transaction, recurring
                         * enabled and Is recurring exits
                         */
                        $transactionData = $this->soapApiModel->getTransactionData();
                        $customerTransactionParams = $this->soapApiModel->prepareCustomerTransactionParams(
                            $payment,
                            $this->isRecurring
                        );
                        $paymentMethodId = $payment->getAdditionalInformation("ebzc_method_id");


                        /** @var  $orderRecurringParams */
                        $orderRecurringParams = [
                            'payment' => $payment,
                            'transaction' => $transactionData,
                            'method_id' => $paymentMethodId,
                            'customer_transaction_params' => $customerTransactionParams
                        ];

                        /** @var  create a $recurring order */
                        $recurring = $this->soapApiModel->creatRecurringOrder($orderRecurringParams);
                        $this->ebizchargeLogger->addInfo(__("Success, the recurring order has been added "));
                    }
                }
            }

            /**
             * unset Payment additional Information
             */
            $this->unsetPaymentAdditionalInfo($payment);

            return $this;
        }


        /* get magento customer session */
        if (!$customerId) {
            $isRecurring = false;
            /**
             *
             * If payment Option Type Graph QL saved
             */
            if (strtolower($ebizPaymentOption) === strtolower(PaymentInterface::EBIZCHARGE_METHOD_GRAPHQL_TYPE_SAVED)) {
                /**
                 * Existing payment method
                 * selected by customer
                 */
                $paymentMethodId = $payment->getAdditionalInformation("method_id");
                $customerToken = $payment->getAdditionalInformation("ebiz_cust_token");
                $isSavedPayment = true;
                $isPaymentAuthenticated = true;
                $this->paymentActionType = strtolower(PaymentInterface::EBIZCHARGE_METHOD_GRAPHQL_TYPE_SAVED);
                $this->isSavePaymentMethod = false;

                if ($isPaymentAuthenticated) {
                    /**
                     * Processing a register customer
                     * checkout
                     */
                    $this->soapApiModel->runCustomerTransaction(
                        $customerToken,
                        $paymentMethodId,
                        $payment,
                        $isRecurring,
                        $isSavedPayment
                    );
                }
                return $this;
            }

            /**
             * if option is paylater in case of guest checkout
             */
            if ($ebizPaymentOption === "paylater") {
                $payment->setIsTransactionClosed(0)->setTransactionAdditionalInfo('trans_id', 'paylater');
                $payment->setStatus(AbstractMethod::STATUS_APPROVED);
                return $this;
            }

            /** Processing
             * a guest checkout
             */
            $this->soapApiModel->runTransaction($payment, $isRecurring);
        } else {

            /** @var $customer */
            $customer = $this->customerFactory->create()->load($customerId);
            /** @var  customerId */
            $this->customerId = (string)$customerId;

            /** Ebiz Customer Id */
            $additionalInformations['ebzc_cust_id'] = $customer->getEcCustId();

            /** @var  $isSavePayment */
            $isSavePayment = $this->getIsSavePaymentMethod($payment);

            /**
             * For logged
             * in Customer go for transaction
             */
            // phpcs:disable
            if (strtolower($ebizPaymentOption) === strtolower(PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_NEW)) {

                /**
                 * $customer Token
                 */
                $customerToken = $customer->getEcCustToken() ?? "";
                $paymentMethodId = 0;
                $isPaymentAuthenticated = false;

                if ($isSavePayment) {
                    $this->paymentMethodParams = $this->preparePaymentMethodParams(
                        $customer,
                        $payment,
                        $additionalInformations
                    );

                    /** @var isSavePaymentMethod */
                    $this->isSavePaymentMethod = $isSavePayment;

                    if ($customer) {
                        $customerToken = $customer->getEcCustToken() ?? "";
                        $customerInternalId = $customer->getEcCustInternalId() ?? "";
                        $customerSID = $customer->getEcCustId() ?? "";
                        $customerDivisionId = $customer->getEcDivisionId() ?? "";
                        $customerSoftwareId = $customer->getEcSoftwareId() ?? "";

                        if (!$customerToken || !$customerSID || !$customerInternalId || !$customerDivisionId ||
                            !$customerSoftwareId) {
                            $customer->addCustomerToEbizcharge($customer);
                            $customer = $this->customerFactory->create()->load($customerId);
                            $customerToken = $customer->getEcCustToken();
                        }
                    }
                }


            } /**
             *
             * If payment Option Type Graph QL saved
             */
            elseif (strtolower($ebizPaymentOption) === strtolower(PaymentInterface::EBIZCHARGE_METHOD_GRAPHQL_TYPE_SAVED)) {

                /**
                 * Existing payment method
                 * selected by customer
                 */
                $paymentMethodId = $payment->getAdditionalInformation("method_id");
                $customerToken = $payment->getAdditionalInformation("ebiz_cust_token");

                $isSavedPayment = true;
                $isPaymentAuthenticated = true;
                $this->paymentActionType = strtolower(PaymentInterface::EBIZCHARGE_METHOD_GRAPHQL_TYPE_SAVED);
                $this->isSavePaymentMethod = false;
                /**
                 * if payment is Authenticated
                 * then run transaction
                 */

            } /**
             *
             * If payment Option Type is Saved Cards
             */
            elseif (strtolower($ebizPaymentOption) === strtolower(PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_SAVED)) {

                /**
                 * Existing payment method
                 * selected by customer
                 */
                $paymentMethodId = $additionalInformations['ebzc_method_id'] ?? '';
                $customerToken = $customer->getEcCustToken();
                $isSavedPayment = true;
                $isPaymentAuthenticated = true;
                $this->paymentActionType = strtolower(PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_SAVED);
                $this->isSavePaymentMethod = false;


            } /**
             *
             * If Payment Option Type is Update
             */
            elseif (strtolower($ebizPaymentOption) === strtolower(PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_UPDATE)) {
                $ebizCustomerId = $additionalInformations['ebzc_cust_id'];
                $customer = $this->customerFactory->create()->loadByEbizCustomerId($ebizCustomerId);
                $customerToken = $customer->getEcCustToken();
                $paymentMethodId = $additionalInformations['ebzc_method_id'];
                $isSavePayment = true;
                $this->paymentActionType = strtolower(PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_SAVED);
                $this->isSavePaymentMethod = false;
                $isPaymentAuthenticated = true;
                $isSavedPayment = true;
            } /**
             *
             * If Payment Type is Pay Later
             *
             */
            elseif (strtolower($ebizPaymentOption) ===
                strtolower(PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_PAY_LATER)) {
                /* payment will be paid later on gataway */
                if (isset($additionalInformations['ebzc_paylater_payment']) &&
                    (int)$additionalInformations['ebzc_paylater_payment'] === 1) {
                    $payment->setIsTransactionClosed(0)->setTransactionAdditionalInfo('trans_id', 'paylater');
                    $payment->setStatus(AbstractMethod::STATUS_APPROVED);
                    return $this;
                } else {
                    $this->ebizchargeLogger->addError(__(
                        "Exception occurred Please select Pay Later checkbox to avail this functionality!"
                    ));
                    throw new LocalizedException(__(
                        'Please select Pay Later checkbox to avail this functionality!'
                    ));
                }
            } elseif (strtolower($ebizPaymentOption) ===
                strtolower(PaymentInterface::EBIZCHARGE_METHOD_GRAPHQL_TYPE_WEB_FORM)) {

                /**
                 * Payment Info Params
                 */
                $this->paymentInfoParams = $paymentInfoParams;

                /**
                 * Assign Transaction Response
                 */
                $this->assignAndSaveTransactionResponse($payment, $ebizPaymentOption);

                /**
                 * unset Payment additional Information
                 */
                $this->unsetPaymentAdditionalInfo($payment);

                return $this;
            } /**
             *
             * If Payment Option type is Recurring
             *
             */
            elseif (strtolower($ebizPaymentOption) ===
                strtolower(PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_RECURRING)) {

                /** @var $paymentInfoParams */
                $paymentInfoParams = $this->getPaymentMethodData($payment);

                $additionalInformations = $payment->getAdditionalInformation();
                $ebizPaymentOption = isset($paymentInfoParams['ebzc_option']) ? $paymentInfoParams['ebzc_option'] : '';
                $paymentMethodType = $paymentInfoParams['ebzc_option_type'] ?? '';
                $saveCards = $paymentInfoParams['ebzc_save_payment'] ?? false;

                $order = $payment->getOrder();
                $billingAddress = $order->getBillingAddress()->getData();
                $additionalInformations['postcode'] = $billingAddress['postcode'] ?? "";
                $additionalInformations['street'] = $billingAddress['street'] ?? "";

                $ebizCustomerId = $additionalInformations['ebzc_cust_id'] ?? "";
                $customer = $this->customerFactory->create()->loadByEbizCustomerId($ebizCustomerId);
                $customerToken = $customer->getEcCustToken();

                /** @var  $deductableAmount */
                $deductableAmount = ($amount - $payment->getAdditionalInformation('excludeAmount'));

                if ($deductableAmount <= 0) {
                    $payment->setIsTransactionClosed(0)->setTransactionAdditionalInfo('trans_id', 'recurring');
                    $payment->setStatus(AbstractMethod::STATUS_APPROVED);

                    return $this;
                } elseif ($deductableAmount > 0) {
                    $payment->getMethodInstance()
                        ->getInfoInstance()
                        ->setExcludeAmount($payment->getAdditionalInformation('excludeAmount'));

                    /** @var  $isSavePayment */
                    $isSavePayment = $this->getIsSavePaymentMethod($saveCards);

                    /*new method added by customer */
                    if ($isSavePayment) {
                        $paymentMethodParams = $this->preparePaymentMethodParams(
                            $customer,
                            $payment,
                            $additionalInformations
                        );

                        if ($paymentMethodType === PaymentInterface::EBIZCHARGE_METHOD_TYPE_CREDIT_CARD) {
                            $paymentMethodId = $this->customerFactory->create()
                                ->addNewPaymentMethod($customerId, $paymentMethodParams);
                        } elseif ($paymentMethodType === PaymentInterface::EBIZCHARGE_METHOD_TYPE_ACH) {
                            $paymentMethodId = $this->customerFactory->create()
                                ->addCustomerBankAccount($customer, $paymentMethodParams);
                        }
                    }
                }

                /**
                 * if Transaction is recurring type
                 *  then Recurring is true
                 */
                $isRecurring = true;
                $isPaymentAuthenticated = true;
                $isSavedPayment = true;
                $this->paymentActionType = strtolower(PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_RECURRING);
                $this->isSavePaymentMethod = false;

            } else {
                /* Processing a guest checkout */
                $this->soapApiModel->runTransaction($payment, $isRecurring);
            }
            // phpcs:enable

            /**
             * if payment is Authenticated
             * then run transaction
             */
            if ($isPaymentAuthenticated) {

                /**
                 * Processing a register customer
                 * checkout
                 */
                $this->soapApiModel->runCustomerTransaction(
                    $customerToken,
                    $paymentMethodId,
                    $payment,
                    $isRecurring,
                    $isSavedPayment
                );
            } else {
                /** running the transaction
                 * in case of new Payment ID
                 */
                if ($isRecurring === true && !$this->isSavePaymentMethod) {
                    throw new LocalizedException(__(
                        "Ops error: You must save your payment method for adding recurring, Please try again."
                    ));
                }

                $this->soapApiModel->runTransaction($payment, $isRecurring);
            }
        }

        /**
         * Assign and Saved Payments
         */
        $this->assignAndSaveTransactionResponse($payment);

        $this->updateQuoteData($payment, $amount, $paymentMethodType);

        /**
         * unset Payment additional Information
         */
        $this->unsetPaymentAdditionalInfo($payment);

        return $this;
    }

    /**
     * Voids transaction.
     *
     * @param InfoInterface $payment
     * @return Payment
     * @throws LocalizedException
     */
    public function void(InfoInterface $payment)
    {
        $this->soapApiModel->initTransactionAPI($payment->getOrder()->getStoreId());
        $order = $payment->getOrder() ?? null;

        if ($payment->getCcTransId() && $order) {
            $command = PaymentInterface::EBIZCHARGE_METHOD_TRANSACTION_TYPE_VOID;
            $orderedAmount = $order->getTotalDue();

            $tran = $this->soapApiModel;
            $tran->setData('amount', $orderedAmount);
            $tran->setData('command', $command);
            $tran->setTransactionData($payment);

            // To set that transaction no whose type is 'authorization' because
            // void only possible for Auth only request
            $this->setTransactionReferenceNo($payment);

            /**
             * Run void Transactions
             */
            $isTransactionSuccess = $this->soapApiModel->runVoidTransaction(
                $payment,
                false,
                true,
                $command
            );

            $resultDataResponse = $tran->getData('resultcode') ??
                PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_ERROR;

            $transType = PaymentInterface::EBIZCHARGE_METHOD_TRANSACTION_TYPE_VOID;

            $transApi = $this->soapApiModel;

            $transactionId = $transApi->getData('refnum');

            /** @var  $transData */
            $transData = [
                "resultcode" => $transApi->getData('resultcode') ??
                    PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_ERROR,
                "refnum" => $transactionId ?? "",
                "transtype" => $resultDataResponse = $transApi->getData('transtype') ??
                    PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_ERROR
            ];
            /**
             * pre and render response
             */
            $this->prepareAndRenderResponse($payment, $transData, $transType);
        }
        return $this;
    }

    /**
     * Set Transaction Reference No for Cancel/Void
     *
     * @param InfoInterface $payment
     * @return void
     * @throws Exception
     */
    private function setTransactionReferenceNo(InfoInterface $payment)
    {
        $order = $payment->getOrder();
        $authTransaction = $this->getOrderPaymentTransactions(
            $order->getId(),
            PaymentInterface::EBIZCHARGE_TRANSACTION_TYPE_AUTHORIZATION
        );
        $authTransactionId = $authTransaction ? $authTransaction->getFirstItem()->getTxnId() : '';
        if ($authTransactionId) {
            $this->soapApiModel->setData('refnum', $authTransactionId);
        }
    }

    /**
     * Get Order Payment Transactions
     *
     * @param mixed $orderId
     * @param mixed $txnType
     * @return array|TransactionSearchResultInterface
     */
    public function getOrderPaymentTransactions($orderId = null, $txnType = '')
    {
        if (!$orderId) {
            return [];
        }

        $transactionCollection = $this->transactionSearchResultFactory->create();
        $transactionCollection->addOrderIdFilter($orderId);

        if ($txnType) {
            $transactionCollection->addTxnTypeFilter($txnType);
        }

        return $transactionCollection;
    }

    /**
     * This method Download orders from admin side
     *
     * @param InfoInterface $payment
     * @param mixed $amount
     * @return Payment|bool
     * @throws LocalizedException
     */
    public function downloadOrders(InfoInterface $payment, $amount = 0)
    {

        /** @var  $paymentInfoSystem */
        $paymentInfoSystem = $payment->getMethodInstance()->getInfoInstance();
        /** @var  $additionalInfo */
        $additionalInfo = $payment->getAdditionalInformation();
        $isRecurringOrder = isset($additionalInfo['is_recurring']) ? $additionalInfo['is_recurring'] : "";
        $ebizCustomerId = isset($additionalInfo['ebizCustomerId']) ? $additionalInfo['ebizCustomerId'] : 'guest';
        $paymentOption = $additionalInfo['ebzc_option'] ?? PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_DOWNLOAD_ORDER;
        $parentOrderNumber = $additionalInfo['ebzc_parent_order_id'] ?? "";
        $transactionRefNumber = isset($additionalInfo['cc_trans_id']) ? $additionalInfo['cc_trans_id'] : '';
        $applicationRefNumber = $additionalInfo['payment_application_id'] ?? '';

        if (count($additionalInfo) > 0) {
            $payment->getCcTransId($additionalInfo);
        }

        if (!$transactionRefNumber) {
            return true;
        }
        /** @var  $orderPaymentData */
        $orderPaymentData = [];
        $transResp = $this->customerFactory->create()->getTransactionDetailByReferenceId($transactionRefNumber);
        $transactionData = isset($transResp["response"]) ? $transResp["response"] : [];
        $transType = isset($transResp["TransactionType"]) ? $transResp["TransactionType"] : "";
        $transStatus = isset($transResp["Status"]) ? $transResp["Status"] : "";
        $accountHolder = isset($transResp["AccountHolder"]) ? $transResp["AccountHolder"] : "";

        $transDetail = isset($transactionData["Details"]) ? (array)$transactionData["Details"] : [];

        if (count($transDetail) > 0) {
            $orderPaymentData = array_merge($orderPaymentData, $transDetail);
        }
        $transResponse = isset($transactionData["Response"]) ? (array)$transactionData["Response"] : [];

        if (count($transResponse) > 0) {
            $orderPaymentData = array_merge($orderPaymentData, $transResponse);
        }
        $transCreditCard = isset($transactionData["CreditCardData"]) ? (array)$transactionData["CreditCardData"] : [];
        if (count($transCreditCard) > 0) {
            $orderPaymentData = array_merge($orderPaymentData, $transCreditCard);
        }
        $transCheckData = isset($transactionData["CheckData"]) ? (array)$transactionData["CheckData"] : [];
        if (count($transCreditCard) > 0) {
            $orderPaymentData = array_merge($orderPaymentData, $transCheckData);
        }

        /* store response variables */
        $transactionType =
            /** @var  $setLastTransId */
        $setLastTransId = $transactionRefNumber . '-' . $transType;
        $resultCode = $orderPaymentData[PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_RESULT_CODE] ?? "D";
        $authCode = $orderPaymentData[PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_AUTH_CODE] ?? "";
        $authAmount = $orderPaymentData[PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_AUTH_AMOUNT] ?? 0;

        $ebizPaymentMethodId = $orderPaymentData[PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_PAYMENT_METHOD_ID] ?? "";
        $ebizSavePayment = isset($orderPaymentData[PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_PAYMENT_METHOD_ID]) ?
            "saved" : "";
        $cardNumber = $orderPaymentData[PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_PAYMENT_CARD_NUMBER] ?? "";
        $cardType = $orderPaymentData[PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_PAYMENT_CARD_TYPE] ?? "";

        $cardCodeResultCode = $orderPaymentData[PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_PAYMENT_CARD_CODE_RESULT_CODE] ?? "";
        $cardCodeResult = $orderPaymentData[PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_PAYMENT_CARD_CODE_RESULT] ?? "";

        $avsResult = $orderPaymentData[PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_AVS_RESULT] ?? "";
        $avsResultCode = $orderPaymentData[PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_AVS_RESULT_CODE] ?? "";

        $ebizAvsStreet = $orderPaymentData[PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_AVS_STREET] ?? "";
        $batchRefNumber = $orderPaymentData[PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_BATCH_REFERENCE_NUMBER] ?? "";
        $batchNumber = $orderPaymentData[PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_BATCH_NUMBER] ?? "";

        $cvvResultCode = $orderPaymentData[PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CARD_CODE] ?? "";
        $ebizAvsZip = $orderPaymentData[PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_AVS_ZIP] ?? "";
        $ccExpMonth = $orderPaymentData[PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CARD_EXPIRATION] ?? "";
        $ccExpYear = $orderPaymentData[PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CARD_EXPIRATION] ?? "";

        $shippingCapturedAmount = $orderPaymentData[PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_SHIPPING] ?? 0;
        $poNumber = $orderPaymentData[PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_PO_NUMBER] ?? "";
        $statusCode = $orderPaymentData[PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_STATUS_CODE] ?? "";
        $paymentStatus = $orderPaymentData[PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_STATUS] ?? "";

        $paymentError = $orderPaymentData[PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_ERROR] ?? "";
        $paymentErrorCode = $orderPaymentData[PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_ERROR_CODE] ?? "";

        $isPaymentDuplicate = $orderPaymentData[PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_IS_DUPLICATE] ?? false;
        $ebizCustomerNumber = $orderPaymentData[PaymentInterface::EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CUSTOM_NUM] ?? false;

        /** setting payment info */
        $payment->setCcApproval($authCode)
            ->setCcTransId($transactionRefNumber)
            ->setCcAvsStatus($avsResultCode)
            ->setCcCidStatus($cvvResultCode)
            // ->setAmountPaid($authAmount)
            ->setEbzcPaymentStatusCode($statusCode)
            ->setEbzcPaymentStatus($paymentStatus)
            ->setEbzcAuthcode($authCode)
            ->setEbzcCustId($ebizCustomerId)
            ->setEbzcBatchRefNumber($batchRefNumber)
            ->setCcExpYear($ccExpYear)
            ->setCcStatus($resultCode)
            ->setLastTransId($transactionRefNumber)
            ->setEbzcBatchNumber($batchNumber)
            ->setEbzcAvsResult($avsResult)
            ->setEbzcAuthAmount($authAmount)
            ->setEbzcAvsResultCode($avsResultCode)
            ->setEbzcPaymentError($paymentError)
            ->setEbzcPaymentErrorCode($paymentErrorCode)
            ->setEbzcPaymentIsDuplicate($isPaymentDuplicate)
            ->setEbzcCardCodeResultCode($cardCodeResultCode)
            ->setEbzcCardCodeResult($cardCodeResult)
            ->setEbzcCustomerNumber($ebizCustomerNumber)
            ->setEbzcPaymentReferenceNumber($transactionRefNumber)
            ->setEbzcAvsResultCodeEbzcAuthAmount($authAmount)
            ->setEbzcResult($paymentStatus)
            ->setEbzcResultCode($statusCode)
            ->setEbzcPaymentCommand($transactionType)
            ->setCcOwner($accountHolder)
            ->setEbzcParentOrderId($parentOrderNumber)
            ->setEbzcApplicationPaymentRefId($applicationRefNumber);

        /* add the special ebzc fields to the database */
        $paymentInfoSystem->setEbzcCustId($ebizCustomerId);
        $paymentInfoSystem->setEbzcMethodId($ebizPaymentMethodId);
        $paymentInfoSystem->setEbzcSavePayment($ebizSavePayment);
        $paymentInfoSystem->setEbzcOption($paymentOption);
        $paymentInfoSystem->setEbzcAvsStreet($ebizAvsStreet);
        $paymentInfoSystem->setEbzcAvsZip($ebizAvsZip);
        /* New params */
        $paymentInfoSystem->setShippingCaptured($shippingCapturedAmount);
        $paymentInfoSystem->setBaseShippingCaptured($shippingCapturedAmount);
        $paymentInfoSystem->setCcExpMonth($ccExpMonth);
        $paymentInfoSystem->setCcApproval($authCode);
        $paymentInfoSystem->setCcLast4(substr((string)$cardNumber, -4));
        $paymentInfoSystem->setCcOwner($accountHolder);
        $paymentInfoSystem->setCcType($cardType);
        $paymentInfoSystem->setPoNumber($poNumber);
        $paymentInfoSystem->setCcExpYear($ccExpYear);
        $paymentInfoSystem->setCcAvsStatus($avsResultCode);
        $paymentInfoSystem->setLastTransId($setLastTransId);
        $paymentInfoSystem->setCcTransId($transactionRefNumber);
        $paymentInfoSystem->setEbzcParentOrderId($parentOrderNumber);
        $paymentInfoSystem->setEbzcApplicationPaymentRefId($applicationRefNumber);

        $payment->setLastTransId($transactionRefNumber);
        $payment->setTransactionId($transactionRefNumber);
        $payment->setIsTransactionClosed(0)->setTransactionAdditionalInfo(
            'trans_id',
            $transactionRefNumber,
            'is_recurring',
            $isRecurringOrder
        );
        /** @var  $transData */
        $transData = [
            "resultcode" => PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_AUTHENTICATED,
            "refnum" => $transactionRefNumber,
            "transtype" => PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_DOWNLOAD_ORDER
        ];

        // phpcs:ignore
        // $this->prepareAndRenderResponse($payment, $transData, $transType);

        return $this;
    }

    /**
     * Prepare Payment Method Params
     *
     * @param null|mixed $customer
     * @param null|mixed $paymentInfo
     * @param null|mixed $additionalInformations
     * @return array
     */
    public function preparePaymentMethodParams(
        $customer = null,
        $paymentInfo = null,
        $additionalInformations = null
    ) {

        /** @var  $paymentParams */
        $paymentParams = $this->getPaymentMethodData($paymentInfo);
        $achRoute = isset($paymentParams['ach_route']) ? $paymentParams['ach_route'] : "";
        $ccType = isset($paymentParams['cc_type']) ? $paymentParams['cc_type'] : '';

        $paymentParams =
            [
                'payment' => [
                    'cc_holder' => isset($paymentParams['cc_owner']) ? $paymentParams['cc_owner'] : '',
                    'cc_number' => isset($paymentParams['cc_number']) ? $paymentParams['cc_number'] : '',
                    'cc_cid' => isset($paymentParams['cc_cid']) ? $paymentParams['cc_cid'] : '',
                    'cc_route' => isset($paymentParams['cc_route']) ? $paymentParams['cc_route'] : '',
                    'cc_exp_month' => isset($paymentParams['cc_exp_month']) ? $paymentParams['cc_exp_month'] : '',
                    'cc_exp_year' => isset($paymentParams['cc_exp_year']) ? $paymentParams['cc_exp_year'] : '',
                    'cc_type' => $this->ebizConfig->getShortCcType($ccType),
                    'postcode' => isset($paymentParams['ebzc_avs_zip']) ? $paymentParams['ebzc_avs_zip'] : '',
                    'street' => [isset($paymentParams['ebzc_avs_street']) ? $paymentParams['ebzc_avs_street'] : ''],
                    'ach_type' => isset($paymentParams['ach_type']) ? $paymentParams['ach_type'] : '',
                    'ach_number' => isset($paymentParams['cc_number']) ? $paymentParams['cc_number'] : '',
                    'ach_holder' => isset($paymentParams['cc_owner']) ? $paymentParams['cc_owner'] : '',
                    'ach_route' => isset($paymentParams['ach_routing']) ? $paymentParams['ach_routing'] : $achRoute,
                    'default' => isset($paymentParams['default']) ? $paymentParams['default'] : 0,
                    'is_default' => isset($paymentParams['is_default']) ? $paymentParams['is_default'] : 0,
                    'is_admin_checkout' => isset($paymentParams['is_admin_checkout']) ?? false,
                    'customer_id' => $customer->getId(),
                    'email' => $customer->getEmail(),
                ],
                'customer_id' => $customer->getId(),
                'email' => $customer->getEmail(),
                'ebiz_customer_internal_id' => $customer->getEcCustInternalId(),
                'ebiz_customer_token' => $customer->getEcCustToken(),
                'postcode' => isset($paymentParams['ebzc_avs_zip']) ? $paymentParams['ebzc_avs_zip'] : '',
                'street' => [isset($paymentParams['ebzc_avs_street']) ? $paymentParams['ebzc_avs_street'] : ''],
                'ach_type' => isset($paymentParams['ach_type']) ? $paymentParams['ach_type'] : '',
                'ach_number' => isset($paymentParams['cc_number']) ? $paymentParams['cc_number'] : '',
                'ach_holder' => isset($paymentParams['cc_owner']) ? $paymentParams['cc_owner'] : '',
                'ach_route' => isset($paymentParams['ach_routing']) ? $paymentParams['ach_routing'] : $achRoute,
                'default' => isset($paymentParams['default']) ? $paymentParams['default'] : 0,
                'is_default' => isset($paymentParams['is_default']) ? $paymentParams['is_default'] : 0,
                'is_checkout' => true,
                'save_card_anyway' => $paymentParams['save_card_anyway'] ?? 0
            ];

        return $paymentParams;
    }

    /**
     * Update quote data if surcharge payment is done
     *
     * @param InfoInterface $payment
     * @param int|string|null $amount
     * @param string|null $paymentMethodType
     * @return void
     */
    private function updateQuoteData($payment, $amount = 0, $paymentMethodType = null)
    {
        try {
            $authorizedData = $this->soapApiModel->getAuthorizeData();

            if (
                $authorizedData['resultcode'] === PaymentInterface::PAYMENT_TRANSACTION_RESPONSE_TYPE_AUTHORIZE &&
                $authorizedData['command'] === PaymentInterface::EBIZCHARGE_COMMAND_AUTHONLY &&
                $paymentMethodType === PaymentInterface::EBIZCHARGE_METHOD_TYPE_CREDIT_CARD
            ) {
                if ($this->backendAuthSession->isLoggedIn()) {
                    $quote = $this->backendQuote->getQuote();
                } else {
                    $quote = $this->checkoutSession->getQuote();
                }

                $requestParams = $this->prepareCalculateSurchargeParams($payment, $amount);
                $surchargeResponse = $this->soapApiModel->calculateSurchargeAmount($requestParams);
                $surchargeAmount = $surchargeResponse[SurchargeInterface::EBIZ_SURCHARGE_AMOUNT] ?? null;
                $ineligible = isset($surchargeResponse[SurchargeInterface::EBIZ_SURCHARGE_INELIGIBLE]) &&
                $surchargeResponse[SurchargeInterface::EBIZ_SURCHARGE_INELIGIBLE] ? 1 : 0;
                $surchargeAmount = $ineligible ? 0 : $surchargeAmount;
                $quote->setEcSurchargeAmount($surchargeAmount);
                $quote->setEcSurchargePercentage(
                    $surchargeResponse[SurchargeInterface::EBIZ_SURCHARGE_PERCENTAGE] ?? null
                );
                $quote->setEcSurchargeIneligible($ineligible);

                if ($surchargeAmount) {
                    $quote->setGrandTotal($quote->getGrandTotal() + $surchargeAmount);
                    $quote->setBaseGrandTotal($quote->getBaseGrandTotal() + $surchargeAmount);
                }

                $this->quoteRepository->save($quote);
            }
        } catch (Exception $exception) {
            $this->ebizchargeLogger->error($exception->getMessage());
        }
    }

    /**
     * Prepare Calculate Surcharge Amount API call params
     *
     * @param InfoInterface $payment
     * @param int|string|null $amount
     * @return array
     */
    private function prepareCalculateSurchargeParams(InfoInterface $payment, $amount = 0): array
    {
        $params = [
            'amount' => 0,
            'cardNumber' => null,
            'cardZipCode' => null,
            'paymentMethodId' => null,
            'customerInternalId' => null
        ];

        try {
            $paymentOption = $payment->getAdditionalInformation('ebzc_option');
            $ccNumber = ($paymentOption === PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_NEW) ?
                $payment->getData('cc_number') : null;
            $avsZip = $payment->getAdditionalInformation('ebzc_avs_zip') ?:
                $payment->getDataByKey('ebzc_avs_zip');
            $ebizCustomerId = $payment->getAdditionalInformation('ebzc_cust_id');
            $ebizCustomerInternalId = "";

            if ($ebizCustomerId) {
                $customerFactory = $this->customerFactory->create();
                $customer = $customerFactory->loadByEbizCustomerId($ebizCustomerId);
                if ($customer && $customer->getId()) {
                    $ebizCustomerInternalId = $customer->getEcCustInternalId();
                }
            }

            $params = [
                'amount' => $amount,
                'cardNumber' => $ccNumber,
                'cardZipCode' => $avsZip,
                'paymentMethodId' => $payment->getAdditionalInformation('ebzc_method_id'),
                'customerInternalId' => $ebizCustomerInternalId
            ];
        } catch (Exception $exception) {
            $this->ebizchargeLogger->error($exception->getMessage());
        }

        return $params;
    }

    /**
     * Quick Sale
     *
     * @param InfoInterface $payment
     * @param int $amount
     * @param string $command
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function quickSale(
        InfoInterface $payment,
        $amount = 0,
        $command = PaymentInterface::EBIZCHARGE_COMMAND_CAPTURE
    ) {
        /* initialize transaction object */
        $tran = $this->soapApiModel;
        /** @var  $order */
        $order = $payment->getOrder();
        /** @var  $storeId */
        $storeId = $order->getStoreId();

        $this->soapApiModel->initTransactionAPI($storeId);

        if (!$payment->getLastTransId()) {
            /** logging localized exception */
            $this->ebizchargeLogger->addError(__(
                'Exception error occurred: Unable to find previous transaction to reference'
            ));
            throw new LocalizedException(__('Unable to find previous transaction to reference'));
        }

        $tran->setData('command', $command);
        $tran->setData('amount', $amount);

        $incrementId = $payment->getOrder()->getIncrementId();
        $paymentDescription = "Order #: " . $incrementId;

        if ($this->ebizConfig->getPaymentDescription()) {
            $paymentDescription = str_replace(
                '[orderid]',
                $incrementId,
                $this->ebizConfig->getPaymentDescription()
            );
        }
        $tran->setData('description', $paymentDescription);
        //$this->isPartialCaptureAllowed($payment, $amount);
        $tran->setTransactionData($payment);

        $this->soapApiModel->runTransaction($payment, false);
        //$responseResultCodeResponse = $tran->getData('resultcode') ?? "";

        $transactionId = $tran->getData('refnum');

        /** @var  $transData */
        $transData = [
            "resultcode" => $tran->getData('resultcode') ??
                PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_ERROR,
            "refnum" => $transactionId ?? "",
            "transtype" => $tran->getData('transtype') ??
                PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_ERROR
        ];
        /** Prepare and render response code */
        $this->prepareAndRenderResponse($payment, $transData, $command);

        return $this;
    }

    /**
     * Create Partial Authority
     *
     * @param InfoInterface $payment
     * @param int $amount
     * @throws LocalizedException
     */
    public function createPartialAuthOnly(InfoInterface $payment, $amount = 0)
    {
        $this->isPartialCaptureAllowed($payment, $amount);

        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        $orderDueTotal = $order->getTotalDue();
        $remainingAmount = $orderDueTotal - $amount;

        if (($orderDueTotal !== $amount) && ($remainingAmount > 0)) {
            $orderId = $order->getIncrementId();
            //$this->soapApiModel->setTransactionData($payment);
            $this->soapApiModel->setCommand(PaymentInterface::EBIZCHARGE_COMMAND_AUTHONLY);
            $this->soapApiModel->setData('amount', $remainingAmount);
            $this->soapApiModel->setData(
                'description',
                'New Authonly transaction after partial capture of order Id #' . $orderId
            );
            $this->soapApiModel->clearLineItems();

            /** add line items */
            $this->soapApiModel->addLineItem($order->getAllVisibleItems());

            $customerId = $payment->getEbzcCustId() ?: ($payment->getAdditionalInformation('ebzc_cust_id') ?: 0);
            $customer = $this->customerFactory->create();
            $customer = $customer->loadByEbizCustomerId($customerId);

            $this->soapApiModel->captureOnlineProcess(
                $customer->getEcCustToken(),
                $payment->getEbzcMethodId(),
                $amount,
                true
            );

            if (
                $this->soapApiModel->getData('resultcode') ===
                PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_AUTHENTICATED
            ) {
                /** logging info  */
                $this->ebizchargeLogger->addInfo(__('Partial transaction created for orderId:#' . $orderId .
                    ' with reference No#' . $this->soapApiModel->getData('refnum')));
            } else {
                /** logging error to the logger */
                $this->ebizchargeLogger->addError(__('Error: Partial transaction not created for orderId:#' .
                    $orderId . ' Error: ' . $this->soapApiModel->getData('error')));
            }
        }
    }

    /**
     * Is Partial Capture Allowed
     *
     * @param InfoInterface|null $payment
     * @param int $amount
     * @return bool
     * @throws LocalizedException
     */
    public function isPartialCaptureAllowed(?InfoInterface $payment = null, $amount = 0)
    {
        /** @var $order */
        $order = $payment->getOrder();

        $orderGrandTotal = $order->getGrandTotal();
        $orderDueTotal = $order->getTotalDue();
        $remainingAmount = (float)$orderGrandTotal - (float)$orderDueTotal;
        $additionalInformation = $payment->getAdditionalInformation();
        $ebizCustomerId = $this->getPaymentAdditionalData('ebzc_cust_id', $payment);
        $ebizMethodId = $this->getPaymentAdditionalData('ebzc_method_id', $payment);

        if (
            ($orderDueTotal != $orderGrandTotal) && ($remainingAmount > 0) &&
            (empty($payment->getEbzcCustId()) || empty($payment->getEbzcMethodId()))
        ) {
            $exceptionMessage = __('Unable to create partial invoice for this order.
            We have not found the save payment method for this customer.');

            /** Add Error to logger */
            $this->ebizchargeLogger->addError($exceptionMessage);
            throw new LocalizedException($exceptionMessage);
        }

        return true;
    }

    /**
     * Get Payment Additional Data
     *
     * @param null|mixed $paymentKey
     * @param null|InfoInterface $payment
     * @return bool
     */
    public function getPaymentAdditionalData($paymentKey = null, $payment = null)
    {
        if (!$paymentKey || !$payment) {
            return false;
        }
        if ($payment && $paymentKey) {
            return $payment->getAdditionalInformation($paymentKey);
        }
        if ($payment) {
            return $payment->getAdditionalInformation();
        }

        return false;
    }

    /**
     * Get Ebiz Customer Id
     *
     * Returns Ebizcharge customer ID.
     * #1 for delete customer card
     *
     * @return mixed
     */
    public function getEbzcCustId()
    {
        if ($this->backendAuthSession->isLoggedIn()) {
            $customerId = $this->backendQuote->getCustomerId();
        } else {
            $customerId = $this->customerSession->getId();
        }
        /** @var load $customer */
        return $this->customerFactory->create()->load($customerId)->getEcCustId();
    }

    /**
     * Create and Save Payment
     *
     * @param InfoInterface|null $payment
     * @param array $paymentData
     * @return mixed
     * @throws LocalizedException
     */
    public function createAndSaveTransaction(?InfoInterface $payment = null, $paymentData = [])
    {
        try {
            /**
             * Get Order from Payment
             */
            $order = $payment->getOrder();
            $payment->setLastTransId($paymentData['id']);
            $payment->setTransactionId($paymentData['id']);
            $payment->setAdditionalInformation(
                [Transaction::RAW_DETAILS => (array)$paymentData]
            );
            $formatedPrice = $order->getBaseCurrency()->formatTxt(
                $order->getGrandTotal()
            );
            $message = __('The authorized amount is %1.', $formatedPrice);
            //get the object of builder class
            $trans = $this->_transactionBuilder;
            $transaction = $trans->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($paymentData['id'])
                ->setAdditionalInformation(
                    [Transaction::RAW_DETAILS => (array)$paymentData]
                )
                ->setFailSafe(true)
                /**
                 * build method creates the transaction and returns the object
                 * */

                ->build(Transaction::TYPE_CAPTURE);
            $payment->addTransactionCommentsToOrder(
                $transaction,
                $message
            );
            /**
             * payment set transaction
             */
            $payment->setParentTransactionId(null);
            /**
             * Save Payment
             */
            $payment->save();

            /**
             * Order Save for Transaction
             */
            $order->save();

            return $transaction->save()->getTransactionId();
        } catch (Exception $exception) {
            /** logging the error */
            $this->ebizchargeLogger->addError(__('Payment authorization error:  ' .
                $this->soapApiModel->getPaymentError()['error'] . '(' .
                $this->soapApiModel->getPaymentError()['errorcode'] . ')'));
            throw new LocalizedException(__('Payment authorization error:  ' .
                $this->soapApiModel->getPaymentError()['error'] . '(' .
                $this->soapApiModel->getPaymentError()['errorcode'] . ')'));
        }
    }

    /**
     * Can Void
     *
     * @return bool
     */
    public function canVoid(): bool
    {
        return true;
    }

    /**
     * Cancel and void the Transactions
     *
     * @param InfoInterface $payment
     * @return $this
     * @throws LocalizedException
     */
    public function cancel(InfoInterface $payment)
    {
        /** @var $storeId */
        $storeId = $payment->getOrder()->getStoreId() ?? $this->ebizConfig->getStoreId();
        $this->soapApiModel->initTransactionAPI($storeId);
        $isRecurring = false;
        /**
         * if we have only tokenization in Magento
         * in that case tans Id will be 0000 and we need to cancel
         */
        $lastTransactionId = $payment->getLastTransId();
        $parentTransactionId = $payment->getParentTransactionId();

        if (
            !$lastTransactionId ||
            str_contains($lastTransactionId, "000") ||
            str_contains($parentTransactionId, "000")
        ) {
            return $this;
        }

        if ($payment->getCcTransId()) {
            $order = $payment->getOrder();
            $command = PaymentInterface::EBIZCHARGE_METHOD_TRANSACTION_TYPE_VOID;
            $amount = $order->getTotalDue();

            $tran = $this->soapApiModel;
            $tran->setData('amount', $amount);
            $tran->setData('command', $command);
            $tran->setTransactionData($payment);

            // To set that transaction no whose type is 'authorization' because
            // void only possible for Auth only request
            $this->setTransactionReferenceNo($payment);

            $isRefund = false;

            $this->soapApiModel->setTotalOrderedQty((float)($order->getTotalQtyOrdered() ?? 0));

            /**
             * Run void Transactions
             */
            $isTransactionSuccess = $this->soapApiModel->runVoidTransaction(
                $payment,
                $isRecurring,
                $isRefund,
                $command
            );

            $resultCodeResponse = $tran->getData('resultcode') ?? PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_ERROR;

            $transType = PaymentInterface::EBIZCHARGE_METHOD_TRANSACTION_TYPE_VOID;
            $transApi = $this->soapApiModel;
            $transactionId = $transApi->getData('refnum');
            /** @var  $transData */
            $transData = [
                "resultcode" => $transApi->getData('resultcode') ??
                    PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_ERROR,
                "refnum" => $transactionId ?? "",
                "transtype" => $transApi->getData('transtype') ??
                    PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_ERROR
            ];
            if ($isTransactionSuccess) {
                $transactionId = $tran->getData('refnum');

                /** @var  $transData */
                $transData = [
                    "resultcode" => $tran->getData('resultcode') ??
                        PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_ERROR,
                    "refnum" => $transactionId ?? "",
                    "transtype" => $resultDataResponse = $tran->getData('transtype') ??
                        PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_ERROR
                ];
                /**
                 * pre and render response
                 */
                $this->prepareAndRenderResponse($payment, $transData, $transType);
            }
        } else {
            $payment->setStatus(AbstractMethod::STATUS_ERROR);
            $this->ebizchargeLogger->addError(__('Invalid transaction ID and declined'));
            throw new LocalizedException(__('Invalid transaction ID and declined'));
        }

        return $this;
    }

    /**
     * Returns saved payment methods.
     *
     * Load in Dropdown Frontend #14 added by IF Done
     *
     * @return array
     */
    public function getSavedAccounts($methodType = "check")
    {

        return $this->soapApiModel->getSavedBankAccounts($this->getEbzcCustToken(), $methodType);
    }

    /**
     * Returns saved payment methods.
     *
     * Load in Dropdown Frontend #14 added by IF Done
     *
     * @return array
     */
    public function getSavedBankAccounts($methodType = "check")
    {

        return $this->soapApiModel->getSavedBankAccounts($this->getEbzcCustToken(), $methodType);
    }

    /**
     * Get Ebiz Customer Token
     *
     * @return array|mixed|null
     */
    public function getEbzcCustToken()
    {
        if ($this->backendAuthSession->isLoggedIn()) {
            $customerId = $this->backendQuote->getCustomerId();
        } else {
            $customerId = $this->customerSession->getId();
        }
        /** @var load $customer */
        return $this->customerFactory->create()->load($customerId)->getEcCustToken();
    }

    /**
     * Get Saved Cards
     *
     * @return array
     */
    public function getSavedCards()
    {
        $ebzcCustomerId = $this->getEbzcCustToken();

        if ($ebzcCustomerId) {
            $paymentMethods = $this->soapApiModel->getCustomerPaymentMethods($ebzcCustomerId);

            $paymentMethodsNew = [];
            foreach ($paymentMethods as $key => $payment) {
                if ($payment->MethodType !== PaymentInterface::EBIZCHARGE_METHOD_TYPE_CHECK) {
                    $paymentMethodName = $payment->MethodName;
                    $paymentMethodJson = json_decode($paymentMethodName);

                    if (is_object($paymentMethodJson)) {
                        $paymentMethodName = $paymentMethodJson->a . "-" . $paymentMethodJson->b;
                    }
                    $payment->MethodName = $paymentMethodName;

                    $paymentMethodsNew[] = $payment;
                }
            }
            return $paymentMethodsNew;
        }

        return [];
    }

    /**
     * Has Token
     * @param mixed $storeId
     * @return bool
     */
    public function hasToken(mixed $storeId = "0"): bool
    {
        $isToken = false;
        if ($this->backendAuthSession->isLoggedIn()) {
            $customerId = $this->backendQuote->getCustomerId();
        } else {
            $customerId = $this->customerSession->getCustomerId();
        }
        $customer = $this->customerFactory->create()->load($customerId);
        /**
         * Saving ebizcharge data resource Model
         */
        $customer->getResource()->saveEbizchargeFields($customer);
        $customer = $this->customerFactory->create()->load($customerId);
        $customerToken = $customer->getEcCustToken();

        if ($customerToken) {
            $isToken = true;
        }
        return $isToken;
    }

    /**
     * Is Available
     *
     * @param CartInterface|null $quote
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isAvailable(?CartInterface $quote = null)
    {
        $storeId = $this->ebizConfig->getStoreId();
        $isActive = $this->ebizConfig->isActive($storeId);
        if (!$isActive) {
            return false;
        }
        return parent::isAvailable($quote);
    }
}

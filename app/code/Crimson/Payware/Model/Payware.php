<?php

namespace Crimson\Payware\Model;

use Crimson\Payware\Model\Payware\Request;
use Crimson\Payware\Model\Payware\Result;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\Cc;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Model\Order;

/**
 * Class Payware
 * @package Crimson\Payware\Model
 */
class Payware extends Cc
{

    const METHOD_CODE               = 'payware';

    const REQUEST_TYPE_AUTH_ONLY    = 'PRE_AUTH';
    const REQUEST_TYPE_AUTH_CAPTURE = 'SALE';

    const ACTION_AUTHORIZE         = self::REQUEST_TYPE_AUTH_ONLY;
    const ACTION_AUTHORIZE_CAPTURE = self::REQUEST_TYPE_AUTH_CAPTURE;

    const RESPONSE_CODE_INCORRECT_XML                = -2;
    const RESPONSE_CODE_SUCCESS                      = -1;
    const RESPONSE_CODE_UNKNOWN                      = 0;
    const RESPONSE_CODE_SETTLED                      = 2;
    const RESPONSE_CODE_CAPTURED                     = 4;
    const RESPONSE_CODE_APPROVED                     = 5;
    const RESPONSE_CODE_DECLINED                     = 6;
    const RESPONSE_CODE_VOIDED                       = 7;
    const RESPONSE_CODE_COMPLETED                    = 10;
    const RESPONSE_CODE_PARTCOMP                     = 16;
    const RESPONSE_CODE_TIP_MODIFIED                 = 17;
    const RESPONSE_CODE_SETTLEMENT_SCHEDULED         = 21;
    const RESPONSE_CODE_VERIFIED                     = 30;
    const RESPONSE_CODE_INVALID_CARD_NUMBER          = 93;
    const RESPONSE_CODE_INVALID_EXPIRATION_DATE      = 97;
    const RESPONSE_CODE_INVALID_AMOUNT               = 1010;
    const RESPONSE_CODE_INCORRECT_USER_ID_OR_USER_PW = 3100;

    protected $_code = self::METHOD_CODE;

    /**
     * Availability options
     */
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = false;
    protected $_canRefund = false;
    protected $_canRefundInvoicePartial = false;
    protected $_canVoid = false;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;
    protected $_canSaveCc = false;
    protected $_canFetchTransactionInfo = false;

    protected $_allowCurrencyCode = array('USD');

    /**
     * Fields that should be replaced in debug with '***'
     *
     * @var array
     */
    protected $_debugReplacePrivateDataKeys = ['ACCT_NUM', 'CVV2', 'MERCHANTKEY', ''];


    /** @var Payware\RequestFactory $_requestFactory */
    protected $_requestFactory;

    /** @var Payware\ResultFactory $_resultFactory */
    protected $_resultFactory;

    /** @var \Magento\Framework\Encryption\EncryptorInterface $_encryptor */
    protected $_encryptor;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Crimson\Payware\Model\Payware\RequestFactory $requestFactory,
        \Crimson\Payware\Model\Payware\ResultFactory $resultFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_requestFactory = $requestFactory;
        $this->_resultFactory = $resultFactory;
        $this->_encryptor = $encryptor;
        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $paymentData, $scopeConfig, $logger, $moduleList, $localeDate, $resource, $resourceCollection, $data);
    }


    /**
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this|Payware
     * @throws LocalizedException
     */
    public function authorize(InfoInterface $payment, $amount)
    {
        if ($amount <= 0) {
            throw new \Exception( __('Invalid amount for authorization.') );
        }

        $this->_place($payment, $amount, self::REQUEST_TYPE_AUTH_ONLY);
        $payment->setSkipTransactionCreation(true);

        return $this;

    }

    /**
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws LocalizedException
     * @throws \Exception
     */
    public function capture(InfoInterface $payment, $amount)
    {
        if ($amount <= 0) {
            throw new \Exception( __('Invalid amount for capture.') );
        }

        $this->_place($payment, $amount, self::REQUEST_TYPE_AUTH_CAPTURE);
        $payment->setSkipTransactionCreation(true);

        return $this;
    }


    /**
     * Send request with new payment to gateway
     *
     * @param InfoInterface $payment
     * @param $amount
     * @param $requestType
     * @return $this
     * @throws LocalizedException
     * @throws \Exception
     */
    protected function _place(InfoInterface $payment, $amount, $requestType)
    {
        $payment->setRequestType($requestType);
        $payment->setAmount($amount);
        $request = $this->_buildRequest($payment);
        $result  = $this->_postRequest($request);

        switch ($requestType) {
            case self::REQUEST_TYPE_AUTH_CAPTURE:
                $newTransactionType      = \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE;
                $defaultExceptionMessage = __('Payment capturing error.');
                break;
            case self::REQUEST_TYPE_AUTH_ONLY:
            default:
                $newTransactionType      = \Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH;
                $defaultExceptionMessage = __('Payment authorization error.');
                break;
        }

        switch ($result->getResponseCode()) {
            case self::RESPONSE_CODE_APPROVED:
            case self::RESPONSE_CODE_CAPTURED:
            case self::RESPONSE_CODE_SETTLED:
                $card = $this->_generateCard($request, $result, $payment);
                $payment->setAmountAuthorized((float) $result->getAuthorizedAmount());
                $payment->setBaseAmountAuthorized((float) $result->getAuthorizedAmount());
                $this->_addTransaction(
                    $payment,
                    $result->getTransactionId(),
                    $newTransactionType,
                    array('is_transaction_closed' => 0),
                    array('real_transaction_id' => $result->getTransactionId()),
                    $this->getTransactionMessage(
                        $payment, $requestType, $result->getTransactionId(), $card, $amount
                    )
                );

                $payment->setLastTransId($result->getTransactionId())
                    ->setCcTransId($result->getTransactionId())
                    ->setAdditionalInformation('real_transaction_id', $result->getTransactionId())
                    ->setAdditionalInformation('auth_code', $result->getAuthCode())
                    ->setAdditionalInformation('avs_code', $result->getAvsCode())
                    ->setAdditionalInformation('cvv_code', $result->getCvvCode())
                    ->setAdditionalInformation('auth_date', $result->getAuthorizationDate())
                    ->setAdditionalInformation('payware_card', $this->getPaywarePartialCard($payment->getCcNumber()));

                return $this;
            case self::RESPONSE_CODE_DECLINED:
                throw new \Exception('Gateway error: ' . $result->getResponseReasonText());
                break;
            default:
                $this->_logger->critical($defaultExceptionMessage. ' - Payware RESULT: '. $result->toJson());
                throw new \Exception($defaultExceptionMessage);
        }

        return $this;
    }

    /**
     * Given full card number return payware expected Partial Card in Format N-NNNN
     *
     * @param $card
     * @return string
     */
    public function getPaywarePartialCard($card): string
    {
        //remove all non-numeric characters, hyphens, spaces, etc.
        $card = preg_replace('/[^0-9]/i', '', $card);

        return sprintf('%s-%s', substr($card, 0, 1), substr($card, -4));
    }


    public function getTransactionMessage($payment, $requestType, $lastTransactionId, $card, $amount = false,
                                          $exception = false
    ) {
        return $this->getExtendedTransactionMessage(
            $payment, $requestType, $lastTransactionId, $card, $amount, $exception
        );
    }

    public function getExtendedTransactionMessage($payment, $requestType, $lastTransactionId, $card, $amount = false,
                                                  $exception = false, $additionalMessage = false
    ) {
        $operation = $this->_getOperation($requestType);

        if (!$operation) {
            return false;
        }

        if ($amount) {
            $amount = __('amount %1', $this->_formatPrice($payment, $amount));
        }

        if ($exception) {
            $result = __('failed');
        } else {
            $result = __('successful');
        }

        $card = __('Credit Card: xxxx-%2', $card->getCcLast4());

        $pattern = '%1 %2 %3 - %4.';
        $texts = array($card, $amount, $operation, $result);

        if (!is_null($lastTransactionId)) {
            $pattern .= ' %5.';
            $texts[] = __('Payware Transaction ID %1', $lastTransactionId);
        }

        if ($additionalMessage) {
            $pattern .= ' %6.';
            $texts[] = $additionalMessage;
        }
        $pattern .= ' %7';
        $texts[] = $exception;

        return __($pattern, $texts);
    }

    /**
     * Format price with currency sign
     *
     * @param  InfoInterface $payment
     * @param float $amount
     * @return string
     */
    protected function _formatPrice($payment, $amount): string
    {
        return $payment->getOrder()->getBaseCurrency()->formatTxt($amount);
    }

    /**
     * Return operation name for request type
     *
     * @param  string $requestType
     * @return bool|string
     */
    protected function _getOperation($requestType)
    {
        switch ($requestType) {
            case self::REQUEST_TYPE_AUTH_ONLY:
                return __('authorize');
            case self::REQUEST_TYPE_AUTH_CAPTURE:
                return __('authorize and capture');
            default:
                return false;
        }
    }

    /**
     * Add payment transaction
     *
     * @param InfoInterface $payment
     * @param $transactionId
     * @param $transactionType
     * @param array $transactionDetails
     * @param array $transactionAdditionalInfo
     * @param bool $message
     * @return mixed
     */
    protected function _addTransaction(InfoInterface $payment, $transactionId, $transactionType,
                                       array $transactionDetails = array(), array $transactionAdditionalInfo = array(), $message = false
    ) {
        $payment->setTransactionId($transactionId);
        $payment->resetTransactionAdditionalInfo();
        foreach ($transactionDetails as $key => $value) {
            $payment->setData($key, $value);
        }
        foreach ($transactionAdditionalInfo as $key => $value) {
            $payment->setTransactionAdditionalInfo($key, $value);
        }
        $transaction = $payment->addTransaction($transactionType);
        foreach ($transactionDetails as $key => $value) {
            $payment->unsetData($key);
        }
        $payment->unsLastTransId();

        /**
         * It for self using
         */
        $transaction->setMessage($message);

        $payment->addTransactionCommentsToOrder(
            $transaction,
            $message
        );


        return $transaction;
    }

    /**
     * @param InfoInterface $payment
     * @return Request
     */
    protected function _buildRequest(InfoInterface $payment): Request
    {
        /** @var Order $order */
        $order          = $payment->getOrder();

        /** @var OrderAddressInterface $billingAddress */
        $billingAddress = $order->getBillingAddress();

        $request = $this->_getRequest();
        $request->addData(
            array(
                'ACCT_NUM'        => $payment->getCcNumber(),
                'CARDHOLDER'      => $billingAddress->getName(),
                'COMMAND'         => $payment->getRequestType(),
                'CUSTOMER_STREET' => $billingAddress->getStreetLine(1),
                'CUSTOMER_ZIP'    => $billingAddress->getPostcode(),
                'CVV2'            => $payment->getCcCid(),
                'EXP_MONTH'       => $payment->getCcExpMonth(),
                'EXP_YEAR'        => substr($payment->getCcExpYear(), -2),
                'FUNCTION_TYPE'   => 'PAYMENT',
                'INVOICE'         => $order->getIncrementId(),
                'PAYMENT_TYPE'    => 'CREDIT',
                'PRESENT_FLAG'    => 1,
                //'PURCHASE_ID'     => 'PoNum',
                'TAX_AMOUNT'      => number_format($order->getBaseTaxAmount(), 2, '.', ''),
                'TRANS_AMOUNT'    => number_format($payment->getAmount(), 2, '.', ''),
            )
        );

        return $request;
    }

    /**
     * @param Request $request
     * @return Result
     * @throws \Zend_Date_Exception
     */
    protected function _postRequest(Request $request): Result
    {
        $action    = $this->getConfigPaymentAction();
        $debugData = array(
            'payment_action' => $action,
        );

        /** @var \Crimson\Payware\Model\Payware\Result $result */
        $result = $this->_resultFactory->create();

        try {
            $data = $request->getData();

            /** prepare field lengths */
            $data = $this->_truncateRequestValues($data);
            $debugData['request'] = $data;

            /** convert to xml */
            $xml = $this->_convertArrayToXml($data);

            $url = $this->_getCgiUrl();

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
            curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: text/xml'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);

            $data = curl_exec($ch);
            curl_close($ch);

            $response = simplexml_load_string($data);
        } catch (\Exception $e) {
            $result->setResponseCode(-1)
                ->setResponseReasonCode($e->getCode())
                ->setResponseReasonText($e->getMessage());

            $debugData['result'] = $result->getData();
            $this->_debug($debugData);

            throw new \Exception($this->_wrapGatewayError($e->getMessage()));
        }

        if (!isset($response) || !($response instanceof \SimpleXMLElement)) {
            /** lets determine what we can can show the customer. */
            throw new \Exception(__('Error in payment gateway.'));
        } else {
            $responseCode = (isset($response->RESULT_CODE) ? (string) $response->RESULT_CODE : self::RESPONSE_CODE_UNKNOWN);
            $responseReasonText = trim((string) $response->RESPONSE_TEXT);

            $result->setResponseCode($responseCode)
                ->setResponseReasonCode($responseCode)
                ->setResponseReasonText($responseReasonText)
                ->setResponseReasonText($responseReasonText)
                ->setResult((string) $response->AUTH_CODE)
                ->setAvsCode((string) $response->AVS_CODE)
                ->setCvvCode((string) $response->CVV2_CODE)
                ->setAuthCode((string) $response->AUTH_CODE)
                ->setTransactionId((string) $response->TROUTD)
                ->setOrderNumber((string) $response->INVOICE);

            if (!empty($response->TRANS_DATE)) {
                $result->setAuthorizationDate(str_replace('.', '-', (string) $response->TRANS_DATE));
            }

            if (isset($response->TRANS_AMOUNT)) {
                $result->setAuthorizedAmount((string)$response->TRANS_AMOUNT);
            }
        }

        $debugData['result'] = $result->getData();
        $this->_debug($debugData);

        return $result;
    }

    /**
     * @return string
     */
    protected function _getCgiUrl(): string
    {
        if ($this->getConfigData('test')) {
            return $this->getConfigData('test_cgi_url');
        }

        return $this->getConfigData('cgi_url');
    }

    /**
     * Return authorize payment request
     *
     * @return Request
     */
    protected function _getRequest(): Request
    {
        /** @var Request $request */
        $request = $this->_requestFactory->create();

        $request->setData(array(
            'CLIENT_ID'       => $this->getConfigData('client_id'),
            'MERCHANTKEY'     => $this->_encryptor->decrypt($this->getConfigData('merchant_key')),
            'USER_ID'         => $this->getConfigData('user_id'),
            'USER_PW'         => $this->_encryptor->decrypt($this->getConfigData('user_password')),
        ));

        return $request;
    }

    /**
     * Gateway response wrapper
     *
     * @param string $text
     *
     * @return string
     */
    protected function _wrapGatewayError($text): string
    {
        return __('Gateway error: %s', $text);
    }

    /**
     * @param Request $request
     * @param Result $result
     * @param InfoInterface $payment
     * @return DataObject
     */
    protected function _generateCard(Request $request, \Crimson\Payware\Model\Payware\Result $result, InfoInterface $payment): DataObject
    {
        $card = new DataObject();

        $card->setRequestedAmount($request->getAuthAmt())
            ->setLastTransId($result->getTransactionId())
            ->setProcessedAmount($request->getAuthAmt())
            ->setCcType($payment->getCcType())
            ->setCcOwner($payment->getCcOwner())
            ->setCcLast4($payment->getCcLast4())
            ->setCcExpMonth($payment->getCcExpMonth())
            ->setCcExpYear($payment->getCcExpYear())
            ->setCcSsIssue($payment->getCcSsIssue())
            ->setCcSsStartMonth($payment->getCcSsStartMonth())
            ->setCcSsStartYear($payment->getCcSsStartYear());

        return $card;
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function _convertArrayToXml($data)
    {
        $xml = new \SimpleXMLElement('<TRANSACTION/>');
        $this->_addRequestData($xml, $data);

        return $xml->asXML();
    }

    /**
     * @param \SimpleXMLElement $xml
     * @param $data
     * @return $this
     */
    protected function _addRequestData(\SimpleXMLElement $xml, $data) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $childNode = $xml->addChild($key);
                $this->_addRequestData($childNode, $value);
            } else {
                $xml->addChild($key, $value);
            }
        }

        return $this;
    }

    /**
     * @param $request
     * @return mixed
     */
    protected function _truncateRequestValues($request)
    {
        $maxFieldLengths = $this->_getMaximumFieldLengths();
        foreach ($request as $key => $value) {

            if (isset($maxFieldLengths[$key]) && strlen($value) > $maxFieldLengths[$key]) {
                $request[$key] = substr($request[$key],0, $maxFieldLengths[$key]);
            }
        }

        return $request;
    }

    /**
     * @return array
     */
    protected function _getMaximumFieldLengths(): array
    {
        return array(
            'AAV'                      => 32,
            'ABA_NUM'                  => 10,
            'ACH_TYPE'                 => 3,
            'ACCT_CURRENCY_CODE'       => 3,
            'ACCT_NUM'                 => 40,
            'ALT_TAX_ID'               => 15,
            'AMOUNT_BALANCE'           => 13,
            'AMOUNT_CLINIC'            => 12,
            'AMOUNT_DENTAL'            => 12,
            'AMOUNT_HEALTHCARE'        => 12,
            'AMOUNT_PRESCRIPTION'      => 12,
            'AMOUNT_VISION'            => 12,
            'AMX_DESCRIPTION_n'        => 75,
            'AUTH_CODE'                => 16,
            'AVS_CODE'                 => 2,
            'BALANCE_ENQ'              => 5,
            'BATCH_BALANCE'            => 12,
            'BATCH_CONTROL_NUM'        => 12,
            'BATCH_COUNT'              => 10,
            'BATCH_NUM'                => 12,
            'BATCH_SEQ_NUM'            => 5,
            'BATCH_TRACE_ID'           => 40,
            'BEVERAGE_AMOUNT'          => 12,
            'BILLING_ADDRESS'          => 30,
            'BILLING_ADDRESS2'         => 30,
            'BILLING_CITY'             => 30,
            'BILLING_COUNTRY'          => 5,
            'BILLING_PHONE'            => 12,
            'BILLING_STATE'            => 2,
            'BILLING_ZIP'              => 10,
            'CARDHOLDER'               => 30,
            'CARD_ID_CODE'             => 1,
            'CASHBACK_AMNT'            => 10,
            'CASHIER_ID'               => 12,
            'CASHIER_NUM'              => 12,
            'CAVV'                     => 40,
            'CDD_DATA'                 => 30,
            'CHECK_NUM'                => 10,
            'CHECK_TYPE'               => 10,
            'CLERK_ID'                 => 8,
            'CLIENT_ID'                => 16,
            'CMRCL_FLAG'               => 1,
            'CMRCL_TYPE'               => 1,
            'COL_n'                    => 255,
            'COMMAND'                  => 30,
            'COMMERCIAL_FLAG'          => 5,
            'CONV_FEE'                 => 7,
            'CREDIT_PLAN_NBR'          => 5,
            'CTROUTD'                  => 10,
            'CUSTOMER_ACCT_TYPE'       => 10,
            'CUSTOMER_ADDRESS'         => 50,
            'CUSTOMER_ADDRESS2'        => 50,
            'CUSTOMER_BANK'            => 25,
            'CUSTOMER_CITY'            => 50,
            'CUSTOMER_CODE'            => 25,
            'CUSTOMER_COUNTRY'         => 5,
            'CUSTOMER_DOB'             => 8,
            'CUSTOMER_EMAIL'           => 75,
            'CUSTOMER_FIRSTNAME'       => 35,
            'CUSTOMER_FULLNAME'        => 100,
            'CUSTOMER_ID_DD'           => 2,
            'CUSTOMER_ID_MM'           => 2,
            'CUSTOMER_ID_NUM'          => 30,
            'CUSTOMER_ID_STATE'        => 2,
            'CUSTOMER_ID_TYPE'         => 15,
            'CUSTOMER_ID_YY'           => 4,
            'CUSTOMER_IP_ADDRESS'      => 20,
            'CUSTOMER_LASTNAME'        => 50,
            'CUSTOMER_MIDNAME'         => 25,
            'CUSTOMER_PHONE'           => 12,
            'CUSTOMER_SECONDARY_PHONE' => 12,
            'CUSTOMER_SSN'             => 9,
            'CUSTOMER_SSN_LAST4'       => 4,
            'CUSTOMER_STATE'           => 2,
            'CUSTOMER_STREET'          => 20,
            'CUSTOMER_STREET2'         => 20,
            'CUSTOMER_ZIP'             => 9,
            'CV_PRESENCE_IND'          => 1,
            'CV_RSP_TYPE'              => 1,
            'CVV2'                     => 10,
            'CVV2_CODE'                => 4,
            'DEBIT_TYPE'               => 8,
            'DEST_COUNTRY_CODE'        => 3,
            'DEST_ZIP_CODE'            => 10,
            'DEVICEKEY'                => 64,
            'DEVTYPE'                  => 30,
            'DL_NUMBER'                => 12,
            'DL_STATE'                 => 2,
            'EBT_TYPE'                 => 16,
            'EBT_VOUCHER_NUM'          => 16,
            'ECI'                      => 1,
            'ECOMM_GOODS_IND'          => 1,
            'EMP_NUM'                  => 10,
            'END_BATCH_DATE'           => 10,
            'END_BATCH_TIME'           => 10,
            'EXP_MONTH'                => 2,
            'EXP_YEAR'                 => 4,
            'FIRSTNAME'                => 30,
            'FOOD_AMOUNT'              => 12,
            'FORCE_FLAG'               => 5,
            'FUNCTION_TYPE'            => 16,
            'GIFT_SECURITY_CODE'       => 6,
            'GIFT_SEQ_NUM'             => 10,
            'GIFT_TRAN_ID'             => 15,
            'GIFT_UNITS'               => 7,
            'INSTALLMENT'              => 1,
            'INSTALLMENT_NUM'          => 4,
            'INSTALLMENT_TOTAL'        => 4,
            'INVOICE'                  => 20,
            'KEY_SERIAL_NUMBER'        => 20,
            'LASTNAME'                 => 30,
            'LOYALTY_FLAG'             => 5,
            'LPTOKEN'                  => 19,
            'MERCH_NUM'                => 20,
            'MERCHANTKEY'              => 80,
            'MICR'                     => 128,
            'MICR_READER_STATUS'       => 26,
            'MIDDLENAME'               => 30,
            'MIMETYPE'                 => 20,
            'MODIFIER'                 => 16,
            'ORIG_COMMAND'             => 16,
            'ORIG_PURCH_DATA'          => 12,
            'ORIG_SEQ_NUM'             => 10,
            'ORIG_TRANS_AMOUNT'        => 12,
            'PASSWORD'                 => 10,
            'PAYMENT_MEDIA'            => 12,
            'PAYMENT_TYPE'             => 16,
            'PHONE_NUMBER'             => 30,
            'PIN_BLOCK'                => 16,
            'PRESENT_FLAG'             => 2,
            'PROCESSING_CODE'          => 6,
            'PROCESSOR_ID'             => 20,
            'PRODUCT_CODE'             => 6,
            'PT_TRN_TYPE'              => 1,
            'PURCHASE_ID'              => 25,
            'RECURRING'                => 1,
            'REFERENCE'                => 16,
            'REFERENCE_FILE'           => 50,
            'REF_TROUTD'               => 10,
            'RESPONSE_CODE'            => 2,
            'RESPONSE_TEXT'            => 100,
            'RESULT'                   => 10,
            'RESULT_CODE'              => 10,
            'RETURN_ACI'               => 1,
            'RETURN_AMOUNT'            => 12,
            'RETURN_COUNT'             => 10,
            'RETURNS_AMOUNT'           => 12,
            'RETURNS_COUNT'            => 10,
            'SALE_AMOUNT'              => 12,
            'SALE_COUNT'               => 10,
            'SALES_AMOUNT'             => 12,
            'SALES_COUNT'              => 10,
            'SERIAL_NUM'               => 20,
            'SERVER_ID'                => 10,
            'SHIFT_ID'                 => 20,
            'SHIPTO_ZIP'               => 10,
            'START_DATE'               => 10,
            'STARTDATE'                => 8,
            'SUPP_ID_DATA'             => 35,
            'SUPP_ID_TYPE'             => 2,
            'SURCHARGE_AMOUNT'         => 12,
            'TAX_AMOUNT'               => 10,
            'TAX_IND'                  => 1,
            'TIP_AMOUNT'               => 10,
            'TOT_NUM_CARDS'            => 3,
            'TOTAL_AMOUNT'             => 10,
            'TPP_ID'                   => 6,
            'TRACE_CODE'               => 30,
            'TRACK_DATA'               => 120,
            'TRANS_AMOUNT'             => 12,
            'TRANS_DATE'               => 10,
            'TRANS_ID'                 => 1,
            'TRANS_SEQ_FIRST'          => 10,
            'TRANS_SEQ_FLAG'           => 1,
            'TRANS_SEQ_LAST'           => 10,
            'TRANS_SEQ_NUM'            => 10,
            'TRANS_TIME'               => 12,
            'TRANSACTION_ID'           => 15,
            'TROUTD'                   => 10,
            'USER_ID'                  => 8,
            'USER_PW'                  => 24,
            'XID'                      => 40,
        );
    }
}

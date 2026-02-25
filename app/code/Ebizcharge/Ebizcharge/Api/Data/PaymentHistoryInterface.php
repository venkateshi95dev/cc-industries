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

namespace Ebizcharge\Ebizcharge\Api\Data;

/**
 * Interface PaymentHistoryInterface
 *
 * Payment History Data Interface
 */
interface PaymentHistoryInterface
{
    /**
     * Admin Grid Class
     *
     * @const ADMIN_GRID_CLASS
     */
    public const ADMIN_GRID_CLASS = 'history';

    /**
     * Result Code Accepted
     *
     * @const: RESULT_CODE_ACCEPTED
     */
    public const RESULT_CODE_ACCEPTED = 'accepted';

    /**
     * Result Code Approved
     *
     * @const: RESULT_CODE_APPROVED
     */
    public const RESULT_CODE_APPROVED = 'approved';

    /**
     * Result Declined
     *
     * @const RESULT_CODE_DECLINED
     */
    public const RESULT_CODE_DECLINED = 'declined';

    /**
     * Result Rejcted
     *
     * @const RESULT_CODE_REJECTED
     */
    public const RESULT_CODE_REJECTED = 'rejected';

    /**
     * Ebizcharge Payment History Entity Id
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_ENTITY_ID
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_ENTITY_ID = 'entity_id';

    /**
     * Ebizcharge Payment History Payment Reference Number
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_REFERENCE_NUMBER
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_REFERENCE_NUMBER = 'payment_ref_number';

    /**
     * Ebizcharge Payment history Recurring
     * Scheduled Payment Internal Id
     *
     * @const: EBIZCHARGE_PAYMENT_HISTORY_RECURRING_SCHEDULED_PAYMENT_INTERNAL_ID
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_RECURRING_SCHEDULED_PAYMENT_INTERNAL_ID =
        'eb_rec_scheduled_payment_internal_id';

    /**
     * Ebizcharge Payment ID
     *
     * @const: EBIZCHARGE_PAYMENT_HISTORY_RECURRING_PAYMENT_INTERNAL_ID
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_RECURRING_PAYMENT_INTERNAL_ID = 'eb_rec_payment_internal_id';

    /**
     * Ebizcharge Payment Customer Number
     *
     * @const: EBIZCHARGE_PAYMENT_HISTORY_RECURRING_PAYMENT_CUSTOMER_NUMBER
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_RECURRING_PAYMENT_CUSTOMER_NUMBER = 'eb_payment_cust_number';

    /**
     * Ebizcharge Payment Method Id
     *
     * @const: EBIZCHARGE_PAYMENT_HISTORY_RECURRING_PAYMENT_METHOD_ID
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_RECURRING_PAYMENT_METHOD_ID = 'eb_payment_method_id';

    /**
     * Ebizcharge Payment History Customer Id
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_CUSTOMER_ID
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_CUSTOMER_ID = 'customer_id';

    /**
     * Ebizcharge Payment History Customer Email
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_CUSTOMER_EMAIL
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_CUSTOMER_EMAIL = 'customer_email';

    /**
     * Ebizcharge Payment History Customer Name
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_CUSTOMER_NAME
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_CUSTOMER_NAME = 'customer_name';

    /**
     * Ebizcharge Payment History Payment Source
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_SOURCE
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_SOURCE = 'payment_source';

    /**
     * Ebizcharge Payment History Payment Amount
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_AMOUNT
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_AMOUNT = 'payment_amount';

    /**
     * Ebizcharge Payment History Payment Card Info
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_CARD_INFO
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_CARD_INFO = 'payment_card_info';

    /**
     * Ebizcharge Payment History Payment DateTime
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_DATETIME
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_DATETIME = 'payment_datetime';

    /**
     * Ebizcharge Payment History Store Id
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_STORE_ID
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_STORE_ID = 'store_id';

    /**
     * Ebizcharge Payment History Server Ip
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_SERVER_IP
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_SERVER_IP = 'server_ip';

    /**
     * Ebizcharge Payment History Payment Response
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_RESPONSE
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_RESPONSE = 'payment_response';

    /**
     * Ebizcharge Payment History Payment Line Items
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_LINE_ITEMS
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_LINE_ITEMS = 'payment_line_items';

    /**
     * Ebizcharge Payment History Payment Detail
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_DETAIL
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_DETAIL = 'payment_detail';

    /**
     * Ebizcharge Payment History Payment Detail User
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_DETAIL_USER
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_DETAIL_USER = 'payment_detail_user';

    /**
     * Ebizcharge Payment History Payment Detail Customer Id
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_DETAIL_CUSTOMER_ID
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_DETAIL_CUSTOMER_ID = 'payment_detail_customer_id';

    /**
     * Ebizcharge Payment History Payment Detail Client Ip
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_DETAIL_CLIENT_IP
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_DETAIL_CLIENT_IP = 'payment_detail_client_ip';

    /**
     * Ebizcharge Payment History Payment Detail Card Data
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_DETAIL_CARD_DATA
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_DETAIL_CARD_DATA = 'payment_detail_card_data';

    /**
     * Ebizcharge Payment History Payment Check Trace
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_CHECK_TRACE
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_CHECK_TRACE = 'payment_check_trace';

    /**
     * Ebizcharge Payment History Payment Check Data
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_CHECK_DATA
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_CHECK_DATA = 'payment_check_data';

    /**
     * Ebizcharge Payment History Payment Shipping Address
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_SHIPPING_ADDRESS
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_SHIPPING_ADDRESS = 'payment_shipping_address';

    /**
     * Ebizcharge Payment History Payment Billing Address
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_BILLING_ADDRESS
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_BILLING_ADDRESS = 'payment_billing_address';

    /**
     * Ebizcharge Payment History Payment Account Holder
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_ENTITY_ID
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_ACCOUNT_HOLDER = 'payment_account_holder';

    /**
     * Ebizcharge Payment History Payment Status
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_STATUS
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_STATUS = 'payment_status';

    /**
     * Ebizcharge Payment History Payment Status Code
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_STATUS_CODE
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_STATUS_CODE = 'status_code';

    /**
     * Ebizcharge Payment History Payment Results
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_RESULT
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_RESULT = 'payment_result';

    /**
     * Ebizcharge Payment History AVS Result
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_AVS_RESULT
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_AVS_RESULT = 'avs_result';

    /**
     * Ebizcharge Payment History Card Code Result
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_CARD_CODE_RESULT
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_CARD_CODE_RESULT = 'card_code_result';

    /**
     * Ebizcharge Payment History Payment Error
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_ERROR
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_ERROR = 'payment_error';

    /**
     * Ebizcharge Payment History Payment Error Code
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_ERROR_CODE
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_ERROR_CODE = 'payment_error_code';

    /**
     * Ebizcharge Payment History Payment Transaction Type
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_TRANSACTION_TYPE
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_TRANSACTION_TYPE = 'payment_transaction_type';

    /**
     * Ebizcharge Payment History Payment Result Card Info
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_RESULT_CARD_INFO
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_RESULT_CARD_INFO = 'result_card_info';

    /**
     * Ebizcharge Payment History Payment Result Status
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_RESULT_STATUS
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_RESULT_STATUS = 'result_status';

    /**
     * Ebizcharge Payment History Created At
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_CREATED_AT
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_CREATED_AT = 'created_at';

    /**
     * Ebizcharge Payment History Modified At
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_MODIFIED_AT
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_MODIFIED_AT = 'modified_at';

    /**
     * Ebizcharge Payment History Last Downloaded Counter
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_LAST_DOWNLOADED_COUNTER
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_LAST_DOWNLOADED_COUNTER = 'last_downloaded_counter';

    /**
     * Ebizcharge Payment History Last downloaded Payments
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_LAST_DOWNLOADED_PAYMENTS
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_LAST_DOWNLOADED_PAYMENTS = 'last_downloaded_payments';

    /**
     * Ebizcharge Payment History Last Synced Date Time
     *
     * @const EBIZCHARGE_PAYMENT_HISTORY_LAST_SYNCED_DATE_TIME
     */
    public const EBIZCHARGE_PAYMENT_HISTORY_LAST_SYNCED_DATE_TIME = 'last_synced_date_time';

    /**
     * Get Payment Reference Number
     *
     * @return string
     */
    public function getPaymentRefNumber(): string;

    /**
     * Get Ebiz Scheduled Payment Internal Id
     *
     * @return string
     */
    public function getEbizScheduledPaymentInternalId(): string;

    /**
     * Set EbizPayment Scheduled Intenal ID
     *
     * @param mixed $ebizPaymentsScheduledInternalId
     * @return PaymentHistoryInterface
     */
    public function setEbizScheduledPaymentInternalId($ebizPaymentsScheduledInternalId): PaymentHistoryInterface;

    /**
     * Get Ebizcharge Payment ID
     *
     * @return string
     */
    public function getEbizPaymentInternalId(): string;

    /**
     * Set Ebizcharge Payment Internal Id
     *
     * @param mixed $ebizPaymentInternalId
     * @return PaymentHistoryInterface
     */
    public function setEbizPaymentInternalId($ebizPaymentInternalId): PaymentHistoryInterface;

    /**
     * Get Ebizcharge Customer Number
     *
     * @return string
     */
    public function getEbizPaymentCustNumber(): string;

    /**
     * Set Ebiz Payment Customer Number
     *
     * @param mixed $ebizPaymentCustNumber
     * @return PaymentHistoryInterface
     */
    public function setEbizPaymentCustNumber($ebizPaymentCustNumber): PaymentHistoryInterface;

    /**
     * Get Customer Id
     *
     * @return string
     */
    public function getCustomerId(): string;

    /**
     * Get Customer Email
     *
     * @return string
     */
    public function getCustomerEmail(): string;

    /**
     * Get Customer Name
     *
     * @return string
     */
    public function getCustomerName(): string;

    /**
     * Get Payment Source
     *
     * @return string
     */
    public function getPaymentSource(): string;

    /**
     * Get Payment Amount
     *
     * @return string
     */
    public function getPaymentAmount(): string;

    /**
     * Get Payment Card Info
     *
     * @return string
     */
    public function getPaymentCardInfo(): string;

    /**
     * Get Payment Date Time
     *
     * @return string
     */
    public function getPaymentDatetime(): string;

    /**
     * Get Store Id
     *
     * @return string
     */
    public function getStoreId(): string;

    /**
     * Get Server Ip
     *
     * @return string
     */
    public function getServerIp(): string;

    /**
     * Get Payment Response
     *
     * @return string
     */
    public function getPaymentResponse(): string;

    /**
     * Get Payment Line Items
     *
     * @return string
     */
    public function getPaymentLineItems(): string;

    /**
     * Get Payment Detail
     *
     * @return string
     */
    public function getPaymentDetail(): string;

    /**
     * Get Payment Detail User
     *
     * @return string
     */
    public function getPaymentDetailUser(): string;

    /**
     * Get Payment Detail Customer Id
     *
     * @return string
     */
    public function getPaymentDetailCustomerId(): string;

    /**
     * Get Payment Detail Client Ip
     *
     * @return string
     */
    public function getPaymentDetailClientIp(): string;

    /**
     * Get Payment Detail Card Data
     *
     * @return string
     */
    public function getPaymentDetailCardData(): string;

    /**
     * Get Payment Check Trace
     *
     * @return string
     */
    public function getPaymentCheckTrace(): string;

    /**
     * Get Payment Check Data
     *
     * @return string
     */
    public function getPaymentCheckData(): string;

    /**
     * Get Payment Shipping Address
     *
     * @return string
     */
    public function getPaymentShippingAddress(): string;

    /**
     * Get Payment Billing Address
     *
     * @return string
     */
    public function getPaymentBillingAddress(): string;

    /**
     * Get Payment Account Holder
     *
     * @return string
     */
    public function getPaymentAccountHolder(): string;

    /**
     * Get Payment Status
     *
     * @return string
     */
    public function getPaymentStatus(): string;

    /**
     * Get Payment Transaction Type
     *
     * @return string
     */
    public function getPaymentTransactionType(): string;

    /**
     * Get created At
     *
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * Get Modified At
     *
     * @return string
     */
    public function getModifiedAt(): string;

    /**
     * Get Payment Results
     *
     * @return string
     */
    public function getPaymentResult(): string;

    /**
     * Get Avs Result
     *
     * @return string
     */
    public function getAvsResult(): string;

    /**
     * Get Card Code Result
     *
     * @return string
     */
    public function getCardCodeResult(): string;

    /**
     * Get Payment Error
     *
     * @return string
     */
    public function getPaymentError(): string;

    /**
     * Get Payment Error Code
     *
     * @return string
     */
    public function getPaymentErrorCode(): string;

    /**
     * Get Payment History Set Last Downloaded Counter
     *
     * @return string
     */
    public function getLastDownloadedCounter(): string;

    /**
     * Get Last Downloaded Payments
     *
     * @return string
     */
    public function getLastDownloadedPayments(): string;

    /**
     * Get Payment History Last Synced Date Time
     *
     * @return string
     */
    public function getLastSyncedDateTime(): string;

    /**
     * Set Avs Result
     *
     * @param string $avsResult
     * @return PaymentHistoryInterface
     */
    public function setAvsResult(string $avsResult): PaymentHistoryInterface;

    /**
     * Set Card Code Result
     *
     * @param string $cardCodeResult
     * @return PaymentHistoryInterface
     */
    public function setCardCodeResult(string $cardCodeResult): PaymentHistoryInterface;

    /**
     * Set Payment Error
     *
     * @param string $paymentError
     * @return PaymentHistoryInterface
     */
    public function setPaymentError(string $paymentError): PaymentHistoryInterface;

    /**
     * Set Payment Error Code
     *
     * @param string $paymentErrorCode
     * @return PaymentHistoryInterface
     */
    public function setPaymentErrorCode(string $paymentErrorCode): PaymentHistoryInterface;

    /**
     * Set Payment Reference Number
     *
     * @param string $paymentRefNumber
     * @return PaymentHistoryInterface
     */
    public function setPaymentRefNumber(string $paymentRefNumber): PaymentHistoryInterface;

    /**
     * Set Customer Id
     *
     * @param string $customerId
     * @return PaymentHistoryInterface
     */
    public function setCustomerId(string $customerId): PaymentHistoryInterface;

    /**
     * Set Customer Email
     *
     * @param string $customerEmail
     * @return PaymentHistoryInterface
     */
    public function setCustomerEmail(string $customerEmail): PaymentHistoryInterface;

    /**
     * Set Customer Name
     *
     * @param string $customerName
     * @return PaymentHistoryInterface
     */
    public function setCustomerName(string $customerName): PaymentHistoryInterface;

    /**
     * Set Payment Source
     *
     * @param string $paymentSource
     * @return PaymentHistoryInterface
     */
    public function setPaymentSource(string $paymentSource): PaymentHistoryInterface;

    /**
     * Set Payment Amount
     *
     * @param string $paymentAmount
     * @return PaymentHistoryInterface
     */
    public function setPaymentAmount(string $paymentAmount): PaymentHistoryInterface;

    /**
     * Set Payment Card Info
     *
     * @param string $paymentCardInfo
     * @return PaymentHistoryInterface
     */
    public function setPaymentCardInfo(string $paymentCardInfo): PaymentHistoryInterface;

    /**
     * Set Payment Datetime
     *
     * @param string $paymentDatetime
     * @return PaymentHistoryInterface
     */
    public function setPaymentDatetime(string $paymentDatetime): PaymentHistoryInterface;

    /**
     * Set Store Id
     *
     * @param string $storeId
     * @return PaymentHistoryInterface
     */
    public function setStoreId(string $storeId): PaymentHistoryInterface;

    /**
     * Set Server Ip
     *
     * @param string $serverIp
     * @return PaymentHistoryInterface
     */
    public function setServerIp(string $serverIp): PaymentHistoryInterface;

    /**
     * Set Payment Response
     *
     * @param string $paymentResponse
     * @return PaymentHistoryInterface
     */
    public function setPaymentResponse(string $paymentResponse): PaymentHistoryInterface;

    /**
     * Set Payment Line Items
     *
     * @param string $paymentLineItems
     * @return PaymentHistoryInterface
     */
    public function setPaymentLineItems(string $paymentLineItems): PaymentHistoryInterface;

    /**
     * Set Payment Detail
     *
     * @param string $paymentDetail
     * @return PaymentHistoryInterface
     */
    public function setPaymentDetail(string $paymentDetail): PaymentHistoryInterface;

    /**
     * Set Payment Detail User
     *
     * @param string $paymentDetailUser
     * @return PaymentHistoryInterface
     */
    public function setPaymentDetailUser(string $paymentDetailUser): PaymentHistoryInterface;

    /**
     * Set Payment Detail Customer Id
     *
     * @param string $detailCustomerId
     * @return PaymentHistoryInterface
     */
    public function setPaymentDetailCustomerId(string $detailCustomerId): PaymentHistoryInterface;

    /**
     * Set Payment Detail Client Ip
     *
     * @param string $paymentDetailClientIp
     * @return PaymentHistoryInterface
     */
    public function setPaymentDetailClientIp(string $paymentDetailClientIp): PaymentHistoryInterface;

    /**
     * Set Payment Detail Card Data
     *
     * @param string $paymentDetailCardData
     * @return PaymentHistoryInterface
     */
    public function setPaymentDetailCardData(string $paymentDetailCardData): PaymentHistoryInterface;

    /**
     * Set Payment Check Trace
     *
     * @param string $paymentCheckTrace
     * @return PaymentHistoryInterface
     */
    public function setPaymentCheckTrace(string $paymentCheckTrace): PaymentHistoryInterface;

    /**
     * Set Payment Check Data
     *
     * @param string $paymentCheckData
     * @return PaymentHistoryInterface
     */
    public function setPaymentCheckData(string $paymentCheckData): PaymentHistoryInterface;

    /**
     * Set Payment Shipping Address
     *
     * @param string $paymentShippingAddress
     * @return PaymentHistoryInterface
     */
    public function setPaymentShippingAddress(string $paymentShippingAddress): PaymentHistoryInterface;

    /**
     * Set Payment Billing Address
     *
     * @param string $paymentBillingAddress
     * @return PaymentHistoryInterface
     */
    public function setPaymentBillingAddress(string $paymentBillingAddress): PaymentHistoryInterface;

    /**
     * Set Payment Account Holder
     *
     * @param string $paymentAccountHolder
     * @return PaymentHistoryInterface
     */
    public function setPaymentAccountHolder(string $paymentAccountHolder): PaymentHistoryInterface;

    /**
     * Set Payment Status
     *
     * @param string $paymentStatus
     * @return PaymentHistoryInterface
     */
    public function setPaymentStatus(string $paymentStatus): PaymentHistoryInterface;

    /**
     * Set Payment Result
     *
     * @param string $paymentResult
     * @return PaymentHistoryInterface
     */
    public function setPaymentResult(string $paymentResult): PaymentHistoryInterface;

    /**
     * Set Payment Transaction Type
     *
     * @param string $paymentTransactionType
     * @return PaymentHistoryInterface
     */
    public function setPaymentTransactionType(string $paymentTransactionType): PaymentHistoryInterface;

    /**
     * Set Created At
     *
     * @param mixed $createdAt
     * @return PaymentHistoryInterface
     */
    public function setCreatedAt($createdAt): PaymentHistoryInterface;

    /**
     * Set Modified At
     *
     * @param mixed $modifiedAt
     * @return PaymentHistoryInterface
     */
    public function setModifiedAt($modifiedAt): PaymentHistoryInterface;

    /**
     * Get Payment Status Code
     *
     * @return string
     */
    public function getPaymentStatusCode(): string;

    /**
     * Set Payment Status Code
     *
     * @param mixed $paymentStatusCode
     * @return PaymentHistoryInterface
     */
    public function setPaymentStatusCode($paymentStatusCode): PaymentHistoryInterface;

    /**
     * Get Payment Result Card Info
     *
     * @return string
     */
    public function getResultCardInfo(): string;

    /**
     * Set Payment Result Card Info
     *
     * @param mixed $resultCardInfo
     * @return PaymentHistoryInterface
     */
    public function setResultCardInfo($resultCardInfo): PaymentHistoryInterface;

    /**
     * Get Payment Result Status
     *
     * @return string
     */
    public function getResultStatus(): string;

    /**
     * Set Payment Result Status
     *
     * @param mixed $resultStatus
     * @return PaymentHistoryInterface
     */
    public function setResultStatus($resultStatus): PaymentHistoryInterface;

    /**
     * Payment History Set Last Downloaded Counter
     *
     * @param mixed $lastDownloadedCounter
     * @return PaymentHistoryInterface
     */
    public function setLastDownloadedCounter($lastDownloadedCounter): PaymentHistoryInterface;

    /**
     * Set Last Downloaded Payments
     *
     * @param mixed $lastDownloadedPayments
     * @return PaymentHistoryInterface
     */
    public function setLastDownloadedPayments($lastDownloadedPayments): PaymentHistoryInterface;

    /**
     * Payment History Last Synced Date Time
     *
     * @param mixed $lastSyncedDateTime
     * @return PaymentHistoryInterface
     */
    public function setLastSyncedDateTime($lastSyncedDateTime): PaymentHistoryInterface;
}

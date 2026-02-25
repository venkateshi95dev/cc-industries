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
 * Interface SoapApiModelInterface
 *
 * Soap Api Model Data Interface
 */
interface SoapApiModelInterface
{
    /**
     * Ebizcharge Version
     *
     * @const: EBIZCHARGE_VERSION
     */
    public const EBIZCHARGE_VERSION = '4.0.0.0';

    /**
     * @count: EBIZCHARGE_SETUP_VERSION
     */
    public const EBIZCHARGE_SETUP_VERSION = "2.4.7";

    /**
     * Transaction Error
     *
     * @const: EBIZCHARGE_TRANSACTION_ERROR
     */
    public const EBIZCHARGE_TRANSACTION_ERROR = "Transaction Authentication Error with EBizCharge Payment Gateway";

    /**
     * Envoirnment Prefix
     *
     * @const: ENVOIRNMENT_PREFIX
     */
    public const ENVOIRNMENT_PREFIX = 'Mag2';

    /**
     * Default Page Listing Limit
     *
     * @const: DEFAULT_PAGE_LISTING_LIMIT
     */
    public const DEFAULT_PAGE_LISTING_LIMIT = 10;

    /**
     *  Default Days Before Listings
     *
     * @const  DEFAULT_DAYS_BEFORE_LISTINGS
     */
    public const DEFAULT_DAYS_BEFORE_LISTINGS = 3650;

    /**
     *  Default Days Payment history Before Listings
     *
     * @const  DEFAULT_DAYS_PAYMENT_HISTORY_BEFORE_LISTINGS
     */
    public const DEFAULT_DAYS_PAYMENT_HISTORY_BEFORE_LISTINGS = 30;

    /**
     * ACH
     *
     * @const ACH
     */
    public const ACH = 'ACH';

    /**
     * Ebizcharge Magento Software
     *
     * @const EBIZCHARGE_MAGENTO_SOFTWARE
     */
    public const EBIZCHARGE_MAGENTO_SOFTWARE = 'Magento2';

    /**
     *  Ebizcharge Division Id
     *
     * @const: EBIZCHARGE_DIVISION_ID
     */
    public const EBIZCHARGE_DIVISION_ID = 'Mag2';

    /**
     * EBizCharge SOAP API Gateway URL
     *
     * @const EBIZCHARGE_SOAP_API_GATEWAY_URL
     */
    public const EBIZCHARGE_SOAP_API_GATEWAY_URL = 'https://soap.ebizcharge.net';

    /**
     * Recurring Payment Status SUSPENDED
     *
     * @const RECURRING_PAYMENT_STATUS_SUSPENDED
     */
    public const RECURRING_PAYMENT_STATUS_SUSPENDED = 1;

    /**
     * Recurring Payment Status UNSUSPENDED
     *
     * @const RECURRING_PAYMENT_STATUS_UNSUSPENDED
     */
    public const RECURRING_PAYMENT_STATUS_UNSUSPENDED = 0;

    /**
     * Staging EBizCharge API Gateway URL
     *
     * @const EBIZCHARGE_SOAP_WSDL_API_GATEAY_STAGING_URL
     */
    public const EBIZCHARGE_SOAP_WSDL_API_GATEAY_STAGING_URL =
        'https://ebizsoapapidev1.azurewebsites.net/eBizService.svc?singleWsdl';

    /**
     * Production EBizCharge API Gateway URL
     *
     * @const EBIZCHARGE_SOAP_WSDL_API_GATEAY_PRODUCTION_URL
     */
    // const EBIZCHARGE_SOAP_WSDL_API_GATEAY_PRODUCTION_URL = 'https://soap.ebizcharge.net/eBizService.svc?singleWsdl';

    /**
     * Production EBizCharge API Gateway URL
     *
     * @const EBIZCHARGE_SOAP_WSDL_API_GATEAY_PRODUCTION_URL
     */
    public const EBIZCHARGE_SOAP_WSDL_API_GATEAY_PRODUCTION_URL =
        'https://soapapi1.ebizcharge.net/v2/wsdl/ebizsoap1.wsdl';

    /**
     * EBizCharge API Gateway URL for 3D SECURE
     *
     * @const EBIZCHARGE_SOAP_WSDL_3DSECURE_URL
     */
    public const EBIZCHARGE_SOAP_WSDL_3DSECURE_URL =
        'https://privatesoapapi1.ebizcharge.net/v2/wsdl/privatesoapapi1.wsdl';

    /**
     * WSDL Trace
     *
     * @const EBIZCHARGE_SOAP_WSDL_TRACE
     */
    public const EBIZCHARGE_SOAP_WSDL_TRACE = true;

    /**
     * WSDL Exceptions
     *
     * @const EBIZCHARGE_SOAP_WSDL_EXCEPTIONS
     */
    public const EBIZCHARGE_SOAP_WSDL_EXCEPTIONS = true;

    /**
     * SOAP WSDL Cache
     *
     * @const EBIZCHARGE_SOAP_WSDL_CACHE
     */
    public const EBIZCHARGE_SOAP_WSDL_CACHE = WSDL_CACHE_NONE;

    /**
     * SOAP WSDL Connection Timeout
     *
     * @const EBIZCHARGE_SOAP_WSDL_CONNECTION_TIMEOUT
     */
    public const EBIZCHARGE_SOAP_WSDL_CONNECTION_TIMEOUT = 180;

    /**
     * SOAP WSDL SSL verify Peer
     *
     * @const EBIZCHARGE_SOAP_WSDL_SSL_VERIFY_PEER
     */
    public const EBIZCHARGE_SOAP_WSDL_SSL_VERIFY_PEER = false;

    /**
     * SOAP WSDL Verify Peer Name
     *
     * @const EBIZCHARGE_SOAP_WSDL_SSL_VERIFY_PEER_NAME
     */
    public const EBIZCHARGE_SOAP_WSDL_SSL_VERIFY_PEER_NAME = false;

    /**
     * SOAP WSDL SSL Allow self Signed
     *
     * @const EBIZCHARGE_SOAP_WSDL_SSL_ALLOW_SELF_SIGNED
     */
    public const EBIZCHARGE_SOAP_WSDL_SSL_ALLOW_SELF_SIGNED = false;

    /**
     *
     * Transaction Type Credit
     *
     * @const: EBIZCHARGE_TRANSACTION_TYPE_CREDIT
     */
    public const EBIZCHARGE_TRANSACTION_TYPE_CREDIT = 'credit_card';

    /**
     * Transaction Type Sale
     *
     * @cosnt: EBIZCHARGE_TRANSACTION_TYPE_SALE
     */
    public const EBIZCHARGE_TRANSACTION_TYPE_SALE = 'sale';

    /**
     * Transaction Type Authonly
     *
     * @cosnt: EBIZCHARGE_TRANSACTION_TYPE_AUTH_ONLY
     */
    public const EBIZCHARGE_TRANSACTION_TYPE_AUTH_ONLY = 'authonly';

    /**
     * Ebizcharge Field Type
     *
     * @const EBIZCHARGE_DEFAULT_FIELD_TYPE
     */
    public const EBIZCHARGE_DEFAULT_FIELD_TYPE = 'Software';

    /**
     * Ebizcharge Not Equal Operator
     *
     * @const EBIZCHARGE_DEFAULT_EQUAL_NOT_OPERATOR
     */
    public const EBIZCHARGE_DEFAULT_EQUAL_NOT_OPERATOR = 'notequal';

    /**
     * Ebizcharge Equal Operator
     *
     * @const EBIZCHARGE_DEFAULT_EQUAL_OPERATOR
     */
    public const EBIZCHARGE_DEFAULT_EQUAL_OPERATOR = 'equal';

    /**
     * Ebizcharge Request Limit
     *
     * @const EBIZCHARGE_DEFAULT_REQUEST_MAX_LIMIT
     */
    public const EBIZCHARGE_DEFAULT_REQUEST_MAX_LIMIT = 990;

    /**
     * Ebizcharge Request Limit
     *
     * @const EBIZCHARGE_DEFAULT_REQUEST_MAX_PAYMENTS_LIMIT
     */
    public const EBIZCHARGE_DEFAULT_REQUEST_MAX_PAYMENTS_LIMIT = 990;

    /**
     * Ebizcharge Default Request Limit
     *
     * @const: EBIZCHARGE_DEFAULT_REQUEST_LIMIT
     */
    public const EBIZCHARGE_DEFAULT_REQUEST_LIMIT = 990;

    /**
     * Request Start Limit
     *
     * @const EBIZCHARGE_DEFAULT_REQUEST_START_LIMIT
     */
    public const EBIZCHARGE_DEFAULT_REQUEST_START_LIMIT = 0;

    /**
     * Ebizcharge Default Request Previouse Range
     *
     * @const EBIZCHARGE_DEFAULT_REQUEST_PREVIOUSE_YEAR_RANGE
     */
    public const EBIZCHARGE_DEFAULT_REQUEST_PREVIOUSE_YEAR_RANGE = 5;

    /**
     * Ebizcharge Sort Column
     *
     * @const EBIZCHARGE_DEFAULT_SORT_COLUMN
     */
    public const EBIZCHARGE_DEFAULT_SORT_COLUMN = 'CustomerId';

    /**
     *  Default Include Items
     *
     * @const EBIZCHARGE_DEFAULT_INCLUDE_ITEMS
     */
    public const EBIZCHARGE_DEFAULT_INCLUDE_ITEMS = 1;

    /**
     *  Ebizcharge Default Max Size
     *
     * @const EBIZCHARGE_DEFAULT_MAX_SIZE
     */
    public const EBIZCHARGE_DEFAULT_MAX_SIZE = 0;

    /**
     * Ebizcharge Default Position
     *
     * @const EBIZCHARGE_DEFAULT_POSITION
     */
    public const EBIZCHARGE_DEFAULT_POSITION = 0;

    /**
     * Payment Account Type Credit Card
     *
     * @const: EBIZCHARGE_PAYMENT_ACCOUNT_TYPE_CREDIT_CARD
     */
    public const EBIZCHARGE_PAYMENT_ACCOUNT_TYPE_CREDIT_CARD = 'cc';

    /**
     * Credit Card
     *
     * @const: BIZCHARGE_PAYMENT_ACCOUNT_TYPE_CREDIT_CARD_SAVED
     */
    public const BIZCHARGE_PAYMENT_ACCOUNT_TYPE_CREDIT_CARD_SAVED = 'credit_card';

    /**
     * Payment Account Type ACH
     *
     * @const: EBIZCHARGE_PAYMENT_ACCOUNT_TYPE_ACH
     */
    public const EBIZCHARGE_PAYMENT_ACCOUNT_TYPE_ACH = 'check';

    /**
     * Payment ACH Type Checking
     *
     * @const: EBIZCHARGE_PAYMENT_ACH_TYPE_CHECKING
     */
    public const EBIZCHARGE_PAYMENT_ACH_TYPE_CHECKING = 'checking';

    /**
     * Payment Type Savings
     *
     * @const: EBIZCHARGE_PAYMENT_ACH_TYPE_SAVINGS
     */
    public const EBIZCHARGE_PAYMENT_ACH_TYPE_SAVINGS = 'savings';

    /**
     * Ebizcharge Sync Assets Download Customers
     *
     * @const: EBIZCHARGE_SYNC_ASSETS_DOWNLAOD_CUSTOMERS
     */
    public const EBIZCHARGE_SYNC_ASSETS_DOWNLAOD_CUSTOMERS = 'customers';

    /**
     * Ebizcharge Sync Assets Download Products
     *
     * @const: EBIZCHARGE_SYNC_ASSETS_DOWNLAOD_PRODUCTS
     */
    public const EBIZCHARGE_SYNC_ASSETS_DOWNLAOD_PRODUCTS = 'products';

    /**
     * Ebizcharge Sync Assets Download Orders
     *
     * @const: EBIZCHARGE_SYNC_ASSETS_DOWNLAOD_ORDERS
     */
    public const EBIZCHARGE_SYNC_ASSETS_DOWNLAOD_ORDERS = 'orders';

    /**
     * Ebizcharge Sync Assets Upload Customers
     *
     * @const: EBIZCHARGE_SYNC_ASSETS_UPLOAD_CUSTOMERS
     */
    public const EBIZCHARGE_SYNC_ASSETS_UPLOAD_CUSTOMERS = 'customers';

    /**
     * Ebizcharge Sync Assets Upload Products
     *
     * @const: EBIZCHARGE_SYNC_ASSETS_UPLOAD_PRODUCTS
     */
    public const EBIZCHARGE_SYNC_ASSETS_UPLOAD_PRODUCTS = 'products';

    /**
     * Ebizcharge Sync Assets Upload Orders
     *
     * @const: EBIZCHARGE_SYNC_ASSETS_UPLOAD_ORDERS
     */
    public const EBIZCHARGE_SYNC_ASSETS_UPLOAD_ORDERS = 'orders';

    /**
     * Is EMV Enabled
     */
    public const EBIZCHARGE_MERCHANT_DATA_IS_EMV_ENABLED = "IsEMVEnabled";

    /**
     * Enable Avs Warnings
     */
    public const EBIZCHARGE_MERCHANT_DATA_AVS_WARNINGS_ENABLED = "EnableAVSWarnings";

    /**
     * CVV Warninggs
     */
    public const EBIZCHARGE_MERCHANT_DATA_CVV_WARNINGS_ENABLED = "EnableCVVWarnings";

    /**
     * Use Full Amount for Avs
     */
    public const EBIZCHARGE_MERCHANT_DATA_USE_FULL_AMOUNT_FOR_AVS = "UseFullAmountForAVS";

    /**
     * Decline Transaction if AVS warnings
     */
    public const EBIZCHARGE_MERCHANT_DATA_DECLINE_TRANSACTION_AVS_WARNINGS_DISABLED =
        "DeclineTransactionIfAVSWarningsAreDisabled";

    /**
     * verify credit card before Saving
     */
    public const EBIZCHARGE_MERCHANT_DATA_VERIFIY_CREDIT_CARD_BEFORE_SAVING = "VerifyCreditCardBeforeSaving";

    /**
     * Allow AcH Payments
     */
    public const EBIZCHARGE_MERCHANT_DATA_ALLOW_ACH_PAYMENTS = "AllowACHPayments";

    /**
     * Allow Credit Card Payments
     */
    public const EBIZCHARGE_MERCHANT_DATA_ALLOW_CREDIT_CARD_PAYMENTS = "AllowCreditCardPayments";

    /**
     * EbizCharge API Response Column Is 3DSecure Enabled
     *
     * @const EBIZ_IS_3DSECURE_ENABLED
     */
    public const EBIZ_IS_3DSECURE_ENABLED = 'is3DSecureEnabled';

    /**
     * EbizCharge API Response Column Is 3DSecure Test Mode
     *
     * @const EBIZ_IS_3DSECURE_TEST_MODE
     */
    public const EBIZ_IS_3DSECURE_TEST_MODE = 'is3DSecureTestMode';

    /**
     * EbizCharge API Response Column Bypass 3DS3 Error
     *
     * @const EBIZ_3DSECURE_BYPASS_ON_ERROR
     */
    public const EBIZ_3DSECURE_BYPASS_ON_ERROR = 'is3DSecureBypassOnError';
}

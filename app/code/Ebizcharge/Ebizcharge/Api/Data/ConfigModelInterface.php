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
 * Interface ConfigModelInterface
 *
 * Configuration Model Data Interface
 */
interface ConfigModelInterface
{


    /**
     * Get country path
     *
     * @const TRANSACTIONAL_MERCHANT_OWNER_EMAIL
     */
    public const TRANSACTIONAL_MERCHANT_OWNER_EMAIL = 'trans_email/ident_general/email';
    /**
     * Get country path
     *
     * @const COUNTRY_CODE_PATH
     */
    public const COUNTRY_CODE_PATH = 'general/country/default';

    /**
     * Get Store Information Name
     *
     * @const STORE_INFORMATION_NAME
     */
    public const STORE_INFORMATION_NAME = 'general/store_information/name';

    /**
     * Get Store Information Phone
     *
     * @const STORE_INFORMATION_PHONE
     */
    public const STORE_INFORMATION_PHONE = 'general/store_information/phone';

    /**
     * Get Store Information Hours
     *
     * @const STORE_INFORMATION_HOURS
     */
    public const STORE_INFORMATION_HOURS = 'general/store_information/hours';

    /**
     * Get Store Country Id
     *
     * @const STORE_INFORMATION_COUNTRY_ID
     */
    public const STORE_INFORMATION_COUNTRY_ID = 'general/store_information/country_id';

    /**
     * Get Store Region Id
     *
     * @const STORE_INFORMATION_REGION_ID
     */
    public const STORE_INFORMATION_REGION_ID = 'general/store_information/region_id';

    /**
     * Get Store Post code
     *
     * @const STORE_INFORMATION_POSTCODE
     */
    public const STORE_INFORMATION_POSTCODE = 'general/store_information/postcode';

    /**
     * Get Store Information City
     *
     * @const STORE_INFORMATION_CITY
     */
    public const STORE_INFORMATION_CITY = 'general/store_information/city';

    /**
     *  System config Void Order Edit
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_VOID_ORDER_EDIT
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_VOID_ORDER_EDIT = "payment/ebizcharge_ebizcharge/void_order_edit";

    /**
     * Get Store Information STREET LINE
     *
     * @const STORE_INFORMATION_STREET_LINE1
     */
    public const STORE_INFORMATION_STREET_LINE1 = 'general/store_information/street_line1';

    /**
     * Get Store Information Merchant Vat Number
     *
     * @const STORE_INFORMATION_MERCHANT_VAT_NUMBER
     */
    public const STORE_INFORMATION_MERCHANT_VAT_NUMBER = 'general/store_information/merchant_vat_number';

    /**
     * System Config Payment Gateway is Active
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_ACTIVE
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_ACTIVE = "payment/ebizcharge_ebizcharge/active";

    /**
     * System Config Title
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_TITLE
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_TITLE = "payment/ebizcharge_ebizcharge/title";

    /**
     * System Config Api Key
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_API_KEY
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_API_KEY = "payment/ebizcharge_ebizcharge/sourcekey";

    /**
     * System Config Api ID
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_API_ID
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_API_ID = "payment/ebizcharge_ebizcharge/sourceid";

    /**
     * System Config Api Password
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_API_PASSWORD
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_API_PASSWORD = "payment/ebizcharge_ebizcharge/sourcepin";

    /**
     * System Config EBizCharge PCI Compliance Enabled
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_PCI_COMPLIANCE_ENABLE
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_PCI_COMPLIANCE_ENABLE = "payment/ebizcharge_ebizcharge/pci_compliance_enable";

    /**
     *
     * @const SYSTEM_CONFIG_RECAPTCHA_TYPE_FOR_PLACE_ORDER
     */
    public const SYSTEM_CONFIG_GOOGLE_RECAPTCHA_TYPE_FOR_PLACE_ORDER = "recaptcha_frontend/type_for/place_order";

    /**
     *
     * @const SYSTEM_CONFIG_GOOGLE_RECAPTCHA_TYPE_FOR_COUPON_CODE
     */
    public const SYSTEM_CONFIG_GOOGLE_RECAPTCHA_TYPE_FOR_COUPON_CODE = "recaptcha_frontend/type_for/coupon_code";
    /**
     *
     * @const SYSTEM_CONFIG_GOOGLE_RECAPTCHA_V2_SITE_KEY
     */
    public const SYSTEM_CONFIG_GOOGLE_RECAPTCHA_V2_SITE_KEY = "recaptcha_frontend/type_recaptcha/public_key";
    /**
     *
     * @const SYSTEM_CONFIG_GOOGLE_RECAPTCHA_V2_SITE_SECRET
     */
    public const SYSTEM_CONFIG_GOOGLE_RECAPTCHA_V2_SITE_SECRET = "recaptcha_frontend/type_recaptcha/public_key";
    /**
     *
     * @const SYSTEM_CONFIG_GOOGLE_RECAPTCHA_V3_SITE_KEY
     */
    public const SYSTEM_CONFIG_GOOGLE_RECAPTCHA_V3_SITE_KEY = "recaptcha_frontend/type_recaptcha_v3/public_key";
    /**
     *
     * @const SYSTEM_CONFIG_GOOGLE_RECAPTCHA_V3_SITE_SECRET
     */
    public const SYSTEM_CONFIG_GOOGLE_RECAPTCHA_V3_SITE_SECRET = "recaptcha_frontend/type_recaptcha_v3/private_key";

    /**
     * @const GOOGLE_RECAPTCHA_VERIFY_URL
     */
    public const GOOGLE_RECAPTCHA_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * @const SYSTEM_EBIZCHARGE_ENCRYPT_KEY_POSTFIX
     */
    public const SYSTEM_EBIZCHARGE_ENCRYPT_KEY_POSTFIX = ':exTdyfDe';


    /**
     * System Config EBizCharge PCI Compliance action gateway URL
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_PCI_COMPLIANCE_ACTION_GATEWAY_URL
     */
    //const SYSTEM_CONFIG_EBIZCHARGE_PCI_COMPLIANCE_ACTION_GATEWAY_URL =
    // "payment/ebizcharge_ebizcharge/pci_compliance_action_url";

    /**
     * System Config EBizCharge AVS Full Payment Enabled
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_AVS_FULL_PAYMENT_ENABLE
     */
    // public const SYSTEM_CONFIG_EBIZCHARGE_AVS_FULL_PAYMENT_ENABLE =
    // "payment/ebizcharge_ebizcharge/avs_full_payment";

    /**
     * DEFAULT EBizCharge PCI Compliance action gateway URL
     *
     * @const SYSTEM_CONFIG_DEFAULT_EBIZCHARGE_PCI_COMPLIANCE_ACTION_GATEWAY_URL
     */
    public const SYSTEM_CONFIG_DEFAULT_EBIZCHARGE_PCI_COMPLIANCE_ACTION_GATEWAY_URL =
        'https://soap.ebizcharge.net/eBizService.svc?wsdl';

    /**
     * System Config Envoirnment Prefix
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_ENVOIRNMENT_PREFIX
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_ENVOIRNMENT_PREFIX = "payment/ebizcharge_ebizcharge/envoirnment_prefix";

    /**
     * System Config Division ID
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_DIVISION_ID
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_DIVISION_ID = "payment/ebizcharge_ebizcharge/division_id";

    /**
     * System Config Select Web Hosted Payment Form Type
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_PAYMENT_FORM_TYPE
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_PAYMENT_FORM_TYPE = "payment/ebizcharge_ebizcharge/payment_form_type";

    /**
     * System Config Select Tokenize Cards Only
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_TOKENIZE_CARDS_ONLY
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_TOKENIZE_CARDS_ONLY = "payment/ebizcharge_ebizcharge/tokenize_cards_only";


    /**
     * System Config ACH Enable
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_ACH_ENABLE
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_ACH_ENABLE = "payment/ebizcharge_ebizcharge/enableAch";

    /**
     * System Config Recurring Payment
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_RECURRING_PAYMENT
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_RECURRING_PAYMENT = "payment/ebizcharge_ebizcharge/recurring_payments";

    /**
     * Show Ebizcharge Saved Methods
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_SHOW_SAVED_METHODS
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_SHOW_SAVED_METHODS = "payment/ebizcharge_ebizcharge/show_saved_methods";

    /**
     * System Config Recurring Frequency
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_RECURRING_FREQUENCY
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_RECURRING_FREQUENCY = "payment/ebizcharge_ebizcharge/recurring_frequency";

    /**
     * System Config Card Code Admin
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_CARD_CODE_ADMIN
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_CARD_CODE_ADMIN = "payment/ebizcharge_ebizcharge/request_card_code_admin";

    /**
     * System Config Indefinit Recurring
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_INDEFINITE_RECURRING
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_INDEFINITE_RECURRING = "payment/ebizcharge_ebizcharge/indefinite_recurring";

    /**
     * System Config Description
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_DESCRIPTION
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_DESCRIPTION = "payment/ebizcharge_ebizcharge/description";

    /**
     * System Config custreceipt
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_CUSTOMER_RECEIPT
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_CUSTOMER_RECEIPT = "payment/ebizcharge_ebizcharge/custreceipt";

    /**
     * System Config custreceipt_template
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_CUSTOMER_RECEIPT_TEMPLATE
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_CUSTOMER_RECEIPT_TEMPLATE =
        "payment/ebizcharge_ebizcharge/custreceipt_template";

    /**
     * System Config order_status
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_ORDER_STATUS
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_ORDER_STATUS = "payment/ebizcharge_ebizcharge/order_status";

    /**
     * System Config inline card action
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_CARD_INLINE_ACTION
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_CARD_INLINE_ACTION = "ebizcharge/cards/inlineaction";

    /**
     * System Config Payment Action
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_PAYMENT_ACTION
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_PAYMENT_ACTION = "payment/ebizcharge_ebizcharge/payment_action";

    /**
     * System Config Sort Order
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_SORT_ORDER
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_SORT_ORDER = "payment/ebizcharge_ebizcharge/sort_order";

    /**
     * System Config Low Stock Email Template
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_LOW_STOCK_EMAIL_TEMPLATE
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_LOW_STOCK_EMAIL_TEMPLATE = "payment/ebizcharge_ebizcharge/low_stock_email";

    /**
     * System Config CCTypes
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_CCTYPES
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_CCTYPES = "payment/ebizcharge_ebizcharge/cctypes";

    /**
     * System Config Allow Specific
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_ALLOW_SPECIFIC
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_ALLOW_SPECIFIC = "payment/ebizcharge_ebizcharge/allowspecific";

    /**
     * System Config Specific Country
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_SPECIFIC_COUNTRY
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_SPECIFIC_COUNTRY = "payment/ebizcharge_ebizcharge/specificcountry";

    /**
     * Sytem Config Minimum Order Total
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_MIN_ORDER_TOTAL
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_MIN_ORDER_TOTAL = "payment/ebizcharge_ebizcharge/min_order_total";

    /**
     * System Config Max Order Total
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_MAX_ORDER_TOTAL
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_MAX_ORDER_TOTAL = "payment/ebizcharge_ebizcharge/max_order_total";

    /**
     * System Config Save Payment
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_SAVE_PAYMENT
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_SAVE_PAYMENT = "payment/ebizcharge_ebizcharge/save_payment";

    /**
     * Is Same Billing & Shipping Addresses at Checkout
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_IS_SAME_BILLING_SHIPPING_ADDRESSES
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_IS_SAME_BILLING_SHIPPING_ADDRESSES = "payment/ebizcharge_ebizcharge/checkout_same_billing_shipping";


    /**
     * System Config Error Message
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_ERROR_MSG
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_ERROR_MSG = "payment/ebizcharge_ebizcharge/error_msg";

    /**
     * System Config Items Sync Settings
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_ITEMS_SYNC_SETTINGS
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_ITEMS_SYNC_SETTINGS = "payment/ebizcharge_ebizcharge/itemssyncsetting";

    /**
     * System Config Upload Connect
     *
     * @const: SYSTEM_CONFIG_EBIZCHARGE_UPLOAD_CONNECT
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_UPLOAD_CONNECT = "payment/ebizcharge_ebizcharge/uploadeconnect";

    /**
     * System Config Sync Customers
     *
     * @const: SYSTEM_CONFIG_EBIZCHARGE_SYNC_CUSTOMERS
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_SYNC_CUSTOMERS = "payment/ebizcharge_ebizcharge/synccustomers";

    /**
     * System Config Sync Items
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_SYNC_ITEMS
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_SYNC_ITEMS = "payment/ebizcharge_ebizcharge/syncitems";

    /**
     * System Config Sync Orders
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_SYNC_ORDERS
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_SYNC_ORDERS = "payment/ebizcharge_ebizcharge/syncorders";

    /**
     * System Config Sync Invoices
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_SYNC_INVOICES
     */
    //const SYSTEM_CONFIG_EBIZCHARGE_SYNC_INVOICES = "payment/ebizcharge_ebizcharge/syncinvoices";

    /**
     * System Config Download Connect
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_DOWNLOAD_CONNECT
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_DOWNLOAD_CONNECT = "payment/ebizcharge_ebizcharge/downloadeconnect";

    /**
     * System Config Shipping Method
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_SHIPPING_METHOD
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_SHIPPING_METHOD = "payment/ebizcharge_ebizcharge/shippingmethod";

    /**
     * System Config Download Customers
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_DOWNLOAD_CUSTOMERS
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_DOWNLOAD_CUSTOMERS = "payment/ebizcharge_ebizcharge/downloadcustomers";

    /**
     * System Config Download Items
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_DOWNLOAD_ITEMS
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_DOWNLOAD_ITEMS = "payment/ebizcharge_ebizcharge/downloaditems";

    /**
     * System Config Download Orders
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_DOWNLOAD_ORDERS
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_DOWNLOAD_ORDERS = "payment/ebizcharge_ebizcharge/downloadorders";

    /**
     * System Config Download Orders
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_DOWNLOAD_INVOICES
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_DOWNLOAD_INVOICES = "payment/ebizcharge_ebizcharge/downloadinvoices";

    /**
     * System Config Upload Customers
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_UPLOAD_CUSTOMERS
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_UPLOAD_CUSTOMERS = "payment/ebizcharge_ebizcharge/synccustomers";

    /**
     * System Config Upload Items
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_UPLOAD_ITEMS
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_UPLOAD_ITEMS = "payment/ebizcharge_ebizcharge/syncitems";

    /**
     * System Config Upload Orders
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_UPLOAD_ORDERS
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_UPLOAD_ORDERS = "payment/ebizcharge_ebizcharge/syncorders";

    /**
     * System Config Upload Invoices
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_UPLOAD_INVOICES
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_UPLOAD_INVOICES = "payment/ebizcharge_ebizcharge/syninvoices";

    /**
     * System Config Pay Later
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_PAY_LATER
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_PAY_LATER = "payment/ebizcharge_ebizcharge/paylater";

    /**
     * System Config Enable Logs
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_ENABLE_LOGS
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_ENABLE_LOGS = "payment/ebizcharge_ebizcharge/enable_logs";

    /**
     * System Config Logs File
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_LOGS_FILE
     */
    //const SYSTEM_CONFIG_EBIZCHARGE_LOGS_FILE = "payment/ebizcharge_ebizcharge/logs_file";

    /**
     * System Config enable_card
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_ENABLE_CARD
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_ENABLE_CARD = "payment/ebizcharge_ebizcharge/enable_card";

    /**
     * System Config Enable AVS CVV Zipcode
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_ENABLE_AVS_CVV_ZIPCODE
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_ENABLE_AVS_CVV_ZIPCODE = "payment/ebizcharge_ebizcharge/enable_avs_cvv";

    /**
     * System Config Save Card
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_SAVE_CARD
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_SAVE_CARD = "payment/ebizcharge_ebizcharge/save_card";

    /**
     * System Config Save Bank Accounts
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_SAVE_BANK_ACCOUNTS
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_SAVE_BANK_ACCOUNTS = "payment/ebizcharge_ebizcharge/save_bank_accounts";

    /**
     * System Config Surcharge Enabled/Disabled
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_SURCHARGE_ENABLED
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_SURCHARGE_ENABLED = "payment/ebizcharge_ebizcharge/enable_surcharge";

    /**
     * System Config New Order Status
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_NEW_ORDER_STATUS
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_NEW_ORDER_STATUS = "payment/ebizcharge_ebizcharge/order_status";

    /**
     * System Config Unit of Measure
     *
     * @const SYSTEM_CONFIG_UNIT_OF_MEASURE
     */
    public const SYSTEM_CONFIG_UNIT_OF_MEASURE = "general/locale/weight_unit";


    /**
     * System Config Enable Gateway Email for orders
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_CUSTOMER_ENABLE_GATEWAY_EMAIL
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_CUSTOMER_ENABLE_GATEWAY_EMAIL =
        "payment/ebizcharge_ebizcharge/enable_gateway_email";

    /**
     * System Config Enable Merchant Gateway Email for orders
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_MERCHANT_ENABLE_GATEWAY_EMAIL
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_MERCHANT_ENABLE_GATEWAY_EMAIL =
        "payment/ebizcharge_ebizcharge/enable_merchant_gateway_email";


    /**
     * System Config Email Templates
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_CUSTOMER_RECEIPT_EMAIL_TEMPLATE
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_CUSTOMER_RECEIPT_EMAIL_TEMPLATE =
        "payment/ebizcharge_ebizcharge/emailtemplates";

    /**
     * System Config Weight Unit
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_WEIGHT_UNIT
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_WEIGHT_UNIT = 'payment/ebizcharge_ebizcharge/unit_of_measure';

    /**
     * Save Guest Users
     *
     * @const SYSTEM_CONFIG_EBIZCHARGE_SAVE_GUEST_USERS
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_SAVE_GUEST_USERS = 'payment/ebizcharge_ebizcharge/save_guest_users';

    /**
     * Key Active
     *
     * @const KEY_ACTIVE
     */
    public const KEY_ACTIVE = 'active';

    /**
     * Transaction Response Type Accepted
     *
     * @const: TRANSACTION_RESPONSE_TYPE_ACCEPTED
     */
    public const TRANSACTION_RESPONSE_TYPE_ACCEPTED = 'Accepted';

    /**
     * Transaction Response Type Declined
     *
     * @const: TRANSACTION_RESPONSE_TYPE_DECLINED
     */
    public const TRANSACTION_RESPONSE_TYPE_DECLINED = 'Declined';

    /**
     * Transaction Response Type Rejected
     *
     * @const: TRANSACTION_RESPONSE_TYPE_REJECTED
     */
    public const TRANSACTION_RESPONSE_TYPE_REJECTED = 'Rejected';
    /**
     * @const TRANSACTION_RESPONSE_TYPE_ERROR
     */
    public const TRANSACTION_RESPONSE_TYPE_ERROR = 'Error';

    /**
     * American Express
     *
     * @const: CREDIT_CARD_TYPE_AMERICAN_EXPRESS
     */
    public const CREDIT_CARD_TYPE_AMERICAN_EXPRESS = 'American Express';

    /**
     * Master Card
     *
     * @const: CREDIT_CARD_TYPE_MASTER_CARD
     */
    public const CREDIT_CARD_TYPE_MASTER_CARD = 'Master Card';

    /**
     * Visa Card
     *
     * @const: CREDIT_CARD_TYPE_VISA_CARD
     */
    public const CREDIT_CARD_TYPE_VISA_CARD = 'VISA';

    /**
     *  Discover Card
     *
     * @const: CREDIT_CARD_TYPE_DISCOVER_CARD
     */
    public const CREDIT_CARD_TYPE_DISCOVER_CARD = 'Discover Card';

    /**
     * Bank of America
     *
     * @const: CREDIT_CARD_TYPE_BANK_OF_AMERICA
     */
    public const CREDIT_CARD_TYPE_BANK_OF_AMERICA = 'Bank Of America';

    /**
     * Citi Card
     *
     * @const: CREDIT_CARD_TYPE_CITI_CARD
     */
    public const CREDIT_CARD_TYPE_CITI_CARD = 'Citi Card';

    /**
     * Not Available
     *
     * @const: NOT_AVAILABLE
     */
    public const NOT_AVAILABLE = '*';

    /**
     * Citi Card
     *
     * @const: CREDIT_CARD_TYPE_CITI_CARD_PREFIX
     */
    public const CREDIT_CARD_TYPE_CITI_CARD_PREFIX = 'CI';

    /**
     * @const CREDIT_CARD_TYPE_AMERICAN_EXPRESS_PREFIX
     */
    public const CREDIT_CARD_TYPE_AMERICAN_EXPRESS_PREFIX = 'AE';

    /**
     *
     * @const CREDIT_CARD_TYPE_MASTER_CARD_PREFIX
     */
    public const CREDIT_CARD_TYPE_MASTER_CARD_PREFIX = 'MC';

    /**
     * @const CREDIT_CARD_TYPE_VISA_CARD_PREFIX
     */
    public const CREDIT_CARD_TYPE_VISA_CARD_PREFIX = 'VI';

    /**
     * @const CREDIT_CARD_TYPE_DISCOVER_CARD_PREFIX
     */
    public const CREDIT_CARD_TYPE_DISCOVER_CARD_PREFIX = 'DI';

    /**
     * @const CREDIT_CARD_TYPE_BANK_OF_AMERICA_PREFIX
     */
    public const CREDIT_CARD_TYPE_BANK_OF_AMERICA_PREFIX = 'JCB';

    /**
     * @const CREDIT_CARD_TYPE_JCB_PREFIX
     */
    public const CREDIT_CARD_TYPE_JCB_PREFIX = 'JCB';

    /**
     * @const CREDIT_CARD_TYPE_OTHER_PREFIX
     */
    public const CREDIT_CARD_TYPE_OTHER_PREFIX = 'OT';

    /**
     * Citi Card
     *
     * @const: CREDIT_CARD_TYPE_CITI_CARD_MAGE_PREFIX
     */
    public const CREDIT_CARD_TYPE_CITI_CARD_MAGE_PREFIX = 'CI';

    /**
     * @const CREDIT_CARD_TYPE_AMERICAN_MAGE_EXPRESS_PREFIX
     */
    public const CREDIT_CARD_TYPE_AMERICAN_EXPRESS_MAGE_PREFIX = 'AE';

    /**
     *
     * @const CREDIT_CARD_TYPE_MASTER_CARD_MAGE_PREFIX
     */
    public const CREDIT_CARD_TYPE_MASTER_CARD_MAGE_PREFIX = 'MC';

    /**
     * @const CREDIT_CARD_TYPE_VISA_CARD_MAGE_PREFIX
     */
    public const CREDIT_CARD_TYPE_VISA_CARD_MAGE_PREFIX = 'VI';

    /**
     * @const CREDIT_CARD_TYPE_DISCOVER_CARD_MAGE_PREFIX
     */
    public const CREDIT_CARD_TYPE_DISCOVER_CARD_MAGE_PREFIX = 'DI';

    /**
     * @const CREDIT_CARD_TYPE_BANK_OF_AMERICA_MAGE_PREFIX
     */
    public const CREDIT_CARD_TYPE_BANK_OF_AMERICA_MAGE_PREFIX = 'BA';


    /**
     * Citi Card
     *
     * @const: CREDIT_CARD_TYPE_CITI_CARD_SHORT_PREFIX
     */
    public const CREDIT_CARD_TYPE_CITI_CARD_SHORT_PREFIX = 'C';

    /**
     * @const CREDIT_CARD_TYPE_AMERICAN_EXPRESS_SHORT_PREFIX
     */
    public const CREDIT_CARD_TYPE_AMERICAN_EXPRESS_SHORT_PREFIX = 'A';

    /**
     *
     * @const CREDIT_CARD_TYPE_MASTER_CARD_SHORT_PREFIX
     */
    public const CREDIT_CARD_TYPE_MASTER_CARD_SHORT_PREFIX = 'M';

    /**
     * @const CREDIT_CARD_TYPE_VISA_CARD_SHORT_PREFIX
     */
    public const CREDIT_CARD_TYPE_VISA_CARD_SHORT_PREFIX = 'V';

    /**
     * @const CREDIT_CARD_TYPE_DISCOVER_CARD_SHORT_PREFIX
     */
    public const CREDIT_CARD_TYPE_DISCOVER_CARD_SHORT_PREFIX = 'D';

    /**
     * @const CREDIT_CARD_TYPE_BANK_OF_AMERICA_SHORT_PREFIX
     */
    public const CREDIT_CARD_TYPE_BANK_OF_AMERICA_SHORT_PREFIX = 'JCB';


}

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

namespace Ebizcharge\Ebizcharge\Api\Data\GraphQL;

/**
 * Interface GraphQLInterface
 *
 * GraphQL Data Interface
 */
interface GraphQLInterface
{
    /**
     * Cart Id
     *
     * @const: GRAPHQL_WEBFORM_INPUT_PARAMS_CART_ID
     */
    public const GRAPHQL_WEBFORM_INPUT_PARAMS_CART_ID = 'cart_id';

    /**
     * Web form Approved URL
     *
     * @const: GRAPHQL_WEBFORM_INPUT_PARAMS_APPROVED_URL
     */
    public const GRAPHQL_WEBFORM_INPUT_PARAMS_APPROVED_URL = 'approved_url';

    /**
     * Declined URL
     *
     * @const: GRAPHQL_WEBFORM_INPUT_PARAMS_DECLINED_URL
     */
    public const GRAPHQL_WEBFORM_INPUT_PARAMS_DECLINED_URL = 'declined_url';

    /**
     * Error URL
     *
     * @const: GRAPHQL_WEBFORM_INPUT_PARAMS_ERROR_URL
     */
    public const GRAPHQL_WEBFORM_INPUT_PARAMS_ERROR_URL = 'error_url';

    /**
     * Form Type
     *
     * @const: GRAPHQL_WEBFORM_INPUT_PARAMS_FORM_TYPE
     */
    public const GRAPHQL_WEBFORM_INPUT_PARAMS_FORM_TYPE = 'form_type';

    /**
     * Email Sender
     *
     * @const: GRAPHQL_WEBFORM_INPUT_PARAMS_EMAIL_SENDER
     */
    public const GRAPHQL_WEBFORM_INPUT_PARAMS_EMAIL_SENDER = 'email_sender';

    /**
     * EBizCharge Form GraphQL Type
     *
     * @const: GRAPHQL_WEBFORM_INPUT_PAYMENT_PARAMS_FORM_TYPE
     */
    public const GRAPHQL_WEBFORM_INPUT_PAYMENT_PARAMS_FORM_TYPE = 'ebiz_webform';

    /**
     * Payment Internal Id
     * @const: GRAPHQL_WEBFORM_OUTPUT_PARAMS_PAYMENT_INTERNAL_ID
     */
    public const GRAPHQL_WEBFORM_OUTPUT_PARAMS_PAYMENT_INTERNAL_ID = 'payment_internal_id';

    /**
     * Webform URL
     *
     * @const: GRAPHQL_WEBFORM_OUTPUT_PARAMS_WEB_FORM_URL
     */
    public const GRAPHQL_WEBFORM_OUTPUT_PARAMS_WEB_FORM_URL = 'ebiz_hosted_pro_url';

    /**
     * Error Message
     * @const: GRAPHQL_WEBFORM_OUTPUT_PARAMS_ERROR_MESSAGE
     */
    public const GRAPHQL_WEBFORM_OUTPUT_PARAMS_ERROR_MESSAGE = 'error_message';

    /**
     *
     * Hosted Pro Cart Id
     * @GRAPHQL_HOST_PRO_INPUT_PARAMS_CART_ID
     */
    public const GRAPHQL_HOST_PRO_INPUT_PARAMS_CART_ID = 'cart_id';

    /**
     * Hosted Pro User Id
     * @GRAPHQL_HOST_PRO_INPUT_PARAMS_USER_ID
     */
    public const GRAPHQL_HOST_PRO_INPUT_PARAMS_USER_ID = 'user_id';

    /**
     * Desclined URL
     * @GRAPHQL_HOST_PRO_INPUT_PARAMS_DECLINED_URL
     */
    public const GRAPHQL_HOST_PRO_INPUT_PARAMS_DECLINED_URL = 'declined_url';

    /**
     * Success URL
     * @GRAPHQL_HOST_PRO_INPUT_PARAMS_SUCCESS_URL
     */
    public const GRAPHQL_HOST_PRO_INPUT_PARAMS_SUCCESS_URL = 'success_url';

    /**
     * Error URL
     * @GRAPHQL_HOST_PRO_INPUT_PARAMS_ERROR_URL
     */
    public const GRAPHQL_HOST_PRO_INPUT_PARAMS_ERROR_URL = 'error_url';

    /**
     * Redirect to URL
     * @const: GRAPHQL_WEBFORM_REDIRECT_TO_CALLBACK_URL_YES
     */
    public const GRAPHQL_WEBFORM_REDIRECT_TO_CALLBACK_URL_YES = 0;

    /**
     * Do not redirect to URL
     * @const: GRAPHQL_WEBFORM_REDIRECT_TO_CALLBACK_URL_NO
     */
    public const GRAPHQL_WEBFORM_REDIRECT_TO_CALLBACK_URL_NO = 1;

    /**
     *
     * @const: PAYMENT_EBZC_OPTION
     */
    public const PAYMENT_EBZC_OPTION = "ebzc_option";

    /**
     *
     * @const: PAYMENT_EBZC_OPTION_TYPE
     */
    public const PAYMENT_EBZC_OPTION_TYPE = "ebzc_option_type";

    /**
     * Customer Token
     *
     * @const: GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_TYPE_CUST_TOKEN
     */
    public const GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_TYPE_CUST_TOKEN = "CustToken";

    /**
     * PM Token
     *
     * @const: GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_TYPE_PM_TOKEN
     */
    public const GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_TYPE_PM_TOKEN = "PmToken";

    /**
     * Transaction Result Code
     *
     * @const: GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_TYPE_TRAN_RESULT_CODE
     */
    public const GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_TYPE_TRAN_RESULT_CODE = "TranResultCode";

    /**
     * Customer Token
     *
     * @const: GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_TYPE_TRAN_RESULT
     */
    public const GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_TYPE_TRAN_RESULT = "TranResult";

    /**
     * Transaction Reference Number
     *
     * @const: GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_TYPE_TRAN_REF_NUMBER
     */
    public const GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_TYPE_TRAN_REF_NUMBER = "TranRefNum";

    /**
     * Auth code
     *
     * @const: GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_TYPE_AUTH_CODE
     */
    public const GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_TYPE_AUTH_CODE = "AuthCode";

    /**
     * Auth Amount
     *
     * @const: GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_TYPE_AUTH_AMOUNT
     */
    public const GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_TYPE_AUTH_AMOUNT = "AuthAmount";

    /**
     * Masked CC
     *
     * @const: GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_TYPE_MASKED_CC
     */
    public const GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_TYPE_MASKED_CC = "MaskedCC";

    /**
     * CC Type
     *
     * @const: GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_TYPE_CC_TYPE
     */
    public const GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_TYPE_CC_TYPE = "CCType";

    /**
     *
     * @const: GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_TYPE_PAY_BY_TYPE
     */
    public const GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_TYPE_PAY_BY_TYPE = "PayByType";

    /**
     * Date Run
     *
     * @const: GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_TYPE_DATE_RUN
     */
    public const GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_TYPE_DATE_RUN = "DateRun";

    /**
     * TIME Run
     *
     * @const: GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_TYPE_TIME_RUN
     */
    public const GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_TYPE_TIME_RUN = "TimeRun";

    /**
     * Transaction Lookup Key
     *
     * @const: GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_TYPE_TRANSACTION_LOOKUP_KEY
     */
    public const GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_TYPE_TRANSACTION_LOOKUP_KEY = "TransactionLookupKey";

    /**
     *  Payment Internal Id
     *
     * @const: GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_TYPE_PAYMENT_INTERNAL_ID
     */
    public const GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_TYPE_PAYMENT_INTERNAL_ID = "PaymentInternalId";

    /**
     *
     * @const:  GRAPHQL_WEBFORM_TRANSACTION_PARAM_FORM_TYPE_WEBFORMS
     */
    public const GRAPHQL_WEBFORM_TRANSACTION_PARAM_FORM_TYPE_WEBFORMS = 'Webform';

    /**
     *
     * @const:  GRAPHQL_WEBFORM_TRANSACTION_PARAM_FORM_TYPE_PAYMENT_METHOD
     */
    public const GRAPHQL_WEBFORM_TRANSACTION_PARAM_FORM_TYPE_PAYMENT_METHOD = 'PmRequestForm';

    /**
     *
     * @const:  GRAPHQL_WEBFORM_TRANSACTION_PARAM_TYPE_FROM_EMAIL
     */
    public const GRAPHQL_WEBFORM_TRANSACTION_PARAM_TYPE_FROM_EMAIL = 'support@centurybizsolutions.net';

    /**
     *
     * @const:  GRAPHQL_WEBFORM_TRANSACTION_PARAM_TYPE_FROM_NAME
     */
    public const GRAPHQL_WEBFORM_TRANSACTION_PARAM_TYPE_FROM_NAME = 'EbizCharge Support Team';

    /**
     *
     * @const:  GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_PAYMENT_INTERNAL_ID
     */
    public const GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_PAYMENT_INTERNAL_ID = 'payment_internal_id';

    /**
     *
     * @const:  GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_EBIZ_HOSTED_PRO_URL
     */
    public const GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_EBIZ_HOSTED_PRO_URL = 'ebiz_hosted_pro_url';

    /**
     *
     * @const:  GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_ERROR_MESSAGE
     */
    public const GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_ERROR_MESSAGE = 'error_message';

    /**
     *
     * @GRAPHQL_HOSTED_TRANSACTION_RESPONSE_SECURE_PAYMENT_URL
     */
    public const GRAPHQL_HOSTED_TRANSACTION_RESPONSE_SECURE_PAYMENT_URL = "secure_payment_url";

    /**
     *
     * @GRAPHQL_HOSTED_TRANSACTION_RESPONSE_PAYMENT_MESSAGE
     */
    public const GRAPHQL_HOSTED_TRANSACTION_RESPONSE_PAYMENT_MESSAGE = "payment_message";
    /**
     *
     * @GRAPHQL_HOSTED_TRANSACTION_RESPONSE_PAYMENT_INTERNAL_ID
     */
    public const GRAPHQL_HOSTED_TRANSACTION_RESPONSE_PAYMENT_INTERNAL_ID = "secure_payment_internal_id";
}

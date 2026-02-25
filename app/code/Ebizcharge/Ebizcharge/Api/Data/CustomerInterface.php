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

use Magento\Customer\Api\Data\CustomerInterface as CoreCustomerInterface;

/**
 * Interface CustomerInterface
 *
 * Customer Data Interface
 */
interface CustomerInterface extends CoreCustomerInterface
{

    /**
     * @const EBIZCHARGE_DEFAULT_BILLING_ADDRESS_COUNTRY
     */
    public const EBIZCHARGE_DEFAULT_BILLING_ADDRESS_COUNTRY = "US";
    /**
     * @const EBIZCHARGE_DEFAULT_BILLING_ADDRESS_CITY
     */
    public const EBIZCHARGE_DEFAULT_BILLING_ADDRESS_CITY = "NY";
    /**
     * @const EBIZCHARGE_DEFAULT_BILLING_ADDRESS_STATE
     */
    public const EBIZCHARGE_DEFAULT_BILLING_ADDRESS_STATE = "NY";
    /**
     * @const EBIZCHARGE_DEFAULT_BILLING_ADDRESS_POSTALCODE
     */
    public const EBIZCHARGE_DEFAULT_BILLING_ADDRESS_POSTALCODE = "10022";
    /**
     * @const EBIZCHARGE_DEFAULT_BILLING_ADDRESS_ZIPCODE
     */
    public const EBIZCHARGE_DEFAULT_BILLING_ADDRESS_ZIPCODE = "10022";


    /**
     * Sync Response Status
     * @const EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS
     */
    public const EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS = "status";

    /**
     * Sync Response Status
     * @const EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS_FAILED
     */
    public const EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS_FAILED = "failed";
    /**
     * Sync Response Status
     * @const EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS_SUCCESS
     */
    public const EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS_SUCCESS = "success";
    /**
     * Sync Response Status
     * @const EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS_CODE
     */
    public const EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS_CODE = "status_code";

    /**
     * Sync Response Status
     * @const EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS_CODE_FAILED
     */
    public const EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS_CODE_FAILED = "000";

    /**
     * Sync Response Status
     * @const EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS_CODE_APPROVED
     */
    public const EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS_CODE_APPROVED = "111";

    /**
     * Sync Response Status
     * @const EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS_ERROR
     */
    public const EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS_ERROR = "error";

    /**
     * Sync Response Status
     * @const EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS_ERROR_CODE
     */
    public const EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS_ERROR_CODE = "error_code";

    /**
     *
     * Ebizcharge Customer Status
     *
     * @const: EBIZ_CUSTOMER_STATUS_ACTIVE
     */
    public const EBIZ_CUSTOMER_STATUS_ACTIVE = 1;

    /**
     *  Ebizcharge Customer Status Pending
     *
     * @const: EBIZ_CUSTOMER_STATUS_PENDING
     */
    public const EBIZ_CUSTOMER_STATUS_PENDING = 2;

    /**
     * customer Status In Active
     *
     * @cosnt: EBIZ_CUSTOMER_STATUS_UN_ACTIVE
     */
    public const EBIZ_CUSTOMER_STATUS_UN_ACTIVE = 2;

    /**
     * Guest Customer First Name
     *
     * @const: GUEST_CUSTOMER_FIRST_NAME
     */
    public const GUEST_CUSTOMER_FIRST_NAME = 'Magento';

    /**
     * Guest Type of Customer
     *
     * @const CUSTOMER_TYPE_GUEST
     */
    public const CUSTOMER_TYPE_GUEST = "Guest";

    /**
     * Guest Customer Email
     *
     * @const GUEST_CUSTOMER_EMAIL
     */
    public const GUEST_CUSTOMER_EMAIL = 'magentoguest@gmail.com';

    /**
     * Guest Customer Last Name
     *
     * GUEST_CUSTOMER_EMAIL
     */
    public const GUEST_CUSTOMER_LAST_NAME = 'Guest';

    /**
     * Default country
     *
     * @const GUEST_DEFAULT_COUNTRY
     */
    public const GUEST_DEFAULT_COUNTRY = 'US';

    /**
     * Deafult State
     *
     * @const GUEST_DEAFULT_STATE
     */
    public const GUEST_DEAFULT_STATE = 'claifornia';

    /**
     * Guest Default company
     *
     * @const GUEST_DEAFULT_COMPANY
     */
    public const GUEST_DEAFULT_COMPANY = 'CBS';

    /**
     * Default Eail Template
     *
     * @DEFAULT_EMAIL_TEMPLATE
     */
    public const DEFAULT_EMAIL_TEMPLATE = 'TransactionReceiptCustomer';

    /**
     * Entity Id
     *
     * @const EBIZCHARGE_CUSTOMER_ENTITY_ID
     */
    public const EBIZCHARGE_CUSTOMER_ENTITY_ID = 'entity_id';

    /**
     * Ebizcharge Customer Sync Status
     *
     * @const EBIZCHARGE_CUSTOMER_SYNC_STATUS
     */
    public const EBIZCHARGE_CUSTOMER_SYNC_STATUS = 'ec_cust_sync_status';

    /**
     * EBizcharge Customer Interal Id
     *
     * @const EBIZCHARGE_CUSTOMER_INTERNAL_ID
     */
    public const EBIZCHARGE_CUSTOMER_INTERNAL_ID = 'ec_cust_internalid';

    /**
     * Ebizcharge Customer Id
     *
     * @const EBIZCHARGE_CUSTOMER_ID
     */
    public const EBIZCHARGE_CUSTOMER_ID = 'ec_cust_id';

    /**
     * Ebizcharge Customer Token
     *
     * @const EBIZCHARGE_CUSTOMER_TOKEN
     */
    public const EBIZCHARGE_CUSTOMER_TOKEN = 'ec_cust_token';

    /**
     * Ebizcharge Customer Last Sync Date
     *
     * @const EBIZCHARGE_CUSTOMER_LAST_SYNC_DATE
     */
    public const EBIZCHARGE_CUSTOMER_LAST_SYNC_DATE = 'ec_cust_lastsyncdate';

    /**
     * Ebizcharge Software Id
     *
     * @const: EBIZCHARGE_SOFTWARE_ID
     */
    public const EBIZCHARGE_SOFTWARE_ID = 'ec_software_id';

    /**
     * Ebizcharge Division Id
     *
     * @const: EBIZCHARGE_DIVISION_ID
     */
    public const EBIZCHARGE_DIVISION_ID = 'ec_division_id';

    /**
     * @const EBIZCHARGE_CUSTOMER_ACTIONS_FORGOT_PASSWORD_ACTION
     */
    public const EBIZCHARGE_CUSTOMER_ACTIONS_FORGOT_PASSWORD_ACTION = "forgotpassword";
    /**
     * @const EBIZCHARGE_CUSTOMER_ACTIONS_FORGOT_PASSWORD_POST_ACTION
     */
    public const  EBIZCHARGE_CUSTOMER_ACTIONS_FORGOT_PASSWORD_POST_ACTION = "forgotpasswordpost";
    /**
     * @const EBIZCHARGE_CUSTOMER_ACTIONS_RESET_PASSWORD_POST_ACTION
     */
    public const  EBIZCHARGE_CUSTOMER_ACTIONS_RESET_PASSWORD_POST_ACTION = "resetpasswordpost";

    /**
     * @const EBIZCHARGE_CUSTOMER_ACTIONS_CREATE_PASSWORD_ACTION
     */
    public const  EBIZCHARGE_CUSTOMER_ACTIONS_CREATE_PASSWORD_ACTION = "createpassword";
    /**
     * @const EBIZCHARGE_CUSTOMER_ACTIONS_LOGOUT_SUCCESS_ACTION
     */
    public const EBIZCHARGE_CUSTOMER_ACTIONS_LOGOUT_SUCCESS_ACTION = "logoutsuccess";
    /**
     * @const EBIZCHARGE_CUSTOMER_ACTIONS_CONFIRM_ACTION
     */
    public const EBIZCHARGE_CUSTOMER_ACTIONS_CONFIRM_ACTION = "confirm";
    /**
     * @const EBIZCHARGE_CUSTOMER_ACTIONS_CONFIRMATION_ACTION
     */
    public const  EBIZCHARGE_CUSTOMER_ACTIONS_CONFIRMATION_ACTION = "confirmation";
    /**
     * @const EBIZCHARGE_CUSTOMER_ACTIONS_LOGIN_POST_ACTION
     */
    public const  EBIZCHARGE_CUSTOMER_ACTIONS_LOGIN_POST_ACTION = "loginpost";
    /**
     * @const EBIZCHARGE_CUSTOMER_ACTIONS_CREATE_POST_ACTION
     */
    public const EBIZCHARGE_CUSTOMER_ACTIONS_CREATE_POST_ACTION = "createpost";

    /**
     * @const EBIZCHARGE_CUSTOMER_ACTIONS_CREATE_NEW_SAVE_ACTION
     */
    public const EBIZCHARGE_CUSTOMER_ACTIONS_CREATE_NEW_SAVE_ACTION = "save";

    /**
     * @const EBIZCHARGE_CUSTOMER_ACTIONS_CUSTOMER_LOAD_ACTION
     */
    public const EBIZCHARGE_CUSTOMER_ACTIONS_CUSTOMER_LOAD_ACTION = "load";

    /**
     * @const EBIZCHARGE_CUSTOMER_CONTROLLER_ACTIONS
     */
    public const EBIZCHARGE_CUSTOMER_CONTROLLER_ACTIONS = [
        self::EBIZCHARGE_CUSTOMER_ACTIONS_FORGOT_PASSWORD_ACTION,
        self::EBIZCHARGE_CUSTOMER_ACTIONS_FORGOT_PASSWORD_POST_ACTION,
        self::EBIZCHARGE_CUSTOMER_ACTIONS_RESET_PASSWORD_POST_ACTION,
        self::EBIZCHARGE_CUSTOMER_ACTIONS_CREATE_PASSWORD_ACTION,
        self::EBIZCHARGE_CUSTOMER_ACTIONS_LOGOUT_SUCCESS_ACTION,
        self::EBIZCHARGE_CUSTOMER_ACTIONS_CONFIRM_ACTION,
        self::EBIZCHARGE_CUSTOMER_ACTIONS_CONFIRMATION_ACTION,
        self::EBIZCHARGE_CUSTOMER_ACTIONS_LOGIN_POST_ACTION,
        self::EBIZCHARGE_CUSTOMER_ACTIONS_CREATE_POST_ACTION,
        self::EBIZCHARGE_CUSTOMER_ACTIONS_CREATE_NEW_SAVE_ACTION,
        self::EBIZCHARGE_CUSTOMER_ACTIONS_CUSTOMER_LOAD_ACTION
    ];

    /**
     * Get Software Id
     *
     * @return string
     */
    public function getEcSoftwareId();

    /**
     * Set Software Id
     *
     * @param mixed $ecSoftwareId
     * @return CustomerInterface
     */
    public function setEcSoftwareId($ecSoftwareId): CustomerInterface;

    /**
     * Get Division Id
     *
     * @return string
     */
    public function getEcDivisionId();

    /**
     * Set Division Id
     *
     * @param mixed $ecDivisionId
     * @return CustomerInterface
     */
    public function setEcDivisionId($ecDivisionId): CustomerInterface;

    /**
     * Get Ebizcharge Customer Sync Status
     *
     * @return mixed
     */
    public function getEcCustSyncStatus();

    /**
     * Set Ebizcharge Customer Sync Status
     *
     * @param mixed $ecCustSyncStatus
     * @return CustomerInterface
     */
    public function setEcCustSyncStatus($ecCustSyncStatus): CustomerInterface;

    /**
     * Get Ebizcharge Customer Internal Id
     *
     * @return mixed
     */
    public function getEcCustInternalId();

    /**
     * Set Ebizcharge customer Internal Id
     *
     * @param mixed $ecCustInternalId
     * @return CustomerInterface
     */
    public function setEcCustInternalId($ecCustInternalId): CustomerInterface;

    /**
     * Get Ebizcharge Customer Id
     *
     * @return string
     */
    public function getEcCustId();

    /**
     * Set Ebizcharge Customer Id
     *
     * @param mixed $ecCustId
     * @return CustomerInterface
     */
    public function setEcCustId($ecCustId): CustomerInterface;

    /**
     * Get Ebizcharge Customer Token
     *
     * @return mixed
     */
    public function getEcCustToken();

    /**
     * Set Ebizcharge Customer Token
     *
     * @param mixed $ecCustToken
     * @return CustomerInterface
     */
    public function setEcCustToken($ecCustToken): CustomerInterface;

    /**
     * Get Ebizcharge Customer Last Sync Date
     *
     * @return mixed
     */
    public function getEcCustLastSyncDate();

    /**
     * Set Ebizcharge Customer Last Sync Date
     *
     * @param mixed $ecCustLastSyncDate
     * @return CoreCustomerInterface
     */
    public function setEcCustLastSyncDate($ecCustLastSyncDate);

    /**
     * Set disable auto group change flag.
     *
     * @param int $disableAutoGroupChange
     * @return CoreCustomerInterface
     */
    public function setDisableAutoGroupChange($disableAutoGroupChange);
}

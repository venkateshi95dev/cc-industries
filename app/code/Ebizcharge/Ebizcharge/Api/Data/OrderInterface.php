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

use Magento\Sales\Api\Data\OrderInterface as CoreOrderInterface;

/**
 * Interface OrderInterface
 *
 * Order Data Interface
 */
interface OrderInterface extends CoreOrderInterface
{
    /**
     * Econnect Order Sync Status
     *
     * @const EC_ORDER_SYNC_STATUS
     */
    public const EC_ORDER_SYNC_STATUS = 'ec_order_sync_status';

    /**
     * Econnect Order Internal Id
     *
     * @const EC_ORDER_INTERNALID
     */
    public const EC_ORDER_INTERNALID = 'ec_order_internalid';

    /**
     * Econnect OrderId
     * @const EC_ORDER_ID
     */
    public const EC_ORDER_ID = 'ec_order_id';

    /**
     * Econnect Customer Id
     *
     * @const EC_CUST_ID
     */
    public const EC_CUST_ID = 'ec_cust_id';

    /**
     * Econnect Order Last Sync Date
     *
     * @const EC_ORDER_LASTSYNCDATE
     */
    public const EC_ORDER_LASTSYNCDATE = 'ec_order_lastsyncdate';

    /**
     * Econnect Order PO Number
     *
     * @const EC_ORDER_PO_NUMBER
     */
    public const EC_ORDER_PO_NUMBER = 'ec_po_number';

    /**
     * EC Order Division Id
     *
     * @const EC_ORDER_DIVISION_ID
     */
    public const EC_ORDER_DIVISION_ID = 'ec_division_id';

    /**
     * EC Order Date Uploaded
     *
     * @const EC_ORDER_DATE_UPLOADED
     */
    public const EC_ORDER_DATE_UPLOADED = 'ec_date_uploaded';

    /**
     * EC Order Due Date
     *
     * @const EC_ORDER_DUE_DATE
     */
    public const EC_ORDER_DUE_DATE = 'ec_due_date';

    /**
     * Econnect ORDER Created IN
     *
     * @const EC_ORDER_CREATED_IN
     */
    public const EC_ORDER_CREATED_IN = 'ec_created_in';

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
     * Recurring Parent Order Number
     *
     * @const: EBIZCHARGE_RECURRING_PARENT_ORDER
     */
    public const EBIZCHARGE_RECURRING_PARENT_ORDER = 'recurring_parent_order';

    /**
     * Recurring Additional Info
     *
     * @const: EBIZCHARGE_RECURRING_ADDITIONAL_INFO
     */
    public const EBIZCHARGE_RECURRING_ADDITIONAL_INFO = 'recurring_additional_info';

    /**
     * Webform Checkout webform type
     *
     * @const: EBIZCHARGE_WEBFORM_CHECKOUT_FORM_TYPE
     */
    public const EBIZCHARGE_WEBFORM_CHECKOUT_FORM_TYPE = 'webform';

    /**
     * Webform Checkout Registered User type
     *
     * @const: EBIZCHARGE_WEBFORM_CHECKOUT_REGISTERED_USER_FORM_TYPE
     */
    public const EBIZCHARGE_WEBFORM_CHECKOUT_REGISTERED_USER_FORM_TYPE = 'CheckoutUser';

    /**
     * Webform Checkout Guest User type
     *
     * @const: EBIZCHARGE_WEBFORM_CHECKOUT_GUEST_USER_FORM_TYPE
     */
    public const EBIZCHARGE_WEBFORM_CHECKOUT_GUEST_USER_FORM_TYPE = 'CheckoutGuest';

    /**
     * Webform Checkout Registered User tokenized only
     *
     * @const: EBIZCHARGE_WEBFORM_CHECKOUT_REGISTERED_USER_TOKENIZED_ONLY_FORM_TYPE
     */
    public const EBIZCHARGE_WEBFORM_CHECKOUT_REGISTERED_USER_TOKENIZED_ONLY_FORM_TYPE = 'RPMcheckoutUser';

    /**
     * Webform checkout payment method types Credit Card or Bank Accounts
     *
     * @const: EBIZCHARGE_WEBFORM_CHECKOUT_PAYMENT_METHOD_TYPES
     */
    public const EBIZCHARGE_WEBFORM_CHECKOUT_PAYMENT_METHOD_TYPES = "CC, ACH";

    /**
     * Webform checkout payment type checkout
     *
     * @const: EBIZCHARGE_WEBFORM_CHECKOUT_PAYMENT_METHOD_TYPES
     */
    public const EBIZCHARGE_WEBFORM_PAYMENT_TYPE_CHECKOUT = "checkout";

    /**
     * Webform payment type add new Payment method
     *
     * @const: EBIZCHARGE_WEBFORM_PAYMENT_TYPE_ADD_NEW_PAYMENT_METHOD
     */
    public const EBIZCHARGE_WEBFORM_PAYMENT_TYPE_ADD_NEW_PAYMENT_METHOD = "add_payment_method";

    /**
     * Not Avaiable
     */
    public const NOT_AVAILABLE = "N/A";

    /**
     * Get Software Id
     *
     * @return string
     */
    public function getSoftwareId(): string;

    /**
     * Set Software Id
     *
     * @param mixed $ecSoftwareId
     * @return OrderInterface
     */
    public function setSoftwareId($ecSoftwareId): OrderInterface;

    /**
     * Get Division Id
     *
     * @return string
     */
    public function getDivisionId(): string;

    /**
     * Get Recurring Parent Order
     *
     * @return string
     */
    public function getRecurringParentOrder(): string;

    /**
     * Get Recurrign Additional Info
     *
     * @return string
     */
    public function getRecurringAdditionalInfo(): string;

    /**
     * Set Division Id
     *
     * @param mixed $ecDivisionId
     * @return OrderInterface
     */
    public function setDivisionId($ecDivisionId): OrderInterface;

    /**
     * Get Ebizcharge Order Sync Status
     *
     * @return mixed
     */
    public function getEcOrderSyncStatus();

    /**
     * Set Ebizcharge Order Sync Status
     *
     * @param mixed $ecOrderSyncStatus
     * @return $this
     */
    public function setEcOrderSyncStatus($ecOrderSyncStatus);

    /**
     * Get Ebizcharge Order Internal Id
     *
     * @return mixed
     */
    public function getEcOrderInternalId();

    /**
     * Set Ebizcharge Order Internal Id
     *
     * @param mixed $ecOrderInternalId
     * @return $this
     */
    public function setEcOrderInternalId($ecOrderInternalId);

    /**
     * Get Ec Order Id
     *
     * @return mixed
     */
    public function getEcOrderId();

    /**
     * Set Ec Order Id
     *
     * @param mixed $ecOrderId
     * @return mixed
     */
    public function setEcOrderId($ecOrderId);

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
     * @return $this
     */
    public function setEcCustId($ecCustId);

    /**
     * Get Ebizcharge Order Last Sync Date
     *
     * @return mixed
     */
    public function getEcOrderLastSyncDate();

    /**
     * Set Ebizcharge ORder Last Sync Date
     *
     * @param mixed $ecOrderLastSyncDate
     * @return $this
     */
    public function setEcOrderLastSyncDate($ecOrderLastSyncDate);

    /**
     * Get Ec Order PO Number
     *
     * @return mixed
     */
    public function getEcOrderPoNumber();

    /**
     * Set Ec Po Number
     *
     * @param mixed $ecOrderPoNumber
     * @return mixed
     */
    public function setEcOrderPoNumber($ecOrderPoNumber);

    /**
     * Get Ec Divivion Id
     *
     * @return mixed
     */
    public function getEcDivisionId();

    /**
     * Set Division Id
     *
     * @param mixed $ecDivisionId
     * @return mixed
     */
    public function setEcDivisionId($ecDivisionId);

    /**
     * Get Ec Date Uploaded
     *
     * @return mixed
     */
    public function getEcDateUploaded();

    /**
     * Set Ec Date Uplaoded
     *
     * @param mixed $ecDateUploaded
     * @return mixed
     */
    public function setEcDateUploaded($ecDateUploaded);

    /**
     * Get Ec Due Date
     *
     * @return mixed
     */
    public function getEcDueDate();

    /**
     * Set Ec Due Date
     *
     * @param mixed $ecDueDate
     * @return mixed
     */
    public function setEcDueDate($ecDueDate);

    /**
     * Get Ec Order Created In
     *
     * @return mixed
     */
    public function getEcOrderCreatedIn();

    /**
     * Set Ec Order Created In
     *
     * @param mixed $ecOrderCreatedIn
     * @return mixed
     */
    public function setEcOrderCreatedIn($ecOrderCreatedIn);

    /**
     * Set Recurring Parent Order
     *
     * @param mixed $recurringParentOrder
     * @return mixed
     */
    public function setRecurringParentOrder($recurringParentOrder);

    /**
     * Set Recurring Additonal Info
     *
     * @param mixed $recurringAdditionalInfo
     * @return mixed
     */
    public function setRecurringAdditionalInfo($recurringAdditionalInfo);

    /**
     * Get Ec Surcharge Amount
     *
     * @return mixed
     */
    public function getEcSurchargeAmount();

    /**
     * Get Ec Surcharge Percentage
     *
     * @return mixed
     */
    public function getEcSurchargePercentage();

    /**
     * Get Ec Surcharge Ineligible
     *
     * @return mixed
     */
    public function getEcSurchargeIneligible();

    /**
     * Set Ec Surcharge Amount
     *
     * @param float|null $surchargeAmount
     * @return $this
     */
    public function setEcSurchargeAmount($surchargeAmount);

    /**
     * Set Ec Surcharge Percentage
     *
     * @param float|null $surchargePercentage
     * @return $this
     */
    public function setEcSurchargePercentage($surchargePercentage);

    /**
     * Set Ec Surcharge Ineligible
     *
     * @param float|null $surchargeIneligible
     * @return $this
     */
    public function setEcSurchargeIneligible($surchargeIneligible);
}

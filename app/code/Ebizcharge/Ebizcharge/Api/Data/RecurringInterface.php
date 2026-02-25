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
 * Interface RecurringInterface
 *
 * Recurring Data Interface
 */
interface RecurringInterface
{

    /**
     * ACL for Admin Resources
     *
     * @const ADMIN_RESOURCE
     */
    public const ADMIN_RESOURCE = 'Ebizcharge_Ebizcharge::admin_actions_subscriptions_orders_save';

    /**
     * Response Return URL
     *
     * @const: RESPONSE_RETURN_URL
     */
    public const RESPONSE_RETURN_URL = 'ebizcharge_ebizcharge/recurrings';

    /**
     * @const: RESPONSE_ADD_SUBSCRIPTION_URL
     */
    public const RESPONSE_ADD_SUBSCRIPTION_URL = 'ebizcharge_ebizcharge/recurrings/addaction/key';

    /**
     * Wrong Request
     *
     * @const WRONG_REQUEST
     */
    public const WRONG_REQUEST = 1;

    /**
     * Wrong Token
     *
     * @const WRONG_TOKEN
     */
    public const WRONG_TOKEN = 2;

    /**
     * Action Exception
     *
     * @const ACTION_EXCEPTION
     */
    public const ACTION_EXCEPTION = 3;

    /**
     * @const RECURRING_TABLE_NAME
     */
    public const RECURRING_TABLE_NAME = 'ebizcharge_recurring';

    /**
     * Subscribed Product Title
     * @const SUBSCRIBED_PRODUCT_COLUMN_TITLE
     */
    public const SUBSCRIBED_PRODUCT_COLUMN_TITLE = "subscribed";

    /**
     *
     * Recurring indefinite limit Default
     *
     * @const: DEFAULT_INDEFINITE_RECURRING_LIMIT
     */
    public const DEFAULT_INDEFINITE_RECURRING_LIMIT = 5;

    /**
     *  Recurring Frequencies Daily
     *
     * @const:  RECURRING_FREQUENCIES_DAILY
     */
    public const RECURRING_FREQUENCIES_DAILY = "daily";

    /**
     * Recurring Frequencies Weekly
     *
     * @const: RECURRING_FREQUENCIES_WEEKLY
     */
    public const RECURRING_FREQUENCIES_WEEKLY = "weekly";

    /**
     * Recurring Frequencies Bi Weekly
     *
     * @const: RECURRING_FREQUENCIES_BI_WEEKLY
     */
    public const RECURRING_FREQUENCIES_BI_WEEKLY = "bi-weekly";

    /**
     * Recurring Frequencies Bi Monthly
     *
     * @const: RECURRING_FREQUENCIES_BI_MONTHLY
     */
    public const RECURRING_FREQUENCIES_BI_MONTHLY = "bi-monthly";

    /**
     *  Recurring Frequencies Four Week
     *
     * @const: RECURRING_FREQUENCIES_FOUR_WEEK
     */
    public const RECURRING_FREQUENCIES_FOUR_WEEK = "four-week";

    /**
     * Recurring Frequencies Monthly
     *
     * @const: RECURRING_FREQUENCIES_MONTHLY
     */
    public const RECURRING_FREQUENCIES_MONTHLY = "monthly";

    /**
     * Recurring Frequencies Two Month
     *
     * @const: RECURRING_FREQUENCIES_TWO_MONTH
     */
    public const RECURRING_FREQUENCIES_TWO_MONTH = "two-month";

    /**
     *  Recurring Frequencies Quarterly
     *
     * @const: RECURRING_FREQUENCIES_QUARTERLY
     */
    public const RECURRING_FREQUENCIES_QUARTERLY = "quarterly";

    /**
     * Recurring Frequencies Three Month
     *
     * @const: RECURRING_FREQUENCIES_THREE_MONTH
     */
    public const RECURRING_FREQUENCIES_THREE_MONTH = "three-month";

    /**
     * Recurring Frequencies 90 Days
     *
     * @const: RECURRING_FREQUENCIES_90_DAYS
     */
    public const RECURRING_FREQUENCIES_90_DAYS = "90-days";

    /**
     * Recurring Frequencies Four Month
     *
     * @const: RECURRING_FREQUENCIES_FOUR_MONTH
     */
    public const RECURRING_FREQUENCIES_FOUR_MONTH = "four-month";

    /**
     * Recurring Frequencies Five Month
     *
     * @const: RECURRING_FREQUENCIES_FIVE_MONTH
     */
    public const RECURRING_FREQUENCIES_FIVE_MONTH = "five-month";

    /**
     * Recurring Frequencies Six Month
     *
     * @const: RECURRING_FREQUENCIES_SIX_MONTH
     */
    public const RECURRING_FREQUENCIES_SIX_MONTH = "six-month";

    /**
     * Recurring Frequencies 180 days
     *
     * @const: RECURRING_FREQUENCIES_180_DAYS
     */
    public const RECURRING_FREQUENCIES_180_DAYS = "180-days";

    /**
     * Recurring Frequencies Bi Annaully
     *
     * @const: RECURRING_FREQUENCIES_BI_ANNUALLY
     */
    public const RECURRING_FREQUENCIES_BI_ANNUALLY = "bi-annually";

    /**
     * Recurring Frequencies Annually
     *
     * @const: RECURRING_FREQUENCIES_ANNUALLY
     */
    public const RECURRING_FREQUENCIES_ANNUALLY = "annually";

    /**
     * Cache Tag
     *
     * @const CACHE_TAG
     */
    public const CACHE_TAG = 'ebizcharge_recurring';

    /**
     * @const CUSTOMER_EMAIL
     */
    public const CUSTOMER_EMAIL = 'customer_email';

    /**
     * Recurring Status at Ebizcharge Gateway Active
     *
     * @const EBIZCHARGE_RECURRING_STATUS_ACTIVE
     */
    public const EBIZCHARGE_RECURRING_STATUS_ACTIVE = 0;

    /**
     * Recurring Status at Ebizcharge Gateway Suspended
     *
     * @const EBIZCHARGE_RECURRING_STATUS_SUSPENDED
     */
    public const EBIZCHARGE_RECURRING_STATUS_SUSPENDED = 1;

    /**
     * Recurring Status at Ebizcharge Gateway Expired
     *
     * @const EBIZCHARGE_RECURRING_STATUS_EXPIRED
     */
    public const EBIZCHARGE_RECURRING_STATUS_EXPIRED = 2;

    /**
     * Recurring Status at Ebizcharge Gateway Canceled
     *
     * @const EBIZCHARGE_RECURRING_STATUS_CANCELED
     */
    public const EBIZCHARGE_RECURRING_STATUS_CANCELED = 3;


    /**
     * Recurring Status at Ebizcharge Gateway Active
     *
     * @const EBIZCHARGE_RECURRING_STATUS_ACTIVE_TITLE
     */
    public const EBIZCHARGE_RECURRING_STATUS_ACTIVE_TITLE = "Active";

    /**
     * Recurring Status at Ebizcharge Gateway Suspended
     *
     * @const EBIZCHARGE_RECURRING_STATUS_SUSPENDED_TITLE
     */
    public const EBIZCHARGE_RECURRING_STATUS_SUSPENDED_TITLE = "Suspended";

    /**
     * Recurring Status at Ebizcharge Gateway Expired
     *
     * @const EBIZCHARGE_RECURRING_STATUS_EXPIRED_TITLE
     */
    public const EBIZCHARGE_RECURRING_STATUS_EXPIRED_TITLE = "Expired";

    /**
     * Recurring Status at Ebizcharge Gateway Canceled
     *
     * @const EBIZCHARGE_RECURRING_STATUS_CANCELED_TITLE
     */
    public const EBIZCHARGE_RECURRING_STATUS_CANCELED_TITLE = "Canceled";

    /**
     *
     * @const EBIZCHARGE_RECURRING_STATUSES
     */
    public const  EBIZCHARGE_RECURRING_STATUSES = [
        self::EBIZCHARGE_RECURRING_STATUS_ACTIVE => self::EBIZCHARGE_RECURRING_STATUS_ACTIVE_TITLE,
        self::EBIZCHARGE_RECURRING_STATUS_SUSPENDED => self::EBIZCHARGE_RECURRING_STATUS_SUSPENDED_TITLE,
        self::EBIZCHARGE_RECURRING_STATUS_EXPIRED => self::EBIZCHARGE_RECURRING_STATUS_EXPIRED_TITLE,
        self::EBIZCHARGE_RECURRING_STATUS_CANCELED => self::EBIZCHARGE_RECURRING_STATUS_CANCELED_TITLE
    ];

    /**
     *  Recurring Status Label Active
     *
     * @const: RECURRING_STATUS_LABEL_ACTIVE
     */
    public const RECURRING_STATUS_LABEL_ACTIVE = 'On';

    /**
     * Recurring Status Label Off
     *
     * @const: RECURRING_STATUS_LABEL_OFF
     */
    public const RECURRING_STATUS_LABEL_OFF = 'Off';

    /**
     * Recurring Status Label Expired
     *
     * @const: RECURRING_STATUS_LABEL_EXPIRED
     */
    public const RECURRING_STATUS_LABEL_EXPIRED = 'Expired';

    /**
     *  Recurring Status Label Suspended
     *
     * @const: RECURRING_STATUS_LABEL_SUSPENDED
     */
    public const RECURRING_STATUS_LABEL_SUSPENDED = 'Suspended';

    /**
     *  Recurring Status Label Canceled
     *
     * @const: RECURRING_STATUS_LABEL_CANCELED
     */
    public const RECURRING_STATUS_LABEL_CANCELED = 'canceled';

    /**
     *  Recurring Status Label Unsubscribed
     *
     * @const: RECURRING_STATUS_LABEL_UN_SUBSCRIBED
     */
    public const RECURRING_STATUS_LABEL_UN_SUBSCRIBED = 'UnSubscribed';

    /**
     * Entity Id
     *
     * @const: ENTITY_ID
     */
    public const ENTITY_ID = 'entity_id';

    /**
     * Recurring Id
     *
     * @const REC_ID
     */
    public const REC_ID = 'rec_id';

    /**
     * Recurring Status
     *
     * @const REC_STATUS
     */
    public const REC_STATUS = 'rec_status';

    /**
     * Recurring Indefinitely
     *
     * @const REC_INDEFINITELY
     */
    public const REC_INDEFINITELY = 'rec_indefinitely';

    /**
     * Magento Customer Id
     *
     * @cont MAGE_CUST_ID
     */
    public const MAGE_CUST_ID = 'mage_cust_id';

    /**
     * Order Id
     *
     * @const MAGE_ORDER_ID
     */
    public const MAGE_ORDER_ID = 'mage_order_id';

    /**
     * Item Id definition
     *
     * @cont MAGE_ITEM_ID
     */
    public const MAGE_ITEM_ID = 'mage_item_id';

    /**
     * Item Name Definition
     *
     * @const  MAGE_ITEM_NAME
     */
    public const MAGE_ITEM_NAME = 'mage_item_name';

    /**
     * Qty Ordered definiton
     *
     * @const QTY_ORDERED
     */
    public const QTY_ORDERED = 'qty_ordered';

    /**
     * Recurring Start Date
     *
     * @const EB_REC_START_DATE
     */
    public const EB_REC_START_DATE = 'eb_rec_start_date';

    /**
     * Store Id
     *
     * @const: EB_REC_STORE_ID
     */
    public const EB_REC_STORE_ID = 'store_id';
    /**
     * Quote Id
     *
     * @const: EB_REC_QUOTE_ID
     */
    public const EB_REC_QUOTE_ID = 'quote_id';

    /**
     * Discount
     *
     * @const: EB_REC_DISCOUNT
     */
    public const EB_REC_DISCOUNT = 'discount';

    /**
     * Coupon Code
     *
     * @const: EB_REC_COUPON_CODE
     */
    public const EB_REC_COUPON_CODE = 'coupon_code';

    /**
     * Cart Rule Id
     *
     * @const: EB_REC_CART_RULE_ID
     */
    public const EB_REC_CART_RULE_ID = 'cart_rule_id';

    /**
     * Product Rule Id
     *
     * @const: EB_REC_PRODUCT_RULE_ID
     */
    public const EB_REC_PRODUCT_RULE_ID = 'product_rule_id';

    /**
     * Recurring End Date Definition
     *
     * @const EB_REC_END_DATE
     */
    public const EB_REC_END_DATE = 'eb_rec_end_date';

    /**
     * Recurring Frequency Definiton
     *
     * @const EB_REC_FREQUENCY
     */
    public const EB_REC_FREQUENCY = 'eb_rec_frequency';

    /**
     * Recurring Method Id
     *
     * @const EB_REC_METHOD_ID
     */
    public const EB_REC_METHOD_ID = 'eb_rec_method_id';

    /**
     * Payment Inter ID Definition
     *
     * @const EB_REC_SCHEDULED_PAYMENT_INTERNAL_ID
     */
    public const EB_REC_SCHEDULED_PAYMENT_INTERNAL_ID = 'eb_rec_scheduled_payment_internal_id';

    /**
     * Product Final Price
     *
     * @const: EB_ORDERED_PRODUCT_FINAL_PRICE
     */
    public const EB_ORDERED_PRODUCT_FINAL_PRICE = 'ordered_product_final_price';

    /**
     * Recurring Total
     *
     * @const EB_REC_TOTAL
     */
    public const EB_REC_TOTAL = 'eb_rec_total';

    /**
     * Recurring Processed
     *
     * @cont EB_REC_PROCESSED
     */
    public const EB_REC_PROCESSED = 'eb_rec_processed';

    /**
     * Recurring Next
     *
     * @const EB_REC_NEXT
     */
    public const EB_REC_NEXT = 'eb_rec_next';

    /**
     * Recurring Remaining
     *
     * @const EB_REC_REMAINING
     */
    public const EB_REC_REMAINING = 'eb_rec_remaining';

    /**
     * Recurring Due Dates
     *
     * @const EB_REC_DUE_DATES
     */
    public const EB_REC_DUE_DATES = 'eb_rec_due_dates';

    /**
     * Parent Item Id
     *
     * @const MAGE_PARENT_ITEM_ID
     */
    public const MAGE_PARENT_ITEM_ID = 'mage_parent_item_id';

    /**
     * Billing Address Id
     *
     * @const BILLING_ADDRESS_ID
     */
    public const BILLING_ADDRESS_ID = 'billing_address_id';

    /**
     * Shipping Address ID
     *
     * @const SHIPPING_ADDRESS_ID
     */
    public const SHIPPING_ADDRESS_ID = 'shipping_address_id';

    /**
     * Amount Definition
     *
     * @const AMOUNT
     */
    public const AMOUNT = 'amount';

    /**
     * Subtotal
     */
    public const SUBTOTAL = "subotal";


    /**
     * Shipping Amount Definition
     *
     * @const SHIPPING_AMOUNT
     */
    public const SHIPPING_AMOUNT = 'shipping_amount';

    /**
     * Tax Amount Definition
     *
     * @const SHIPPING_AMOUNT
     */
    public const TAX_AMOUNT = 'tax_amount';

    /**
     * Grand Total Definition
     *
     * @const GRAND_TOTAL
     */
    public const GRAND_TOTAL = 'grand_total';

    /**
     * Surcharge Amount Definition
     *
     * @const SURCHARGE_AMOUNT
     */
    public const SURCHARGE_AMOUNT = 'surcharge_amount';

    /**
     * Item Price Definition
     *
     * @const ITEM_PRICE
     */
    public const ITEM_PRICE = 'item_price';


    /**
     * Payment Method Definition
     *
     * @const PAYMENT_METHOD_NAME
     */
    public const PAYMENT_METHOD_NAME = 'payment_method_name';

    /**
     * Shipping Method Definition
     *
     * @const SHIPPING_METHOD
     */
    public const SHIPPING_METHOD = 'shipping_method';

    /**
     * Failed Attempts
     *
     * @const FAILED_ATTEMPTS
     */
    public const FAILED_ATTEMPTS = 'failed_attempts';

    /**
     * Ordered Date
     *
     * @const ORDERED_DATE
     */
    public const ORDERED_DATE = 'order_date';

    /**
     * Created At Date
     *
     * @const CREATED_AT
     */
    public const CREATED_AT = 'created_at';

    /**
     * Updated At Date
     *
     * @const UPDATED_AT
     */
    public const UPDATED_AT = 'updated_at';

    /**
     * Unsubscribed Status Key
     *
     * @const: EBIZCHARGE_RECURRING_STATUS_KEY_UNSUBSCRIBED
     */
    public const EBIZCHARGE_RECURRING_STATUS_KEY_UNSUBSCRIBED = 'unsubscribe';

    /**
     * Suspended Status Key
     *
     * @const: EBIZCHARGE_RECURRING_STATUS_KEY_SUSPENDED
     */
    public const EBIZCHARGE_RECURRING_STATUS_KEY_SUSPENDED = 'suspend';

    /**
     * Suspended Status Key
     *
     * @const: EBIZCHARGE_RECURRING_STATUS_KEY_EXPIRED
     */
    public const EBIZCHARGE_RECURRING_STATUS_KEY_EXPIRED = 'expired';

    /**
     * Suspended Status Key
     *
     * @const: EBIZCHARGE_RECURRING_STATUS_KEY_ACTIVE
     */
    public const EBIZCHARGE_RECURRING_STATUS_KEY_ACTIVE = 'active';

    /**
     * Get Ordered Date
     *
     * @return mixed
     */
    public function getOrderedDate();

    /**
     * Set Ordered Date
     *
     * @param mixed $orderedDate
     * @return mixed
     */
    public function setOrderedDate($orderedDate);

    /**
     * Get Entity Id
     *
     * @return int
     */
    public function getEntityId();

    /**
     * Set Entity Id
     *
     * @param int $entityId
     * @return RecurringInterface
     */
    public function setEntityId($entityId);

    /**
     * Set value for Recurring Id
     *
     * @param string $rec_id
     * @return RecurringInterface
     */
    public function setRecId(string $rec_id): RecurringInterface;

    /**
     * Get value for Recurring Id
     *
     * @return string
     */
    public function getRecId(): string;

    /**
     * Set Recurring Status Value
     *
     * @param int $rec_status
     * @return RecurringInterface
     */
    public function setRecStatus(int $rec_status): RecurringInterface;

    /**
     * Get Value for Recurring Status
     *
     * @return int
     */
    public function getRecStatus(): int;

    /**
     * Set value for Recurring indefinitely
     *
     * @param int $rec_indefinitely
     * @return RecurringInterface
     */
    public function setRecIndefinitely(int $rec_indefinitely): RecurringInterface;

    /**
     * Get value for Recurring Indefinitely
     *
     * @return int
     */
    public function getRecIndefinitely(): int;

    /**
     * Set Magento Customer Id
     *
     * @param string $mage_cust_id
     * @return RecurringInterface
     */
    public function setMageCustId(string $mage_cust_id): RecurringInterface;

    /**
     * Get Magento Customer Id
     *
     * @return string
     */
    public function getMageCustId(): string;

    /**
     * Get Coupon Code
     *
     * @return string
     */
    public function getCouponCode(): string;

    /**
     * Set Coupon Code
     *
     * @param string $couponCode
     * @return RecurringInterface
     */
    public function setCouponCode(string $couponCode): RecurringInterface;

    /**
     * Get Discount Amount
     *
     * @return string
     */
    public function getDiscount(): string;

    /**
     * Set Discount Amount
     *
     * @param string $discount
     * @return RecurringInterface
     */
    public function setDiscount(string $discount): RecurringInterface;


    /**
     * Set Shipping Amount
     *
     * @param string $shippingAmount
     * @return RecurringInterface
     */
    public function setShippingAmount(string $shippingAmount): RecurringInterface;

    /**
     * Set Tax Amount
     *
     * @param string $taxAmount
     * @return RecurringInterface
     */
    public function setTaxAmount(string $taxAmount): RecurringInterface;

    /**
     * Set Grand Total
     *
     * @param string $grandTotal
     * @return RecurringInterface
     */
    public function setGrandTotal(string $grandTotal): RecurringInterface;

    /**
     * @param string $surchargeAmount
     * @return RecurringInterface
     */
    public function setSurchargeAmount(string $surchargeAmount): RecurringInterface;

    /**
     * @param string $itemPrice
     * @return RecurringInterface
     */
    public function setItemPrice(string $itemPrice): RecurringInterface;

    /**
     * Get Product Rule Id
     *
     * @return int
     */
    public function getProductRuleId(): int;

    /**
     * Set Product Rule Id
     *
     * @param int $productRuleId
     * @return RecurringInterface
     */
    public function setProductRuleId(int $productRuleId): RecurringInterface;

    /**
     * Get Cart Rule Id
     *
     * @return int
     */
    public function getCartRuleId(): int;

    /**
     * Set Cart Rule Id
     *
     * @param int $cartRuleId
     * @return RecurringInterface
     */
    public function setCartRuleId(int $cartRuleId): RecurringInterface;

    /**
     * Set Value for Order Id
     *
     * @param string $mage_order_id
     * @return RecurringInterface
     */
    public function setMageOrderId(string $mage_order_id): RecurringInterface;

    /**
     * Set value for Order Id
     *
     * @return string
     */
    public function getMageOrderId(): string;

    /**
     * Set Store Id
     *
     * @param int $storeId
     * @return RecurringInterface
     */
    public function setStoreId(int $storeId): RecurringInterface;

    /**
     * Get Store Id
     *
     * @return int
     */
    public function getStoreId(): int;

    /**
     * Set Magento Item Id
     *
     * @param string $mage_item_id
     * @return RecurringInterface
     */
    public function setMageItemId(string $mage_item_id): RecurringInterface;

    /**
     * Get Magento Item Id
     *
     * @return string
     */
    public function getMageItemId(): string;

    /**
     * Set Magento Item Name
     *
     * @param string $mage_item_name
     * @return RecurringInterface
     */
    public function setMageItemName(string $mage_item_name): RecurringInterface;

    /**
     * Get Magento Item Name
     *
     * @return string
     */
    public function getMageItemName(): string;

    /**
     * Set Qty Ordered
     *
     * @param string $qty_ordered
     * @return RecurringInterface
     */
    public function setQtyOrdered(string $qty_ordered): RecurringInterface;

    /**
     * Get Qty Ordered
     *
     * @return string
     */
    public function getQtyOrdered(): string;

    /**
     * Set EB Recurring Start Date
     *
     * @param string $eb_rec_start_date
     * @return RecurringInterface
     */
    public function setEbRecStartDate(string $eb_rec_start_date): RecurringInterface;

    /**
     * Get EB Recurring Start Date
     *
     * @return string
     */
    public function getEbRecStartDate(): string;

    /**
     * Set EB Recurring End Date
     *
     * @param string $eb_rec_end_date
     * @return RecurringInterface
     */
    public function setEbRecEndDate(string $eb_rec_end_date): RecurringInterface;

    /**
     * Get EB Recurring End Date
     *
     * @return string
     */
    public function getEbRecEndDate(): string;

    /**
     * Set Eb Ordered Product Final Price
     *
     * @param string $eb_rec_ordered_product_final_price
     * @return RecurringInterface
     */
    public function setEbOrderedProductFinalPrice(string $eb_rec_ordered_product_final_price): RecurringInterface;

    /**
     * Get Eb Ordered Product Final Price
     *
     * @return string
     */
    public function getEbOrderedProductFinalPrice(): string;

    /**
     * Set EB Recurring Frequency
     *
     * @param string $eb_rec_frequency
     * @return RecurringInterface
     */
    public function setEbRecFrequency(string $eb_rec_frequency): RecurringInterface;

    /**
     * Get EB Recurring Frequency
     *
     * @return string
     */
    public function getEbRecFrequency(): string;

    /**
     * Set EB Recurring Method Id
     *
     * @param mixed $eb_rec_method_id
     * @return RecurringInterface
     */
    public function setEbRecMethodId($eb_rec_method_id): RecurringInterface;

    /**
     * Get EB Recurring Method Id
     *
     * @return string
     */
    public function getEbRecMethodId(): string;

    /**
     * Set Value for Scheduled Payment Internal Id
     *
     * @param string $ebRecScheduledPaymentInternalId
     * @return RecurringInterface
     */
    public function setEbRecScheduledPaymentInternalId(string $ebRecScheduledPaymentInternalId): RecurringInterface;

    /**
     * Get EB Recurring Scheduled Payment Internal Id
     *
     * @return string
     */
    public function getEbRecScheduledPaymentInternalId(): string;

    /**
     * Set EB Recurring Total
     *
     * @param int $eb_rec_total
     * @return RecurringInterface
     */
    public function setEbRecTotal(int $eb_rec_total): RecurringInterface;

    /**
     * Get EB Recurring Total
     *
     * @return int
     */
    public function getEbRecTotal(): int;

    /**
     * Set Eb Recurring Processed
     *
     * @param int $eb_rec_processed
     * @return RecurringInterface
     */
    public function setEbRecProcessed(int $eb_rec_processed): RecurringInterface;

    /**
     * Get EB Recurring Processed
     *
     * @return int
     */
    public function getEbRecProcessed(): int;

    /**
     * Set EB Recurring Next
     *
     * @param string $eb_rec_next
     * @return RecurringInterface
     */
    public function setEbRecNext(string $eb_rec_next): RecurringInterface;

    /**
     * Get EB Recurring Next
     *
     * @return string
     */
    public function getEbRecNext(): string;

    /**
     * Set EB Recurring Remaining
     *
     * @param int $eb_rec_remaining
     * @return RecurringInterface
     */
    public function setEbRecRemaining(int $eb_rec_remaining): RecurringInterface;

    /**
     * Get EB Recurring Remaining
     *
     * @return int
     */
    public function getEbRecRemaining(): int;

    /**
     * Set EB Recurring Due Dates
     *
     * @param string $eb_rec_due_dates
     * @return RecurringInterface
     */
    public function setEbRecDueDates(string $eb_rec_due_dates): RecurringInterface;

    /**
     * Get EB Recurring Due Dates
     *
     * @return int
     */
    public function getEbRecDueDates(): int;

    /**
     * Set Magento Parent Item Id
     *
     * @param string $mage_parent_item_id
     * @return RecurringInterface
     */
    public function setMageParentItemId(string $mage_parent_item_id): RecurringInterface;

    /**
     * Get Magento Parent Id
     *
     * @return string
     */
    public function getMageParentItemId(): string;

    /**
     * Set Billing Address Id
     *
     * @param int $billing_address_id
     * @return RecurringInterface
     */
    public function setBillingAddressId(int $billing_address_id): RecurringInterface;

    /**
     * Get Billing Address Id
     *
     * @return int
     */
    public function getBillingAddressId(): int;

    /**
     * Set Shipping Address Id
     *
     * @param int $shipping_address_id
     * @return RecurringInterface
     */
    public function setShippingAddressId(int $shipping_address_id): RecurringInterface;

    /**
     * Get shipping Address Id
     *
     * @return int
     */
    public function getShippingAddressId(): int;

    /**
     * Set Value for Amount
     *
     * @param float $amount
     * @return RecurringInterface
     */
    public function setAmount(float $amount): RecurringInterface;

    /**
     * Get Amount Value
     *
     * @return float
     */
    public function getAmount(): float;

    /**
     * Get Shipping Amount Value
     *
     * @return float
     */
    public function getShippingAmount(): float;

    /**
     * @return float
     */
    public function getQuoteId(): float;

    /**
     * Get Tax Amount Value
     *
     * @return float
     */
    public function getTaxAmount(): float;

    /**
     * Get Grand Total Value
     *
     * @return float
     */
    public function getGrandTotal(): float;

    /**
     * @return float
     */
    public function getSurchargeAmount(): float;

    /**
     * @return float
     *
     */
    public function getItemPrice(): float;


    /**
     * Set Payment Method Name
     *
     * @param string $payment_method_name
     * @return RecurringInterface
     */
    public function setPaymentMethodName(string $payment_method_name): RecurringInterface;

    /**
     * Get Payment Method Name
     *
     * @return string
     */
    public function getPaymentMethodName(): string;

    /**
     * Set Shipping Method
     *
     * @param string $shipping_method
     * @return RecurringInterface
     */
    public function setShippingMethod(string $shipping_method): RecurringInterface;

    /**
     * Get Shipping Method
     *
     * @return mixed
     */
    public function getShippingMethod();

    /**
     * Set Failed Attempts Value
     *
     * @param int $failed_attempts
     * @return RecurringInterface
     */
    public function setFailedAttempts(int $failed_attempts): RecurringInterface;

    /**
     * Get Failed Attempts value
     *
     * @return int
     */
    public function getFailedAttempts(): int;

    /**
     * Get Created At
     *
     * @return mixed
     */
    public function getCreatedAt();

    /**
     * Set Created At
     *
     * @param mixed $createdAt
     * @return RecurringInterface
     */
    public function setCreatedAt($createdAt): RecurringInterface;

    /**
     * @param $quoteId
     * @return RecurringInterface
     */
    public function setQuoteId($quoteId): RecurringInterface;

    /**
     * Updated At
     *
     * @param mixed $updatedAt
     * @return RecurringInterface
     */
    public function setUpdatedAt($updatedAt): RecurringInterface;

    /**
     * Get Updated At
     *
     * @return mixed
     */
    public function getUpdatedAt();
}

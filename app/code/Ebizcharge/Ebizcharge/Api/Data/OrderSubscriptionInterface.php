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
 * Interface OrderSubscriptionInterface
 *
 * Order Subscription Data Interface
 */
interface OrderSubscriptionInterface
{

    /**
     * @const EBIZCHARGE_SUBSCRIPTION_ORDER_STATUS_COMPLETED_TITLE
     */
    public const  EBIZCHARGE_SUBSCRIPTION_ORDER_STATUS_COMPLETED_TITLE = "Completed";
    /**
     * @const EBIZCHARGE_SUBSCRIPTION_ORDER_STATUS_COMPLETED
     */
    public const  EBIZCHARGE_SUBSCRIPTION_ORDER_STATUS_COMPLETED = 0;
    /**
     * @const EBIZCHARGE_SUBSCRIPTION_ORDER_STATUS_FAILED_TITLE
     */
    public const  EBIZCHARGE_SUBSCRIPTION_ORDER_STATUS_FAILED_TITLE = "Failed";
    /**
     * @const EBIZCHARGE_SUBSCRIPTION_ORDER_STATUS_FAILED
     */
    public const  EBIZCHARGE_SUBSCRIPTION_ORDER_STATUS_FAILED = 1;


    /**
     *  Payment Method Name
     *
     * @const PAYMENT_METHOD_NAME
     */
    public const PAYMENT_METHOD_NAME = 'payment_method_name';

    /**
     * Shipping Method
     *
     * @const: SHIPPING_METHOD
     */
    public const SHIPPING_METHOD = 'shipping_method';

    /**
     * Scheduled Payment Internal Id
     *
     * @const: SCHEDULED_PAYMENT_INTERNAL_ID
     */
    public const SCHEDULED_PAYMENT_INTERNAL_ID = 'scheduled_payment_internal_id';

    /**
     * Ebizcharge Recurring Payment Id
     *
     * @const: EBIZ_RECURRING_PAYMENT_ID
     */
    public const EBIZ_RECURRING_PAYMENT_ID = 'ebiz_recurring_payment_id';

    /**
     * Ebiz Method Id
     *
     * @const: EBIZ_METHOD_ID
     */
    public const EBIZ_METHOD_ID = 'ebiz_method_id';

    /**
     * ENTITY_ID definition
     *
     * @const ENTITY_ID
     */
    public const ENTITY_ID = 'entity_id';

    /**
     * Recurring id Definition
     *
     * @const RECURRING_ID
     */
    public const RECURRING_ID = 'recurring_id';

    /**
     * Defining the Recurring Order Id
     *
     * @const REC_ORDER_ID
     */
    public const REC_ORDER_ID = 'rec_order_id';

    /**
     * Creation Date Definition
     *
     * @const CREATED_DATE
     */
    public const CREATED_DATE = 'created_date';

    /**
     * Recurring Message
     *
     * @const MESSAGE
     */
    public const MESSAGE = 'message';

    /**
     * Recurring Order Status
     *
     * @const STATUS
     */
    public const STATUS = 'status';

    /**
     * Ordered Date
     *
     * @const ORDER_DATE
     */
    public const ORDER_DATE = 'order_date';

    /**
     * Order Entity Id
     *
     * @const ORDER_ENTITY_ID
     */
    public const ORDER_ENTITY_ID = 'order_entity_id';

    /**
     * Recurring Date
     *
     * @const: RECURRING_DATE
     */
    public const RECURRING_DATE = 'recurring_date';

    /**
     * Store Id
     *
     * @const: STORE_ID
     */
    public const STORE_ID = 'store_id';

    /**
     * Coupon Code
     *
     * @const: COUPON_CODE
     */
    public const COUPON_CODE = 'coupon_code';

    /**
     * Discount
     *
     * @const: DISCOUNT
     */
    public const DISCOUNT = 'discount';

    /**
     * Created At
     *
     * @const: CREATED_AT
     */
    public const CREATED_AT = 'created_date';

    /**
     * Updated At Date
     *
     * @const UPDATED_AT
     */
    public const UPDATED_AT = 'updated_at';

    /**
     * Get Payment Method Name
     *
     * @return mixed
     */
    public function getPaymentMethodName();

    /**
     * Set Payment Method Name
     *
     * @param mixed $paymentMethodName
     * @return mixed
     */
    public function setPaymentMethodName($paymentMethodName);

    /**
     * Get Shipping Method
     *
     * @return mixed
     */
    public function getShippingMethod();

    /**
     * Set Shipping Method
     *
     * @param mixed $shippingMethod
     * @return mixed
     */
    public function setShippingMethod($shippingMethod);

    /**
     * Get Scheduled Payment Internal Id
     *
     * @return mixed
     */
    public function getScheduledPaymentInternalId();

    /**
     * Set Scheduled Payment Internal Id
     *
     * @param mixed $scheduledPaymentInternalId
     * @return mixed
     */
    public function setScheduledPaymentInternalId($scheduledPaymentInternalId);

    /**
     * Get Ebizcharge Recurring Payment Id
     *
     * @return mixed
     */
    public function getEbizRecurringPaymentId();

    /**
     * Set Ebizcharge Recurring Payment Id
     *
     * @param mixed $ebizRecurringPaymentId
     * @return mixed
     */
    public function setEbizRecurringPaymentId($ebizRecurringPaymentId);

    /**
     * Get Ebizcharge Method Id
     *
     * @return mixed
     */
    public function getEbizMethodId();

    /**
     * Set Ebizcharge Method Id
     *
     * @param mixed $ebizMethodId
     * @return mixed
     */
    public function setEbizMethodId($ebizMethodId);

    /**
     * Get Store Id
     *
     * @return mixed
     */
    public function getStoreId();

    /**
     * Set Store Id
     *
     * @param mixed $storeId
     * @return mixed
     */
    public function setStoreId($storeId);

    /**
     * Entity Id
     *
     * @return int
     */
    public function getEntityId();

    /**
     * Get Discount
     *
     * @return mixed
     */
    public function getDiscount();

    /**
     * Set Discount
     *
     * @param mixed $discount
     * @return mixed
     */
    public function setDiscount($discount);

    /**
     * Get coupon Code
     *
     * @return mixed
     */
    public function getCouponCode();

    /**
     * Set Coupon Code
     *
     * @param mixed $couponCode
     * @return mixed
     */
    public function setCouponCode($couponCode);

    /**
     * Get Created At
     *
     * @return mixed
     */
    public function getCreatedAt();

    /**
     * Set Entity Id
     *
     * @param int $entityId
     * @return OrderSubscriptionInterface
     */
    public function setEntityId($entityId);

    /**
     * Recurring Date
     *
     * @return mixed
     */
    public function getReccurrigDate();

    /**
     * Set Recurring Date
     *
     * @param mixed $recurringDate
     * @return mixed
     */
    public function setRecurringdate($recurringDate);

    /**
     * Set Recurring Id
     *
     * @param int $recurring_id
     * @return OrderSubscriptionInterface
     */
    public function setRecurringId(int $recurring_id): OrderSubscriptionInterface;

    /**
     * Get Recurring Id
     *
     * @return int
     */
    public function getRecurringId(): int;

    /**
     * Set Recurring Order Id
     *
     * @param int $rec_order_id
     * @return OrderSubscriptionInterface
     */
    public function setRecurringOrderId(int $rec_order_id): OrderSubscriptionInterface;

    /**
     * Get Recurring Order Id
     *
     * @return int
     */
    public function getRecurringOrderId(): int;

    /**
     * Set Order Creation Date
     *
     * @param mixed $created_date
     * @return OrderSubscriptionInterface
     */
    public function setOrderCreatedDate($created_date): OrderSubscriptionInterface;

    /**
     * Get created At Date
     *
     * @return mixed
     */
    public function getOrderCreatedDate();

    /**
     * Set Order Message
     *
     * @param mixed $message
     * @return OrderSubscriptionInterface
     */
    public function setOrderMessage($message): OrderSubscriptionInterface;

    /**
     * Get Order Message
     *
     * @return string
     */
    public function getOrderMessage(): string;

    /**
     * Set Order Status
     *
     * @param int $status
     * @return OrderSubscriptionInterface
     */
    public function setOrderStatus(int $status): OrderSubscriptionInterface;

    /**
     * Get order Status
     *
     * @return int
     */
    public function getOrderStatus(): int;

    /**
     * Set Recurring Order Date
     *
     * @param mixed $recurring_date
     * @return OrderSubscriptionInterface
     */
    public function setRecurringOrderDate($recurring_date): OrderSubscriptionInterface;

    /**
     * Get Recurring Order Date
     *
     * @return mixed
     */
    public function getRecurringOrderDate();

    /**
     * Set Order Entity Id
     *
     * @param int $order_entity_id
     * @return OrderSubscriptionInterface
     */
    public function setOrderEntityId(int $order_entity_id): OrderSubscriptionInterface;

    /**
     * Get Order Entity Id
     *
     * @return int
     */
    public function getOrderEntityId(): int;

    /**
     * Set Created At
     *
     * @param mixed $createdAt
     * @return OrderSubscriptionInterface
     */
    public function setCreatedAt($createdAt): OrderSubscriptionInterface;

    /**
     * Updated At
     *
     * @param mixed $updatedAt
     * @return OrderSubscriptionInterface
     */
    public function setUpdatedAt($updatedAt): OrderSubscriptionInterface;

    /**
     * Get Updated At
     *
     * @return mixed
     */
    public function getUpdatedAt();
}

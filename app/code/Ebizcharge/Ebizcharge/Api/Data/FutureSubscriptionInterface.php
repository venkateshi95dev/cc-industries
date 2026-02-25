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
 * Interface FutureSubscriptionInterface
 *
 * Future Subscription Data Interface
 */
interface FutureSubscriptionInterface
{
    /**
     * Ebizcharge Future
     *
     * @const FUTURE_SUBSCRIPTION_CACHE_TAG
     */
    public const FUTURE_SUBSCRIPTION_CACHE_TAG = "ebizcharge_future";

    /**
     * @const FUTURE_SUBSCRIPTION_TABLE_NAME
     */
    public const FUTURE_SUBSCRIPTION_TABLE_NAME = "ebizcharge_recurring_dates";

    /**
     *
     * @const EBIZCHARGE_FUTURE_SUBSCRIPTION_STATUS_PENDING
     */
    public const EBIZCHARGE_FUTURE_SUBSCRIPTION_STATUS_PENDING = 0;

    /**
     * @const  EBIZCHARGE_FUTURE_SUBSCRIPTION_STATUS_COMPLETED
     */
    public const EBIZCHARGE_FUTURE_SUBSCRIPTION_STATUS_COMPLETED = 1;

    /**
     * @const EBIZCHARGE_FUTURE_SUBSCRIPTION_STATUS_FAILED
     */
    public const EBIZCHARGE_FUTURE_SUBSCRIPTION_STATUS_FAILED = 2;

    /**
     * @const EBIZCHARGE_FUTURE_SUBSCRIPTION_STATUS_PENDING_TITLE
     */
    public const EBIZCHARGE_FUTURE_SUBSCRIPTION_STATUS_PENDING_TITLE = "Pending";

    /**
     * @const EBIZCHARGE_FUTURE_SUBSCRIPTION_STATUS_COMPLETED_TITLE
     */
    public const EBIZCHARGE_FUTURE_SUBSCRIPTION_STATUS_COMPLETED_TITLE = "Completed";

    /**
     * @const EBIZCHARGE_FUTURE_SUBSCRIPTION_STATUS_FAILED_TITLE
     */
    public const EBIZCHARGE_FUTURE_SUBSCRIPTION_STATUS_FAILED_TITLE = "Failed";

    /**
     * @const EBIZCHARGE_FUTURE_SUBSCRIPTION_STATUSES
     */
    public const EBIZCHARGE_FUTURE_SUBSCRIPTION_STATUSES = [
        self::EBIZCHARGE_FUTURE_SUBSCRIPTION_STATUS_PENDING => self::EBIZCHARGE_FUTURE_SUBSCRIPTION_STATUS_PENDING_TITLE,
        self::EBIZCHARGE_FUTURE_SUBSCRIPTION_STATUS_COMPLETED => self::EBIZCHARGE_FUTURE_SUBSCRIPTION_STATUS_COMPLETED_TITLE,
        self::EBIZCHARGE_FUTURE_SUBSCRIPTION_STATUS_FAILED => self::EBIZCHARGE_FUTURE_SUBSCRIPTION_STATUS_FAILED_TITLE
    ];

    /**
     * Defining the ENTITY_ID
     *
     * @const ENTITY_ID
     */
    public const ENTITY_ID = 'entity_id';

    /**
     * Recurring ID Definition
     *
     * @const  RECURRING_ID
     */
    public const RECURRING_ID = 'recurring_id';

    /**
     * Defining Recurring Date
     *
     * @const RECURRING_DATE
     */
    public const RECURRING_DATE = 'recurring_date';

    /**
     * Customer Id
     *
     * @const: CUSTOMER_ID
     */
    public const CUSTOMER_ID = 'customer_id';

    /**
     * Store Id
     *
     * @const: STORE_ID
     */
    public const STORE_ID = 'store_id';

    /**
     * Ordered Quantity
     *
     * @const: ORDERED_QTY
     */
    public const ORDERED_QTY = 'ordered_qty';

    /**
     * Ordered Product Final Price
     *
     * @const: ORDERED_PRODUCT_FINAL_PRICE
     */
    public const ORDERED_PRODUCT_FINAL_PRICE = 'ordered_product_final_price';

    /**
     * Ordered Product Id
     *
     * @const: ORDERED_PRODUCT_ID
     */
    public const ORDERED_PRODUCT_ID = 'ordered_product_id';

    /**
     * Coupon Code
     *
     * @const: COUPON_CODE
     */
    public const COUPON_CODE = 'coupon_code';

    /**
     * Discount Amount
     *
     * @const: DISCOUNT
     */
    public const DISCOUNT = 'discount';

    /**
     * Ordered Status
     *
     * @const: ORDERED_STATUS
     */
    public const ORDERED_STATUS = 'ordered_status';

    /**
     * Ordered Remarks
     *
     * @const: REMARKS
     */
    public const REMARKS = 'remarks';

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
     * @const GRAND_TOTAL
     */
    public const AMOUNT = "amount";
    /**
     * @const GRAND_TOTAL
     */
    public const SHIPPING_AMOUNT = "shipping_amount";
    /**
     * const TAX_AMOUNT
     */
    public const TAX_AMOUNT = "tax_amount";
    /**
     * @cont ITEM_PRICE
     */
    public const ITEM_PRICE = "item_price";
    /**
     * @const ITEM_PRICE
     */
    public const SURCHARGE_AMOUNT = "surcharge_amount";
    /**
     * @const SUBTOTAL
     */
    public const SUBTOTAL = "subtotal";
    /**
     * @const GRAND_TOTAL
     */
    public const GRAND_TOTAL = "grand_total";


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
     * @return FutureSubscriptionInterface
     */
    public function setEntityId($entityId);

    /**
     * Set Recurring Id
     *
     * @param int $recurring_id
     * @return FutureSubscriptionInterface
     */
    public function setRecurringId(int $recurring_id): FutureSubscriptionInterface;

    /**
     * Get Recurring ID
     *
     * @return int
     */
    public function getRecurringId(): int;

    /**
     * Set Recurring Date
     *
     * @param mixed $recurring_date
     * @return FutureSubscriptionInterface
     */
    public function setRecurringDate($recurring_date): FutureSubscriptionInterface;

    /**
     * Get Recurring Date
     *
     * @return string
     */
    public function getRecurringDate(): string;

    /**
     * Get Customer Id
     *
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * Set Customer Id
     *
     * @param int $customerId
     * @return FutureSubscriptionInterface
     */
    public function setCustomerId(int $customerId): FutureSubscriptionInterface;

    /**
     * Get Store Id
     *
     * @return int
     */
    public function getStoreId(): int;

    /**
     * Set Store Id
     *
     * @param int $storeId
     * @return FutureSubscriptionInterface
     */
    public function setStoreId(int $storeId): FutureSubscriptionInterface;

    /**
     * Get ordered Qty
     *
     * @return float
     */
    public function getOrderedQty(): float;

    /**
     * Set Ordered Quantity
     *
     * @param float $orderedQty
     * @return FutureSubscriptionInterface
     */
    public function setOrderedQty(float $orderedQty): FutureSubscriptionInterface;

    /**
     * Get Ordered Product id
     *
     * @return int
     */
    public function getOrderedProductId(): int;

    /**
     * Set Ordered Product Id
     *
     * @param int $orderedProductId
     * @return FutureSubscriptionInterface
     */
    public function setOrderedProductId(int $orderedProductId): FutureSubscriptionInterface;

    /**
     * Get Ordered Final Price
     *
     * @return float
     */
    public function getOrderedProducFinalPrice(): float;

    /**
     * Set Ordered Product Final Price
     *
     * @param int $orderedProductFinalPrice
     * @return FutureSubscriptionInterface
     */
    public function setOrderedProducFinalPrice(int $orderedProductFinalPrice): FutureSubscriptionInterface;

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
     * @return FutureSubscriptionInterface
     */
    public function setCouponCode(string $couponCode): FutureSubscriptionInterface;

    /**
     * Get Discount
     *
     * @return float
     */
    public function getDiscount(): float;

    /**
     * Set Discount
     *
     * @param float $discount
     * @return FutureSubscriptionInterface
     */
    public function setDiscount(float $discount): FutureSubscriptionInterface;

    /**
     * Get Ordered Status
     *
     * @return string
     */
    public function getOrderedStatus(): string;

    /**
     * Set Ordered Status
     *
     * @param string $orderStatus
     * @return FutureSubscriptionInterface
     */
    public function setOrderedStatus(string $orderStatus): FutureSubscriptionInterface;

    /**
     * Get Remarks
     *
     * @return string
     */
    public function getRemarks(): string;

    /**
     * Set Remarks
     *
     * @param string $remarks
     * @return FutureSubscriptionInterface
     */
    public function setRemarks(string $remarks): FutureSubscriptionInterface;

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
     * @return FutureSubscriptionInterface
     */
    public function setCreatedAt($createdAt): FutureSubscriptionInterface;

    /**
     * Updated At
     *
     * @param mixed $updatedAt
     * @return FutureSubscriptionInterface
     */
    public function setUpdatedAt($updatedAt): FutureSubscriptionInterface;

    /**
     * Get Updated At
     *
     * @return mixed
     */
    public function getUpdatedAt();
}

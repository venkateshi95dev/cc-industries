<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Api\Data;

/**
 * DiscountGroup Amount Interface.
 */
interface I95DevDiscountGroupInterface
{
    public const DISCOUNT_GROUP_AMOUNT = 'discount_group_amount';
    public const BASE_DISCOUNT_GROUP_AMOUNT = 'base_discount_group_amount';

    /**
     * Return the discount group amount.
     *
     * @return float|null base discount group amount. Otherwise, null.
     */
    public function getDiscountGroupAmount();

    /**
     * Set the discount group amount.
     *
     * @param float $discountGroupAmount
     * @return $this
     */

    public function setDiscountGroupAmount($discountGroupAmount);

    /**
     * Return the base discount group amount.
     *
     * @return float|null base discount group amount. Otherwise, null.
     */
    public function getBaseDiscountGroupAmount();

    /**
     * Set the discount group amount.
     *
     * @param float $baseDiscountGroupAmount
     * @return $this
     */
    public function setBaseDiscountGroupAmount($baseDiscountGroupAmount);
}

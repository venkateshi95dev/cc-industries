<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Model\Data;

use I95DevConnect\DiscountGroups\Api\Data\I95DevDiscountGroupInterface;

class I95DevDiscountGroup implements I95DevDiscountGroupInterface
{
    /**
     * @inheritdoc
     */
    public function getDiscountGroupAmount()
    {
        return $this->getData(self::DISCOUNT_GROUP_AMOUNT);
    }

    /**
     * @inheritdoc
     */
    public function setDiscountGroupAmount($discountGroupAmount)
    {
        return $this->setData(self::DISCOUNT_GROUP_AMOUNT, $discountGroupAmount);
    }

    /**
     * @inheritdoc
     */
    public function getBaseDiscountGroupAmount()
    {
        return $this->getData(self::BASE_DISCOUNT_GROUP_AMOUNT);
    }

    /**
     * @inheritdoc
     */
    public function setBaseDiscountGroupAmount($baseDiscountGroupAmount)
    {
        return $this->setData(self::BASE_DISCOUNT_GROUP_AMOUNT, $baseDiscountGroupAmount);
    }
}

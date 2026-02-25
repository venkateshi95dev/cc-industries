<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Forward;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class responsible for preparing order discount data which will be added in order result to ERP
 */
class DiscountEntity
{
    /**
     * Returns discount data from order
     *
     * @param OrderInterface $order
     * @throws LocalizedException
     * @return array
     * @createdBy SravaniPolu
     */
    public function getOrderDiscount($order)
    {
        try {
            if ($order->getBaseDiscountAmount()) {
                $discountEntity['discountType'] = 'discount';
                $discountEntity['discountAmount'] = (float)(abs($order->getBaseDiscountAmount()));
                $discountEntity['discountCode'] = $order->getCouponCode();
                $discountData[] = $discountEntity;
            } else {
                $discountData = [];
            }

            return $discountData;
        } catch (LocalizedException $ex) {
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
    }
}

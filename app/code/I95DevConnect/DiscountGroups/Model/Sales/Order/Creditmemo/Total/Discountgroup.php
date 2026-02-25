<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Model\Sales\Order\Creditmemo\Total;

use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

class Discountgroup extends AbstractTotal
{
    /**
     * Collect customer balance totals for credit memo
     *
     * @param Creditmemo $creditmemo
     * @return $this
     */
    public function collect(Creditmemo $creditmemo)
    {
        // phpcs:disable
        $creditmemo->setBaseDiscountGroupAmount(0);
        $creditmemo->setDiscountGroupAmount(0);
        $totalDiscountAmount = 0;
        $baseTotalDiscountAmount = 0;
        foreach ($creditmemo->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            if ($orderItem->isDummy()) {
                continue;
            }

            $orderItemDiscount = (double) $orderItem->getDiscountGroupAmount();
            $baseOrderItemDiscount = (double) $orderItem->getBaseDiscountGroupAmount();
            $orderItemQty = $orderItem->getQtyOrdered();
            if ($orderItemDiscount && $orderItemQty) {
                $discount = $creditmemo->roundPrice(
                    $orderItemDiscount / $orderItemQty * $item->getQty(),
                    'regular',
                    true
                );
                $baseDiscount = $creditmemo->roundPrice(
                    $baseOrderItemDiscount / $orderItemQty * $item->getQty(),
                    'base',
                    true
                );
                $item->setDiscountGroupAmount($discount);
                $item->setBaseDiscountGroupAmount($baseDiscount);
                $totalDiscountAmount += $discount;
                $baseTotalDiscountAmount += $baseDiscount;
            }
        }

        $creditmemo->setDiscountGroupAmount($totalDiscountAmount);
        $creditmemo->setBaseDiscountGroupAmount($baseTotalDiscountAmount);
        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $totalDiscountAmount);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseTotalDiscountAmount);
        return $this;
        // phpcs:enable
    }
}

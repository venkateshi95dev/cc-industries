<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Model\Sales\Order\Invoice\Total;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Item;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

class Discountgroup extends AbstractTotal
{
    /**
     * Set discount group amount to invoice items
     *
     * @param Invoice $invoice
     * @return $this
     */
    public function collect(Invoice $invoice)
    {
        // phpcs:disable
        $invoice->setDiscountGroupAmount(0);
        $invoice->setBaseDiscountGroupAmount(0);
        $totalDiscountAmount = 0;
        $baseTotalDiscountAmount = 0;

        /** @var $item Item */
        foreach ($invoice->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            if ($orderItem->isDummy()) {
                continue;
            }

            $orderItemDiscount = (double) $orderItem->getDiscountGroupAmount();
            $baseOrderItemDiscount = (double) $orderItem->getBaseDiscountGroupAmount();
            $orderItemQty = $orderItem->getQtyOrdered();
            if ($orderItemDiscount && $orderItemQty) {
                $discount = $invoice->roundPrice($orderItemDiscount / $orderItemQty * $item->getQty(), 'regular', true);
                $baseDiscount =
                    $invoice->roundPrice($baseOrderItemDiscount / $orderItemQty * $item->getQty(), 'base', true);
                $item->setDiscountGroupAmount($discount);
                $item->setBaseDiscountGroupAmount($baseDiscount);
                $totalDiscountAmount += $discount;
                $baseTotalDiscountAmount += $baseDiscount;
            }
        }

        $invoice->setDiscountGroupAmount($totalDiscountAmount);
        $invoice->setBaseDiscountGroupAmount($baseTotalDiscountAmount);
        $invoice->setGrandTotal($invoice->getGrandTotal() + $totalDiscountAmount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseTotalDiscountAmount);
        return $this;
        // phpcs:enable
    }
}

<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Observer\Forward;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class DGAmountToOrder implements ObserverInterface
{
    /**
     * Send Discount group amount in order request
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $currentObject = $observer->getEvent()->getData("order");
        if (isset($currentObject->InfoData)) {
            $i = 0;
            foreach ($currentObject->order->getItems() as $orderItem) {
                if ($orderItem->getProductType() == 'simple') {
                    if ($orderItem->getParentItem() !== null) {
                        $itemDiscountAmount =
                            $orderItem->getParentItem()->getDiscountGroupAmount();
                        $currentObject->InfoData['orderItems'][$i]['discount'][] = [
                            'discountAmount' => -$itemDiscountAmount,
                            'discountType' => 'discount',
                        ];
                    } else {
                        $itemDiscountAmount = $orderItem->getDiscountGroupAmount();
                        $currentObject->InfoData['orderItems'][$i]['discount'][] = [
                            'discountAmount' => -$itemDiscountAmount,
                            'discountType' => 'discount',
                        ];
                    }
                }
                $i++;
            }

            if ($currentObject->order->getExtensionAttributes() !== null) {
                $currentObject->InfoData['discount'][] = ['discountType' => 'discount',
                    'discountAmount' => -$currentObject->order->getDiscountGroupAmount()];
            }
        }
    }
}

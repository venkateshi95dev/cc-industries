<?php

namespace Crimson\Checkout\Model\Service;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Class IsOrderDropship
 * @package Crimson\Checkout\Model\Service
 */
class IsOrderDropship
{

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function doesCartHaveDropships(OrderInterface $order): bool
    {
        foreach ($order->getAllItems() as $item) {
            $product = $item->getProduct();

            if ($product->getTypeId() == Configurable::TYPE_CODE) {
                continue;
            }

            $dropShip = ($product->getCustomAttribute('ships_from_manufacturer')
                ? (bool) $product->getCustomAttribute('ships_from_manufacturer')->getValue() : false);
            if ($dropShip) {
                return true;
            }
        }

        return false;
    }
}

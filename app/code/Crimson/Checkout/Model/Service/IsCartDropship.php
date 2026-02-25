<?php

namespace Crimson\Checkout\Model\Service;

use Magento\Quote\Api\Data\CartInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Quote\Model\Quote\Item;

/**
 * Class IsCartDropship
 * @package Crimson\Checkout\Model\Service
 */
class IsCartDropship
{

    /**
     * @param CartInterface|null $quote
     * @return bool
     */
    public function doesCartHaveDropships(CartInterface $quote = null): bool
    {
        foreach ($quote->getAllItems() as $item) {
            /** @var Item $item */
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

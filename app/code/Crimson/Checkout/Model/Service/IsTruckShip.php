<?php

namespace Crimson\Checkout\Model\Service;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Quote\Model\Quote\Item;

/**
 * Class IsTruckShip
 * @package Crimson\Checkout\Model\Service
 */
class IsTruckShip
{
    /**
     * @var ProductResource
     */
    protected $productResource;

    /**
     * IsTruckShip constructor.
     *
     * @param ProductResource $productResource
     */
    public function __construct(
        ProductResource $productResource
    ) {
        $this->productResource = $productResource;
    }

    /**
     * @param CartInterface|null $quote
     * @return bool
     * @throws LocalizedException
     */
    public function doesCartHaveIsTruckShip(CartInterface $quote = null): bool
    {
        foreach ($quote->getAllItems() as $item) {
            /** @var Item $item */
            $product = $item->getProduct();

            if ($product->getTypeId() == Configurable::TYPE_CODE) {
                continue;
            }

            if ($product->getCustomAttribute('truckship')
                && $product->getCustomAttribute('truckship')->getValue()) {
                $optionValueId = $product->getCustomAttribute('truckship')->getValue();
                $attribute     = $this->productResource->getAttribute('truckship');
                $isTruckShip   = $attribute->getSource()->getOptionText($optionValueId);
                if ($isTruckShip == 'Yes') {
                    return true;
                }
            }
        }

        return false;
    }
}

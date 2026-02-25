<?php

namespace Crimson\Catalog\ViewModel;

use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class ProductData implements ArgumentInterface
{
    const ATTRIBUTE_PRODUCT_GROUP_CODE = 'productgroupcode';

    protected array $hideOutOfStockForGroups = [
        '005'
    ];

    /**
     * To determine if a product is a service product.
     * Normally their SKU starts with "S-"
     *
     * @param Product $product
     * @return bool
     */
    public function isService(Product $product): bool
    {
        return stripos($product->getSku(), 'S-') === 0;
    }

    /**
     * To determine if a product is in group(s).
     *
     * @param Product $product
     * @param array $groups
     * @return bool
     */
    public function isInGroups(Product $product, array $groups): bool
    {
        $group = $product->getData(self::ATTRIBUTE_PRODUCT_GROUP_CODE);
        return !empty($group) && count($groups) && in_array($group, $groups);
    }

    /**
     * ZIP-1933 - logic to determine whether the "Out of Stock" message should be displayed.
     *
     * @param Product $product
     * @return bool
     */
    public function shouldHideOutOfStock(Product $product): bool
    {
        return $this->isService($product) && $this->isInGroups($product, $this->hideOutOfStockForGroups);
    }
}

<?php

namespace Crimson\Catalog\Service;

use Magento\Catalog\Model\Product;

class ServiceProduct
{

    const ATTRIBUTE_PRODUCT_GROUP_CODE = 'productgroupcode';
    const HIDE_OOS_GROUPS              = ['005'];

    public function is(Product $product): bool
    {
        if (!$this->isSkuServiceProduct($product->getSku())) {
            return false;
        }

        if (!$this->isInGroups($product)) {
            return false;
        }

        return true;
    }

    private function isInGroups(Product $product): bool
    {
        $group = $product->getData(self::ATTRIBUTE_PRODUCT_GROUP_CODE);
        return in_array($group, self::HIDE_OOS_GROUPS);
    }

    private function isSkuServiceProduct(string $sku): bool
    {
        return stripos($sku, 'S-') === 0;
    }
}

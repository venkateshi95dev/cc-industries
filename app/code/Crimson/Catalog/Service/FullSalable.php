<?php

namespace Crimson\Catalog\Service;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;

class FullSalable
{

    public function is(Product|ProductInterface $product): bool
    {
        $stockData = $product->getData('quantity_and_stock_status');
        return ($stockData['qty'] ?? 0) > 0;
    }
}

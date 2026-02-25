<?php

namespace Crimson\CatalogOutOfStockPreventor\Plugin\Magento\CatalogInventory\Model\ResourceModel\Stock\Item;

use Magento\CatalogInventory\Model\ResourceModel\Stock\Item;

class PreventOutOfStockStatus
{
    public function beforeSave(Item $subject, $stockItem) 
    {
        $stockItem->setIsInStock(1);
    }
}
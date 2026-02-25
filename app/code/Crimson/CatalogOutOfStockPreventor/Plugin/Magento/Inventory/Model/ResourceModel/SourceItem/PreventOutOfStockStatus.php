<?php

namespace Crimson\CatalogOutOfStockPreventor\Plugin\Magento\Inventory\Model\ResourceModel\SourceItem;

use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

class PreventOutOfStockStatus
{
    public function beforeSave(SourceItem $subject, $sourceItem) 
    {
        $sourceItem->setStatus(SourceItemInterface::STATUS_IN_STOCK);
    }
}
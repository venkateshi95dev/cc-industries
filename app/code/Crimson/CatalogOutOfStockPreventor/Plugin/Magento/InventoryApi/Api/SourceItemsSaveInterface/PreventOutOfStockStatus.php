<?php

namespace Crimson\CatalogOutOfStockPreventor\Plugin\Magento\InventoryApi\Api\SourceItemsSaveInterface;

use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

class PreventOutOfStockStatus
{
    public function beforeExecute(SourceItemsSaveInterface $subject, $sourceItems) 
    {
        foreach ($sourceItems as $sourceItem) {
            $sourceItem->setStatus(SourceItemInterface::STATUS_IN_STOCK);
        }
    }
}
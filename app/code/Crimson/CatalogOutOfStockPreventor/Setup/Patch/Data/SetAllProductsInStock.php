<?php

namespace Crimson\CatalogOutOfStockPreventor\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

class SetAllProductsInStock implements DataPatchInterface
{
    /** @var \Magento\Framework\Setup\ModuleDataSetupInterface */
    private $moduleDataSetup;

    /** @var \Magento\Inventory\Model\ResourceModel\SourceItem */
    private $inventorySourceItemResource;

    /** @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Item */
    private $stockItemResource;

    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Inventory\Model\ResourceModel\SourceItem $inventorySourceItemResource,
        \Magento\CatalogInventory\Model\ResourceModel\Stock\Item $stockItemResource
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->inventorySourceItemResource = $inventorySourceItemResource;
        $this->stockItemResource = $stockItemResource;
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $connection = $this->stockItemResource->getConnection();

        $inventorySourceItemTable = $this->inventorySourceItemResource->getMainTable();
        $connection->query("UPDATE $inventorySourceItemTable SET status=" . SourceItemInterface::STATUS_IN_STOCK);

        $stockItemTable = $this->stockItemResource->getMainTable();
        $connection->query("UPDATE $stockItemTable SET is_in_stock=1");

        $this->moduleDataSetup->endSetup();
    }

    public function getAliases() 
    { 
        return [];
    }

    public static function getDependencies() 
    {
        return []; 
    }
}
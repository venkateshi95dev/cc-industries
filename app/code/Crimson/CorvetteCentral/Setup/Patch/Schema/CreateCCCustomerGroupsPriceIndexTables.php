<?php

namespace Crimson\CorvetteCentral\Setup\Patch\Schema;

use Crimson\CorvetteCentral\Setup\Patch\Data\AddCCCustomerGroups;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CreateCCCustomerGroupsPriceIndexTables implements DataPatchInterface
{

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {}

    public function apply(): void
    {
        $setup      = $this->moduleDataSetup;
        $connection = $setup->getConnection();
        $setup->startSetup();
        // Suffixes for each price-index table
        $suffixes = ['', '_tmp', '_idx', '_replica'];
        // Loop customer group IDs 31 through 43
        for ($groupId = 31; $groupId <= 43; $groupId++) {
            $newPrefix = 'catalog_product_index_price_cg' . $groupId;
            foreach ($suffixes as $suffix) {
                $newTable = $setup->getTable($newPrefix . $suffix);
                // Drop if exists
                if ($connection->isTableExists($newTable)) {
                    $connection->dropTable($newTable);
                }
            }

            // Recreate base tables by cloning cg1 skeleton
            $connection->query(
                sprintf(
                    'CREATE TABLE %s LIKE %s',
                    $setup->getTable("catalog_product_index_price_cg{$groupId}"),
                    $setup->getTable('catalog_product_index_price_cg1')
                )
            );
            $connection->query(
                sprintf(
                    'CREATE TABLE %s LIKE %s',
                    $setup->getTable("catalog_product_index_price_cg{$groupId}_replica"),
                    $setup->getTable('catalog_product_index_price_cg1_replica')
                )
            );
        }

        $setup->endSetup();
    }

    public function getAliases(): array
    {
        return [];
    }

    public static function getDependencies(): array
    {
        return [
            AddCCCustomerGroups::class
        ];
    }
}

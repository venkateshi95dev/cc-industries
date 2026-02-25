<?php
namespace Silk\Coker\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $connection = $installer->getConnection();
        if ($defaultStoreId = $connection->fetchOne('select min(store_id) from store where store_id>1;')) {
            $installer->startSetup();

            // tables 
            $tables = ['adspace'];
            // column structure
            $storeIdStructure = [
                'type'=>\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'length'=>10,
                'unsigned'=>true,
                'nullable'=>false,
                'default'=>$defaultStoreId,
                'comment'=>'Store Id'
            ];
            // add column
            foreach ($tables as $table) {
                $table = $installer->getTable($table);
                if ($installer->tableExists($table) && !$connection->tableColumnExists($table, 'store_id')) {
                    $connection->addColumn($table, 'store_id', $storeIdStructure);
                }
            }
            $connection->modifyColumn('magestore_bannerslider_banner', 'status', [
                'type'=>\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'length'=>5,
                'unsigned'=>true,
                'nullable'=>false,
                'default'=>2,
                'comment'=>'Banner status',
            ]);
            $installer->endSetup();
        }
    }
}

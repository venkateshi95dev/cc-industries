<?php
/**
 * @namespace   Crimson
 * @module      Shipping
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        01/23/2019
 */
namespace Crimson\Shipping\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {

            /**
             * Create table 'shipping_tablerate_twoday'
             */
            if (!$installer->tableExists('shipping_tablerate_twoday')) {
                $tableShippingTablerateTwoday = $installer->getConnection()->newTable(
                    $installer->getTable('shipping_tablerate_twoday')
                )->addColumn(
                    'pk',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity'  => true,
                        'unsigned'  => true,
                        'nullable'  => false,
                        'primary'   => true,
                    ],
                    'Primary Key'
                )->addColumn(
                    'website_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable'  => false,
                        'default'   => '0',
                    ],
                    'Website Id'
                )->addColumn(
                    'dest_country_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    4,
                    [
                        'nullable'  => false,
                        'default'   => '0',
                    ],
                    'Destination coutry ISO/2 or ISO/3 code'
                )->addColumn(
                    'dest_region_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable'  => false,
                        'default'   => '0',
                    ],
                    'Destination Region Id'
                )->addColumn(
                    'dest_zip',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    10,
                    [
                        'nullable'  => false,
                        'default'   => '*',
                    ],
                    'Destination Post Code (Zip)'
                )->addColumn(
                    'condition_name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    20,
                    [
                        'nullable'  => false,
                    ],
                    'Rate Condition name'
                )->addColumn(
                    'condition_value',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [
                        'nullable'  => false,
                        'default'   => '0.0000',
                    ],
                    'Rate condition value'
                )->addColumn(
                    'price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [
                        'nullable'  => false,
                        'default'   => '0.0000',
                    ],
                    'Price'
                )->addColumn(
                    'cost',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [
                        'nullable'  => false,
                        'default'   => '0.0000',
                    ],
                    'Cost'
                )->addIndex(
                    $installer->getIdxName(
                        $installer->getTable('shipping_tablerate_twoday'),
                        ['website_id', 'dest_country_id', 'dest_region_id', 'dest_zip', 'condition_name', 'condition_value'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['website_id', 'dest_country_id', 'dest_region_id', 'dest_zip', 'condition_name', 'condition_value'],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE ]
                )->setComment('Shipping Tablerate Two-day');

                $installer->getConnection()->createTable($tableShippingTablerateTwoday);
            }

            /**
             * Create table 'shipping_tablerate_surepost'
             */
            if (!$installer->tableExists('shipping_tablerate_surepost')) {
                $tableShippingTablerateSurepost = $installer->getConnection()->newTable(
                    $installer->getTable('shipping_tablerate_surepost')
                )->addColumn(
                    'pk',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity'  => true,
                        'unsigned'  => true,
                        'nullable'  => false,
                        'primary'   => true,
                    ],
                    'Primary Key'
                )->addColumn(
                    'website_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable'  => false,
                        'default'   => '0',
                    ],
                    'Website Id'
                )->addColumn(
                    'dest_country_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    4,
                    [
                        'nullable'  => false,
                        'default'   => '0',
                    ],
                    'Destination coutry ISO/2 or ISO/3 code'
                )->addColumn(
                    'dest_region_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable'  => false,
                        'default'   => '0',
                    ],
                    'Destination Region Id'
                )->addColumn(
                    'dest_zip',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    10,
                    [
                        'nullable'  => false,
                        'default'   => '*',
                    ],
                    'Destination Post Code (Zip)'
                )->addColumn(
                    'condition_name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    20,
                    [
                        'nullable'  => false,
                    ],
                    'Rate Condition name'
                )->addColumn(
                    'condition_value',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [
                        'nullable'  => false,
                        'default'   => '0.0000',
                    ],
                    'Rate condition value'
                )->addColumn(
                    'price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [
                        'nullable'  => false,
                        'default'   => '0.0000',
                    ],
                    'Price'
                )->addColumn(
                    'cost',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [
                        'nullable'  => false,
                        'default'   => '0.0000',
                    ],
                    'Cost'
                )->addIndex(
                    $installer->getIdxName(
                        $installer->getTable('shipping_tablerate_surepost'),
                        ['website_id', 'dest_country_id', 'dest_region_id', 'dest_zip', 'condition_name', 'condition_value'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['website_id', 'dest_country_id', 'dest_region_id', 'dest_zip', 'condition_name', 'condition_value'],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE ]
                )->setComment('Shipping Tablerate Surepost');

                $installer->getConnection()->createTable($tableShippingTablerateSurepost);
            }
        }

        $installer->endSetup();
    }

}
<?php

namespace Crimson\Brand\Setup;

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
        $setup->startSetup();

	    if (version_compare($context->getVersion(), '1.0.1', '<')) {

            $connection = $setup->getConnection();
            $tableName = $setup->getTable('crimson_brand_brand');
            /**
             * Add 'display' column to 'crimson_brand_brand' table
             */
            $connection->addColumn(
                $tableName, 'display', array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    'nullable'  => true,
                    'default'   => true,
                    'comment'   => 'Display Yes/No the Brand info on PDP.',
                    'after'     => 'cms_id'
                )
            );

        }

        $setup->endSetup();
    }
}

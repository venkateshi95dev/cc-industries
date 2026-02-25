<?php
/**
 * @namespace   Crimson
 * @module      SalesRule
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        2/1/2019
 * @brief
 */
namespace Crimson\SalesRule\Setup;

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
            $tableName = $setup->getTable('salesrule');
            /**
             * Add 'free_shipping_methods' column to 'salesrule' table
             */
            $connection->addColumn(
                $tableName, 'free_shipping_methods', array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable'  => true,
                    'comment'   => 'Methods that should be made free.',
                    'after'     => 'simple_free_shipping'
                )
            );

        }

        $setup->endSetup();
    }
}

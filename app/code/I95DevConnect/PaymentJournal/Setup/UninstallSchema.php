<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PaymentJournal
 */

namespace I95DevConnect\PaymentJournal\Setup;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

/**
 * Interface for handling data removal during module uninstall
 *
 * @api
 */
class UninstallSchema implements UninstallInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConn;

    /**
     * UninstallSchema constructor.
     * @param ResourceConnection $resourceConn
     */
    public function __construct(
        ResourceConnection $resourceConn
    ) {
        $this->resourceConn = $resourceConn;
    }

    /**
     * Invoked when remove-data flag is set during module uninstall.
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @author Hrusikesh Manna
     */
    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {

        $setup->startSetup();
        $this->dropCustomTable($setup);
        $connection = $this->resourceConn->getConnection();
        $connection->delete(
            $setup->getTable('setup_module'),
            ['module = ?' => 'I95DevConnect_PaymentJournal']
        );

        if ($setup->getConnection()->isTableExists($setup->getTable("patch_list"))) {
            $connection->delete(
                $setup->getTable('patch_list'),
                [
                    'patch_name = ?' => 'I95DevConnect\PaymentJournal\Setup\Patch\Data\EntitiesData'
                ]
            );
        }

        $setup->endSetup();
    }

    /**
     * Drop connector table during uninstallation
     *
     * @param SchemaSetupInterface $setup
     * @author Hrusikesh Manna
     */
    public function dropCustomTable($setup)
    {
        $path = [
            'i95dev_payment_journal'
        ];

        foreach ($path as $configPath) {
            if ($setup->getConnection()->isTableExists($setup->getTable($configPath))) {
                $setup->getConnection()->dropTable($setup->getTable($configPath));
            }
        }
    }
}

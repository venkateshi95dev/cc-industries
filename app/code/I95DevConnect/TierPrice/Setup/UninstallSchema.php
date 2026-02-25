<?php

/**
 * @author    Subhan
 * @copyright Copyright (c) 2022 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_TierPrice
 */

namespace I95DevConnect\TierPrice\Setup;

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
     *
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
     * @param  SchemaSetupInterface   $setup
     * @param  ModuleContextInterface $context
     * @author i95Dev Team
     */
    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {

        $setup->startSetup();

        $connection = $this->resourceConn->getConnection();
        $connection->delete(
            $setup->getTable('setup_module'),
            ['module = ?' => 'I95DevConnect_TierPrice']
        );

        if ($setup->getConnection()->isTableExists($setup->getTable("patch_list"))) {
            $connection->delete(
                $setup->getTable('patch_list'),
                [
                    'patch_name = ?' => 'I95DevConnect\TierPrice\Setup\Patch\Data\Entitiesdata'
                ]
            );
        }

        $setup->endSetup();
    }
}

<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_CancelOrder
 */

namespace I95DevConnect\CancelOrder\Setup;

use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

/**
 * class for removing cancel order module entry from db
 *
 * @api
 */
class UninstallSchema implements UninstallInterface
{
    /**
     * @var ConfigInterface
     */
    public $resourceConfig;

    /**
     * @var ResourceConnection
     */
    public $resourceConn;

    /**
     * UninstallSchema constructor.
     *
     * @param ConfigInterface    $resourceConfig
     * @param ResourceConnection $resourceConn
     */
    public function __construct(
        ConfigInterface $resourceConfig,
        ResourceConnection $resourceConn
    ) {

        $this->resourceConfig = $resourceConfig;
        $this->resourceConn = $resourceConn;
    }

    /**
     * Invoked when remove-data flag is set during module uninstall.
     *
     * @param  SchemaSetupInterface   $setup
     * @param  ModuleContextInterface $context
     * @return void
     */
    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {

        $setup->startSetup();

        $this->resourceConfig->deleteConfig(
            'i95devconnect_CancelOrder/cancelorder_enabled_settings/enable_cancelorder'
        );

        $connection = $this->resourceConn->getConnection();
        $connection->delete(
            $setup->getTable('setup_module'),
            [
                'module = ?' => 'I95DevConnect_CancelOrder'
            ]
        );

        if ($setup->getConnection()->isTableExists($setup->getTable("patch_list"))) {
            $connection->delete(
                $setup->getTable('patch_list'),
                [
                    'patch_name = ?' => 'I95DevConnect\CancelOrder\Setup\Patch\Data\Entitiesdata'
                ]
            );
        }

        $setup->endSetup();
    }
}

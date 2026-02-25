<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PaymentMapping
 */

namespace I95DevConnect\PaymentMapping\Setup;

use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
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
     * @param ConfigInterface $resourceConfig
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
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {

        $setup->startSetup();
        $this->deleteMQConfigurations();

        $connection = $this->resourceConn->getConnection();

        $connection->delete(
            $setup->getTable('setup_module'),
            ['module = ?' => 'I95DevConnect_PaymentMapping']
        );

        $setup->getConnection()->dropTable($setup->getTable('i95dev_payment_mapping_list'));

        if ($setup->getConnection()->isTableExists($setup->getTable("patch_list"))) {
            $connection->delete(
                $setup->getTable('patch_list'),
                [
                    'patch_name = ?' => 'I95DevConnect\PaymentMapping\Setup\Patch\Data\Paymentlist'
                ]
            );
        }

        $setup->endSetup();
    }

    /**
     * Delete message queue configuration entries during un-installation
     */
    public function deleteMQConfigurations()
    {
        $path = [
            'i95dev_adapter_configurations/i95dev_payment_mapping/enabled'
        ];

        foreach ($path as $configPath) {
            $this->resourceConfig->deleteConfig(
                $configPath
            );
        }
    }
}

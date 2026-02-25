<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Setup;

use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use Magento\Store\Model\StoreManagerInterface;

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
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * Constructor for DI
     * @param ConfigInterface $resourceConfig
     * @param ResourceConnection $resourceConn
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ConfigInterface $resourceConfig,
        ResourceConnection $resourceConn,
        StoreManagerInterface $storeManager
    ) {

        $this->resourceConfig = $resourceConfig;
        $this->resourceConn = $resourceConn;
        $this->storeManager = $storeManager;
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
        $this->deleteConfigurations();

        $connection = $this->resourceConn->getConnection();
        /**
         * @updatedBy vinayakrao.shetkar, replaced with Magento Standard Query
         */
        $connection->delete(
            $setup->getTable('setup_module'),
            ['module = ?' => 'I95DevConnect_CloudConnect']
        );

        $setup->endSetup();
    }

    /**
     * Delete configurations
     */
    public function deleteConfigurations()
    {
        $path = [
            'i95dev_adapter_configurations/enabled_disabled/token',
            'i95dev_adapter_configurations/enabled_disabled/enabled',
            'i95dev_adapter_configurations/enabled_disabled/target_url',
            'i95dev_adapter_configurations/enabled_disabled/client_id',
            'i95dev_adapter_configurations/enabled_disabled/subscription_key',
            'i95dev_adapter_configurations/enabled_disabled/endpoint_code',
            'i95dev_adapter_configurations/enabled_disabled/instance_type',
            'i95dev_adapter_configurations/enabled_disabled/logs_enabled',
            'i95dev_adapter_configurations/enabled_disabled/crmerp'
        ];

        foreach ($path as $configPath) {
            $this->resourceConfig->deleteConfig(
                $configPath
            );
        }
    }
}

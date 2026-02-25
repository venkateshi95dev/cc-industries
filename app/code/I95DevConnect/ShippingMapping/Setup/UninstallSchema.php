<?php

/**
 * @author Arushi Bansal
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_ShippingMapping
 */

namespace I95DevConnect\ShippingMapping\Setup;

use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * class for removing shipping mapping module entry from db
 *
 * @api
 */
class UninstallSchema implements \Magento\Framework\Setup\UninstallInterface
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
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     */
    public function uninstall(
        \Magento\Framework\Setup\SchemaSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {

        $setup->startSetup();

        $this->deleteMQConfigurations();

        $setup->getConnection()->dropTable($setup->getTable('i95dev_shipping_mapping_list'));

        $connection = $this->resourceConn->getConnection();
        $connection->delete(
            $setup->getTable('setup_module'),
            ['module = ?' => 'I95DevConnect_ShippingMapping']
        );

        if ($setup->getConnection()->isTableExists($setup->getTable("patch_list"))) {
            $connection->delete(
                $setup->getTable('patch_list'),
                [
                    'patch_name = ?' => 'I95DevConnect\ShippingMapping\Setup\Patch\Data\Shippinglist'
                ]
            );
        }

        $setup->endSetup();
    }

    /**
     * Delete messagequeue configuration entries during uninstallation
     */
    protected function deleteMQConfigurations()
    {
        $path = [
            'i95dev_adapter_configurations/i95dev_shipping_mapping/enabled'
        ];

        foreach ($path as $configPath) {
            $this->resourceConfig->deleteConfig(
                $configPath
            );
        }
    }
}

<?php

/**
 * Copyright ? Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace I95DevConnect\ConfigurableProducts\Setup;

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
     * UninstallSchema constructor.
     *
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
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     */
    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {

        $setup->startSetup();

        $connection = $this->resourceConn->getConnection();
        $connection->delete(
            $setup->getTable('setup_module'),
            ['module = ?' => 'I95DevConnect_ConfigurableProducts']
        );

        $this->resourceConfig->deleteConfig(
            'configurableproducts/i95dev_enabled_settings/is_enabled'
        );
        
        if ($setup->getConnection()->isTableExists($setup->getTable("patch_list"))) {
            $connection->delete(
                $setup->getTable('patch_list'),
                [
                    'patch_name = ?' => 'I95DevConnect\ConfigurableProducts\Setup\Patch\Data\CategoryAttributes'
                ]
            );

            $connection->delete(
                $setup->getTable('patch_list'),
                [
                    'patch_name = ?' => 'I95DevConnect\ConfigurableProducts\Setup\Patch\Data\EntitiesNAttributes'
                ]
            );
        }

        $setup->endSetup();
    }
}

<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Setup;

use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

/**
 * Class for delete configuration from DB during un installation
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
     * Constructer for DI
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
     * @author Hrusikesh Manna
     */
    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
        if ($setup->getConnection()->isTableExists($setup->getTable('i95dev_pricelevels'))) {
            $setup->getConnection()->dropTable($setup->getTable('i95dev_pricelevels'));
        }
        if ($setup->getConnection()->isTableExists($setup->getTable('i95dev_erp_pricelevel_price'))) {
            $setup->getConnection()->dropTable($setup->getTable('i95dev_erp_pricelevel_price'));
        }
        $connection = $this->resourceConn->getConnection();
        $this->deleteMQConfigurations();
        $connection->delete(
            $setup->getTable('setup_module'),
            ['module = ?' => 'I95DevConnect_PriceLevel']
        );

        if ($setup->getConnection()->isTableExists($setup->getTable("patch_list"))) {
            $connection->delete(
                $setup->getTable('patch_list'),
                [
                    'patch_name = ?' => 'I95DevConnect\PriceLevel\Setup\Patch\Data\EntitiesNAttributes'
                ]
            );
        }
        
        $setup->endSetup();
    }

    /**
     * Delete Extension Configuratio Dusring Uninstall
     *
     * @author Hrusikesh Manna
     */
    public function deleteMQConfigurations()
    {
        $this->resourceConfig->deleteConfig(
            'i95dev_pricelevel/active_display/enabled'
        );
    }
}

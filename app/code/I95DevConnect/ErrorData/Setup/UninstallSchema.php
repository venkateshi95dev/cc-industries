<?php
/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_ErrorData
 */

namespace I95DevConnect\ErrorData\Setup;

/**
 * Interface for handling data removal during module uninstall
 *
 * @api
 */
class UninstallSchema implements \Magento\Framework\Setup\UninstallInterface
{

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    public $resourceConn;

    /**
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    public $resourceConfig;

    /**
     * UninstallSchema constructor.
     *
     * @param \Magento\Framework\App\ResourceConnection $resourceConn
     * @param \Magento\Framework\App\Config\ConfigResource\ConfigInterface $resourceConfig
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConn,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $resourceConfig
    ) {
        $this->resourceConn = $resourceConn;
        $this->resourceConfig = $resourceConfig;
    }

    /**
     * Invoked when remove-data flag is set during module uninstall.
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     *
     * @return void
     */
    public function uninstall(
        \Magento\Framework\Setup\SchemaSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        $setup->startSetup();
        $connection = $this->resourceConn->getConnection();
        $this->deleteMQConfigurations();
        $connection->delete(
            $setup->getTable('setup_module'),
            ['module = ?' => 'I95DevConnect_ErrorData']
        );
        $setup->endSetup();
    }

    /**
     * Delete all ErrorData configuration
     */
    public function deleteMQConfigurations()
    {

        $this->resourceConfig->deleteConfig(
            'i95devconnect_errors/reports_enabled_settings/report'
        );
        $this->resourceConfig->deleteConfig(
            'i95devconnect_errors/reports_enabled_settings/report_entities'
        );
        $this->resourceConfig->deleteConfig(
            'i95devconnect_errors/reports_enabled_settings/report_type'
        );
    }
}

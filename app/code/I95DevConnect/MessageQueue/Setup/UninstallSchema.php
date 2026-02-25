<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Setup;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Interface for handling data removal during module uninstall
 *
 * @createdBy Arushi Bansal
 */
class UninstallSchema implements UninstallInterface {

    /**
     * @var CustomerSetupFactory
     */
    public $customerSetupFactory;

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
     * Invoked when remove-data flag is set during module uninstall
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    // @codingStandardsIgnoreFile
    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        $this->dropMQTable($setup);
        $this->deleteMQConfigurations();

        $connection = $this->resourceConn->getConnection();

        /**
         * @updatedBy vinayakrao.shetkar, replaced with Magento Standard Query
         */
        $connection->delete(
                $setup->getTable('setup_module'), ['module = ?' => 'I95DevConnect_MessageQueue']
        );

        if ($setup->getConnection()->isTableExists($setup->getTable("patch_list"))) {
            $connection->delete(
                $setup->getTable('patch_list'),
                [
                    'patch_name = ?' => 'I95DevConnect\MessageQueue\Setup\Patch\Data\PlatinumEntities'
                ]
            );

            $connection->delete(
                $setup->getTable('patch_list'),
                [
                    'patch_name = ?' => 'I95DevConnect\MessageQueue\Setup\Patch\Data\PlatinumInitial'
                ]
            );
        }

        $setup->endSetup();
    }

    /**
     * Delete messagequeue configuration entries during uninstallation
     */
    function deleteMQConfigurations() {
        $path = [
            'i95dev_messagequeue/I95DevConnect_credentials/token',
            'i95dev_messagequeue/I95DevConnect_generalcontact/username',
            'i95dev_messagequeue/I95DevConnect_generalcontact/email_sent',
            'i95dev_messagequeue/I95DevConnect_settings/capture_invoice',
            'i95dev_messagequeue/I95DevConnect_settings/component',
            'i95dev_messagequeue/i95dev_extns/packet_size',
            'i95dev_messagequeue/i95dev_extns/enabled',
            'i95dev_messagequeue/I95DevConnect_notifications/email_notifications',
            'i95dev_messagequeue/I95DevConnect_notifications/order_totalmismatch',
            'i95dev_messagequeue/I95DevConnect_generalcontact/email_sent',
            'i95dev_messagequeue/I95DevConnect_generalcontact/username',
            'i95dev_messagequeue/I95DevConnect_logsettings/max_log_size',
            'i95dev_messagequeue/I95DevConnect_logsettings/log_clean_days',
            'i95dev_messagequeue/I95DevConnect_logsettings/debug',
            'i95dev_messagequeue/I95DevConnect_mqsettings/mqdata_clean_days',
            'i95dev_messagequeue/I95DevConnect_mqsettings/retry_limit',
            'i95dev_messagequeue/I95DevConnect_settings/customer_group',
            'i95dev_messagequeue/I95DevConnect_settings/attribute_set',
            'i95dev_messagequeue/I95DevConnect_settings/attribute_group'
        ];

        foreach ($path as $configPath) {
            $this->resourceConfig->deleteConfig(
                $configPath
            );
        }
    }

    /**
     * Drop connector table during uninstallation
     * @param $setup
     */
    function dropMQTable($setup) {
        $path = [
            'i95dev_magento_messagequeue',
            'i95dev_erp_messagequeue',
            'i95dev_erp_data',
            'i95dev_error_report',
            'i95dev_entity'
        ];

        foreach ($path as $configPath) {
            if ($setup->getConnection()->isTableExists($setup->getTable($configPath))) {
                $setup->getConnection()->dropTable($setup->getTable($configPath));
            }
        }
    }

}

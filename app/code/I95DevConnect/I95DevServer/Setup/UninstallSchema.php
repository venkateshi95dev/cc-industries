<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Setup;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

/**
 * Class for handling i95devServer module entry removal from DB
 *
 * @api
 */
class UninstallSchema implements UninstallInterface
{
    /**
     * @var ResourceConnection
     */
    public $resourceConn;

    /**
     * Constructor for DI
     *
     * @param     ResourceConnection $resourceConn
     * @createdBy Arushi Bansal
     */
    public function __construct(
        ResourceConnection $resourceConn
    ) {
        $this->resourceConn = $resourceConn;
    }

    /**
     * Remove i95devServer module from setup_module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     * @createdBy Arushi Bansal
     */
    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {

        $setup->startSetup();
        $connection = $this->resourceConn->getConnection();
        /**
         * @updatedBy vinayakrao.shetkar, replaced with Magento Standard Query
         */
        $connection->delete(
            $setup->getTable('setup_module'),
            ['module = ?' => 'I95DevConnect_I95DevServer']
        );

        $setup->endSetup();
    }
}

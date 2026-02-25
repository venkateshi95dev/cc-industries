<?php

/**
 * Copyright ? Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace I95DevConnect\Splitshipment\Setup;

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
    public $resourceConn;

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
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {

        $setup->startSetup();

        $connection = $this->resourceConn->getConnection();
        $connection->delete(
            $setup->getTable('setup_module'),
            ['module = ?' => 'I95DevConnect_Splitshipment']
        );

        $setup->endSetup();
    }
}

<?php
/**
 * Century Business Solutions
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the centurybizsolutions.com license that is
 * available through the URL:  https://www.centurybizsolutions/License.txt
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file to upgrade this extension to newer
 * version in the future please contact to CENTURY BUSINESS SOLUTIONS.
 *
 * @category    Ebizcharge
 * @package     Ebizcharge_Ebizcharge
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Sync Assets Resource Model class
 *
 * Class SyncAssets
 */
class SyncAssets extends AbstractDb
{
    /**
     * EBIZCHARGE_SYNC_TABLE_NAME
     *
     * @const EBIZCHARGE_SYNC_TABLE_NAME
     */
    public const EBIZCHARGE_SYNC_TABLE_NAME = 'ebizcharge_sync_assets_cron';

    /**
     * @var ResourceConnection
     */
    protected $_resources;

    /**
     * @param Context $context
     * @param ResourceConnection $resource
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        ResourceConnection $resource,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);

        /** @var  _resources */
        $this->_resources = $resource;
    }

    /**
     * Load By Process Code
     *
     * @param string $processCode
     * @return string
     * @throws LocalizedException
     */
    public function loadByProcessCode(string $processCode)
    {
        /** @var $mainTable */
        $mainTable = $this->getTableName();
        /** @var $connection */
        $connection = $this->_resources->getConnection();
        /** @var $where */
        $where = $connection->quoteInto(\Ebizcharge\Ebizcharge\Model\SyncAssets::PROCESS_CODE . " = ?", $processCode);
        $select = $connection->select()->from($mainTable, ['entity_id'])->where($where);

        return $connection->fetchOne($select);
    }

    /**
     * Get Table Name
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTableName()
    {
        $connection = $this->_resources->getConnection();
        $tablePrefix = $this->_resources->getTablePrefix();
        /** @var $mainTable */
        $mainTable = $connection->getTableName($this->getMainTable());

        if ($tablePrefix && strpos($mainTable, $tablePrefix) !== 0) {
            $mainTable = $tablePrefix . $mainTable;
        }

        return $mainTable;
    }

    /**
     * Construct Method
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::EBIZCHARGE_SYNC_TABLE_NAME, 'entity_id');
    }
}

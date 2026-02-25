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

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Resource Model for ebizcharge_recurring_dates table
 *
 * Class OrderSubscription
 */
class OrderSubscription extends AbstractDb
{
    /**
     * Recurring Order Table Name
     *
     * @const: EBIZCHARGE_RECURRING_ORDER
     */
    public const EBIZCHARGE_RECURRING_ORDER = 'ebizcharge_recurring_order';

    /**
     * Ebizcharge Entity Id
     *
     * @const EBIZCHARGE_ENTITY_ID
     */
    public const EBIZCHARGE_ENTITY_ID = 'entity_id';

    /**
     * @var ResourceConnection
     */
    protected $_resources;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @param Context $context
     * @param ResourceConnection $resource
     * @param EbizchargeLogger $ebizchargeLogger
     * @param string $connectionName
     */
    public function __construct(
        Context            $context,
        ResourceConnection $resource,
        EbizchargeLogger   $ebizchargeLogger,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        /** @var  _resources */
        $this->_resources = $resource;
        /** @var  _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
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
        $this->_init(self::EBIZCHARGE_RECURRING_ORDER, self::EBIZCHARGE_ENTITY_ID);
    }
}

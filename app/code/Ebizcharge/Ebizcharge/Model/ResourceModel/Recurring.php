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

use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Resource Model for ebizcharge_recurring table
 *
 * Class Recurring
 */
class Recurring extends AbstractDb
{
    /**
     * Recurring Table Name
     *
     * @const: RECURRING_TABLE_NAME
     */
    public const RECURRING_TABLE_NAME = 'ebizcharge_recurring';

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
     * Load Recurring By Order Id
     *
     * @param mixed $mageOrderId
     * @return string
     * @throws LocalizedException
     */
    public function loadRecurringByOrderId($mageOrderId)
    {
        /** @var $connection */
        $connection = $this->_resources->getConnection();
        $mainTable = $this->getTableName();

        /** @var $where */
        $where = $connection->quoteInto(RecurringInterface::MAGE_ORDER_ID . " = ?", $mageOrderId);
        /** @var $select */
        $select = $connection->select()->from($mainTable, ['entity_id'])->where($where);

        return $connection->fetchOne($select);
    }

    /**
     * Load By Scheduled Payment Internal Id
     *
     * @param null|mixed $scheduledPaymentInternalId
     * @return string
     * @throws LocalizedException
     */
    public function loadByScheduledPaymentInternalId($scheduledPaymentInternalId = null)
    {
        /** @var $connection */
        $connection = $this->_resources->getConnection();
        $mainTable = $this->getTableName();
        /** @var $where */
        $where = $connection->quoteInto(
            RecurringInterface::EB_REC_SCHEDULED_PAYMENT_INTERNAL_ID . " = ?",
            $scheduledPaymentInternalId
        );
        /** @var $select */
        $select = $connection->select()->from($mainTable, ['entity_id'])->where($where);
        return $connection->fetchOne($select);
    }

    /**
     * Get Table Name
     *
     * @return string
     * @throws LocalizedException
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
        $this->_init('ebizcharge_recurring', 'entity_id');
    }
}

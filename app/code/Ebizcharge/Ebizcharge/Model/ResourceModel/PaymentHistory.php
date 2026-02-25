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

use Ebizcharge\Ebizcharge\Api\Data\PaymentHistoryInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Payment History Resource Manager
 *
 * Class PaymentHistory
 */
class PaymentHistory extends AbstractDb
{
    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var ResourceConnection
     */
    protected $_resources;

    /**
     * @param Context $context
     * @param EbizchargeLogger $ebizchargeLogger
     * @param ResourceConnection $resource
     * @param string $connectionName
     */
    public function __construct(
        Context            $context,
        EbizchargeLogger   $ebizchargeLogger,
        ResourceConnection $resource,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        /** @var  _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var  _resources */
        $this->_resources = $resource;
    }

    /**
     * Load By Ebizcharge Payment Ref Number
     *
     * @param string $ebizPaymentRefNumber
     * @return string
     * @throws LocalizedException
     */
    public function loadByPaymentRefNumber($ebizPaymentRefNumber = '')
    {
        $connection = $this->_resources->getConnection();
        $mainTable = $this->getTableName();

        /** @var $where */
        $where = $connection->quoteInto(
            PaymentHistoryInterface::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_REFERENCE_NUMBER . " = ?",
            $ebizPaymentRefNumber
        );
        /** @var $select */
        $select = $connection->select()->from($mainTable, ['entity_id'])->where($where);

        return $connection->fetchOne($select);
    }

    /**
     * Save Multiple Items
     *
     * @param array $paymentHistoryItems
     * @return bool|int
     */
    public function saveMultipleItems($paymentHistoryItems = [])
    {
        /** is payment Saved */
        $isPaymentSaved = false;

        try {
            $connection = $this->_resources->getConnection();
            $mainTable = $this->getTableName();
            $this->_ebizchargeLogger->addInfo(__(
                "Total payment history items added to the database " . count($paymentHistoryItems)
            ));
            $isPaymentSaved = $connection->insertMultiple($mainTable, $paymentHistoryItems);
            sprintf("Payments are saved: " . count($paymentHistoryItems));

        } catch (Exception $e) {
            $this->_ebizchargeLogger->addCritical(__(
                'Something went wrong while saving the data.' . $e->getMessage()
            ));
            // phpcs:ignore
            sprintf("Exception during saving payment history items Error: " . $e->getMessage());
        }
        return $isPaymentSaved;
    }

    /**
     * Main construct Method
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Ebizcharge\Ebizcharge\Model\PaymentHistory::PAYMENT_HISTORY_TABLE_NAME,
            'entity_id'
        );
    }

    /**
     * Get Table Name
     *
     * @return string
     * @throws LocalizedException
     */
    public function getTableName()
    {
        $mainTable = $this->getMainTable();

        $connection = $this->_resources->getConnection();
        $tablePrefix = $this->_resources->getTablePrefix();
        /** @var $mainTable */
        $mainTable = $connection->getTableName($this->getMainTable());

        if ($tablePrefix && strpos($mainTable, $tablePrefix) !== 0) {
            $mainTable = $tablePrefix . $mainTable;
        }

        return $mainTable;
    }
}

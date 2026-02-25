<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PaymentMapping
 */

namespace I95DevConnect\PaymentMapping\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * payment mapping resource model class
 */
class PaymentMapping extends AbstractDb
{
    /**
     * Payment mapping table and id setting
     */
    // @codingStandardsIgnoreStart
    protected function _construct()
    {
        $this->_init('i95dev_payment_mapping_list', 'id');
    }
    // @codingStandardsIgnoreEnd

    /**
     * Truncate table
     */
    // @codingStandardsIgnoreStart
    public function truncateTable()
    {
        $this->getConnection()->truncateTable($this->getTable('i95dev_payment_mapping_list'));
    }
    // @codingStandardsIgnoreEnd
}

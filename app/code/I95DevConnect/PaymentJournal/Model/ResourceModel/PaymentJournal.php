<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PaymentJournal
 */

namespace I95DevConnect\PaymentJournal\Model\ResourceModel;

/**
 * Payment Journal Resource Model
 */
class PaymentJournal extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Class construct
     *
     * @return void
     */
    // @codingStandardsIgnoreStart
    public function _construct()
    {
        $this->_init("i95dev_payment_journal", "id");
    }
    // @codingStandardsIgnoreEnd
}

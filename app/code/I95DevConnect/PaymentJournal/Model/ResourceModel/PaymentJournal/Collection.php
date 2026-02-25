<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PaymentJournal
 */

namespace I95DevConnect\PaymentJournal\Model\ResourceModel\PaymentJournal;

/**
 * Data Collection class
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Class construct
     *
     * @return void
     */
    // @codingStandardsIgnoreStart
    public function _construct()
    {
        $this->_init(
            "I95DevConnect\PaymentJournal\Model\PaymentJournal",
            "I95DevConnect\PaymentJournal\Model\ResourceModel\PaymentJournal"
        );
    }
    // @codingStandardsIgnoreEnd
}

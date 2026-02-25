<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PaymentMapping
 */

namespace I95DevConnect\PaymentMapping\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * i95dev payment mapping model
 */
class PaymentMapping extends AbstractModel
{
    /**
     * Initialize resource
     *
     * @return void
     */
    // @codingStandardsIgnoreStart
    protected function _construct()
    {
        $this->_init('I95DevConnect\PaymentMapping\Model\ResourceModel\PaymentMapping');
    }
    // @codingStandardsIgnoreEnd
}

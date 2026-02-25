<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PaymentMapping
 */

namespace I95DevConnect\PaymentMapping\Model\ResourceModel\PaymentMapping;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * I95dev Sales Invoice collection
 */
class Collection extends AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    // @codingStandardsIgnoreStart
    protected function _construct()
    {
        $this->_init(
            'I95DevConnect\PaymentMapping\Model\PaymentMapping',
            'I95DevConnect\PaymentMapping\Model\ResourceModel\PaymentMapping'
        );
    }
    // @codingStandardsIgnoreEnd
}

<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\ResourceModel\SalesInvoice;

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
    protected function _construct()// phpcs:ignore
    {
        $this->_init(
            'I95DevConnect\MessageQueue\Model\SalesInvoice',
            'I95DevConnect\MessageQueue\Model\ResourceModel\SalesInvoice'
        );
    }
}

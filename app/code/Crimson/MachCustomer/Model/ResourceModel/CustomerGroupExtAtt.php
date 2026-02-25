<?php

namespace Crimson\MachCustomer\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class CustomerGroupExtAtt
 * @package Crimson\MachCustomer\Model\ResourceModel
 */
class CustomerGroupExtAtt extends AbstractDb
{
    protected $_idFieldName = 'customer_group_id';

    protected $_isPkAutoIncrement = false;

    protected function _construct()
    {
        $this->_init('customer_group_extension_attributes','customer_group_id');
    }
}

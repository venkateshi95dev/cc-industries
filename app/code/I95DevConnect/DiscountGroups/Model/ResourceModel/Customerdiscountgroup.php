<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Customerdiscountgroup extends AbstractDb
{
    /**
     * Customerdiscountgroup constructor
     */
    protected function _construct()// phpcs:ignore
    {
        $this->_init('i95dev_customer_discount_group', 'id');
    }
}

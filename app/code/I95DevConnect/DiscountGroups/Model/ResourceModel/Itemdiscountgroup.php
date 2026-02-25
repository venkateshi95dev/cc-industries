<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Itemdiscountgroup extends AbstractDb
{
    /**
     * Itemdiscountgroup constructor
     */
    protected function _construct() // phpcs:ignore
    {
        $this->_init('i95dev_item_discount_group', 'id');
    }
}

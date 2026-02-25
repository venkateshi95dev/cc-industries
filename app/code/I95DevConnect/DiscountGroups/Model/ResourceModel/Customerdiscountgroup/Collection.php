<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Model\ResourceModel\Customerdiscountgroup;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Collection constructor
     */
    protected function _construct()// phpcs:ignore
    {
        $this->_init(
            'I95DevConnect\DiscountGroups\Model\Customerdiscountgroup',
            'I95DevConnect\DiscountGroups\Model\ResourceModel\Customerdiscountgroup'
        );
    }
}

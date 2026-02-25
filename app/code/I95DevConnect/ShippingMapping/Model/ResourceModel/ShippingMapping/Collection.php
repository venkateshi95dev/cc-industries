<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_ShippingMapping
 */

namespace I95DevConnect\ShippingMapping\Model\ResourceModel\ShippingMapping;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * I95dev Shipping Mapping collection
 */
class Collection extends AbstractCollection
{

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'I95DevConnect\ShippingMapping\Model\ShippingMapping',
            'I95DevConnect\ShippingMapping\Model\ResourceModel\ShippingMapping'
        );
    }
}

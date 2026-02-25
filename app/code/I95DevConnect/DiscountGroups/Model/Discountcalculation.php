<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Model;

use Magento\Framework\Model\AbstractModel;

class Discountcalculation extends AbstractModel
{
    /**
     * Define resource model
     **/
    protected function _construct() // phpcs:ignore
    {
        $this->_init('I95DevConnect\DiscountGroups\Model\ResourceModel\Discountcalculation');
    }
}

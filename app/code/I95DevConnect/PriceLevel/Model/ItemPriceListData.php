<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Model Class for Item Price List
 */
class ItemPriceListData extends AbstractModel
{
    /**
     * Class constructor to initialize the resource model
     */
    // @codingStandardsIgnoreStart
    protected function _construct()
    {
        $this->_init('I95DevConnect\PriceLevel\Model\ResourceModel\ItemPriceListData');
    }
    // @codingStandardsIgnoreEnd
}

<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Resource Model Class for Price Level
 */
class PriceLevelData extends AbstractDb
{
    /**
     * Class constructor to initialize the primary field of a table
     */
    // @codingStandardsIgnoreStart
    protected function _construct()
    {
        $this->_init('i95dev_pricelevels', 'pricelevel_id');
    }
    // @codingStandardsIgnoreEnd
}

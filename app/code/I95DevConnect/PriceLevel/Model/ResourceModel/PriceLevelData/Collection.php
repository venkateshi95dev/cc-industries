<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Model\ResourceModel\PriceLevelData;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Collection Class for Price Level
 */
class Collection extends AbstractCollection
{
    /**
     * Class constructor to initialize model & resource model
     */
    // @codingStandardsIgnoreStart
    protected function _construct()
    {
        $this->_init(
            'I95DevConnect\PriceLevel\Model\PriceLevelData',
            'I95DevConnect\PriceLevel\Model\ResourceModel\PriceLevelData'
        );
    }
    // @codingStandardsIgnoreEnd
}

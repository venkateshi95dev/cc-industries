<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Model\ResourceModel\ItemPriceListData;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Collection Class for Price List
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
            'I95DevConnect\PriceLevel\Model\ItemPriceListData',
            'I95DevConnect\PriceLevel\Model\ResourceModel\ItemPriceListData'
        );
    }
    // @codingStandardsIgnoreEnd
}

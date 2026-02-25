<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Resource Model Class for Price List
 */
class ItemPriceListData extends AbstractDb
{
    /**
     * Class constructor to initialize the primary field of a table
     */
    // @codingStandardsIgnoreStart
    protected function _construct()
    {
        $this->_init('i95dev_erp_pricelevel_price', 'id');
    }
    // @codingStandardsIgnoreEnd

    /**
     * Get Price List for all the SKU's available
     *
     * @return array
     * @throws LocalizedException
     */
    public function getRowsBySku()
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getMainTable());

        $result = [];
        foreach ($this->getConnection()->fetchAll($select) as $row) {
            $sku = strtolower($row['sku']);
            if (!isset($result[$sku])) {
                $result[$sku] = [];
            }
            $result[$sku][] = $row;
        }
        return $result;
    }
}

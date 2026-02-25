<?php
/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2022 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_Returns
 */

namespace I95DevConnect\Returns\Plugin\ResourceModel\CreditMemo;

use Magento\Framework\DB\Select;

class Grid
{
    /**
     * After search
     *
     * @param object $intercepter
     * @param array $collection
     * @return mixed
     */
    public function afterSearch($intercepter, $collection) //NOSONAR
    {
        if ($collection->getMainTable() === $collection->getConnection()->getTableName('sales_creditmemo_grid')) {
            $leftJoinTableName = $collection->getConnection()->getTableName('i95dev_magentorma_creditmemoids');
            $collection
                ->getSelect()
                ->joinLeft(
                    ['custom' => $leftJoinTableName],
                    "custom.magento_creditmemo_id  = main_table.increment_id",
                    [
                        'creditmemo_id' => 'custom.creditmemo_id'
                    ]
                );

            $where = $collection->getSelect()->getPart(Select::WHERE);
            $collection->getSelect()->setPart(Select::WHERE, $where)->group('main_table.entity_id');
        }
        return $collection;
    }
}

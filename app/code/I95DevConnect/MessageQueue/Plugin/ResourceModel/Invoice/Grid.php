<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2022 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Plugin\ResourceModel\Invoice;

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
        if ($collection->getMainTable() === $collection->getConnection()->getTableName('sales_invoice_grid')) {
            $leftJoinTableName = $collection->getConnection()->getTableName('i95dev_sales_flat_invoice');
            $collection
                ->getSelect()
                ->joinLeft(
                    ['custom' => $leftJoinTableName],
                    "custom.source_invoice_id  = main_table.increment_id",
                    [
                        'target_invoice_id' => 'custom.target_invoice_id'
                    ]
                );

            $where = $collection->getSelect()->getPart(Select::WHERE);
            $collection->getSelect()->setPart(Select::WHERE, $where)->group('main_table.entity_id');
        }
        if ($collection->getMainTable() === $collection->getConnection()->getTableName('sales_shipment_grid')) {
            $leftJoinTableName = $collection->getConnection()->getTableName('i95dev_sales_flat_shipment');
            $collection
                ->getSelect()
                ->joinLeft(
                    ['custom' => $leftJoinTableName],
                    "custom.source_shipment_id  = main_table.increment_id",
                    [
                        'target_shipment_id' => 'custom.target_shipment_id'
                    ]
                );

            $where = $collection->getSelect()->getPart(Select::WHERE);
            $collection->getSelect()->setPart(Select::WHERE, $where)->group('main_table.entity_id');
        }
        return $collection;
    }
}

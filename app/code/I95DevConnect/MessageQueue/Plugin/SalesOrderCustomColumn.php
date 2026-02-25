<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Plugin;

use Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as SalesOrderGridCollection;
use Closure;

class SalesOrderCustomColumn
{
    public function aroundGetReport(
        CollectionFactory $subject,
        Closure $proceed,
        $requestName
    ) {
        $collection = $proceed($requestName);
 
        if ($requestName !== 'sales_order_grid_data_source') {
            return $collection;
        }
 
        if (!$collection instanceof SalesOrderGridCollection) {
            return $collection;
        }
 
        $connection = $collection->getConnection();
        $select     = $collection->getSelect();
 
        // Prevent duplicate joins
        $from = $select->getPart('from');
        if (isset($from['i95_order'])) {
            return $collection;
        }
 
        $select->joinLeft(
            ['i95_order' => $connection->getTableName('i95dev_sales_flat_order')],
            'i95_order.source_order_id = main_table.increment_id',
            [
                'target_order_id' => 'i95_order.target_order_id',
                'origin'          => 'i95_order.origin'
            ]
                );
 
        return $collection;
    }
}
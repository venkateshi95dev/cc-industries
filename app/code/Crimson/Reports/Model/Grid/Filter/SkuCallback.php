<?php
declare(strict_types=1);

namespace Crimson\Reports\Model\Grid\Filter;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Framework\Data\Collection;
use Magento\Framework\DB\Select;

class SkuCallback
{
    public static function applyFilter(Collection $collection, Column $column): void
    {
        $value = $column->getFilter()->getValue();
        if ($value === null || trim($value) === '') {
            return;
        }
        $select = $collection->getSelect();
        if (!isset($select->getPart('from')['quote_item_table'])) {
            $itemTable = $collection->getTable('quote_item');
            $select->joinLeft(
                ['quote_item_table' => $itemTable],
                'main_table.entity_id = quote_item_table.quote_id',
                []
            );
        }
        $select->group('main_table.entity_id');

        // Prevent ambiguous column error (store_id)
        $where = $select->getPart(Select::WHERE);
        foreach ($where as $key => $condition) {
            if (strpos($condition, 'store_id') !== false) {
                $where[$key] = str_replace('`store_id`', '`main_table`.`store_id`', $condition);
            }
        }
        $select->setPart(Select::WHERE, $where);
        $select->having(
            'GROUP_CONCAT(quote_item_table.sku SEPARATOR ", ") LIKE ?',
            '%' . $value . '%'
        );
    }
}

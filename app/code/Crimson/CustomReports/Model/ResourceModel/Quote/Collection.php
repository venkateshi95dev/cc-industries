<?php
declare(strict_types=1);

namespace Crimson\CustomReports\Model\ResourceModel\Quote;

use Magento\Reports\Model\ResourceModel\Quote\Collection as OriginalCollection;

class Collection extends OriginalCollection
{
    protected array $activeFilters = [];

    public function addActiveFilter(string $field, $condition): void
    {
        $this->activeFilters[$field] = $condition;
    }

    public function resolveItemsData(): void
    {
        $quoteIds = $this->getColumnValues('quote_id');
        if (empty($quoteIds)) {
            return;
        }

        $itemTable = $this->getTable('quote_item');
        $select = $this->getConnection()->select()
            ->from(
                $itemTable,
                [
                    'quote_item_id' => 'item_id',
                    'quote_id',
                    'sku',
                    'qty',
                    'price',
                    'name',
                    'product_id',
                    'qty_on_hand_at_add',
                    'is_stock_at_add'
                ]
            )
            ->where('quote_id IN (?)', $quoteIds)
            ->where('parent_item_id IS NULL');

        foreach ($this->activeFilters as $field => $condition) {
            $select->where($this->getConnection()->prepareSqlCondition($field, $condition));
        }

        $allItemsData = $this->getConnection()->fetchAll($select);
        $newItems = [];
        $originalQuotesById = [];
        foreach ($this->getItems() as $quote) {
            $originalQuotesById[$quote->getQuoteId()] = $quote;
        }

        foreach ($allItemsData as $itemData) {
            $quoteId = $itemData['quote_id'];
            if (isset($originalQuotesById[$quoteId])) {
                $newRow = clone $originalQuotesById[$quoteId];
                $newRow->setData('sku', $itemData['sku']);
                $newRow->setData('name', $itemData['name']);
                $newRow->setData('qty', $itemData['qty']);
                $newRow->setData('price', $itemData['price']);
                $newRow->setData('product_id', $itemData['product_id']);
                $newRow->setData('items_qty', $itemData['qty']);
                $newRow->setData('qty_on_hand_at_add', $itemData['qty_on_hand_at_add']);
                $newRow->setData('is_stock_at_add', $itemData['is_stock_at_add']);
                $newRow->setData('subtotal', $itemData['price'] * $itemData['qty']);
                $newRow->setId($itemData['quote_item_id']);
                $newItems[] = $newRow;
            }
        }

        $this->_setIsLoaded(false);
        $this->removeAllItems();

        foreach ($newItems as $item) {
            $this->addItem($item);
        }

        $this->_totalRecords = count($newItems);
        $this->_setIsLoaded(true);
    }
}

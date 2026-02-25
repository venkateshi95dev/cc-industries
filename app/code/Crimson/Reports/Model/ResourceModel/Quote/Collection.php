<?php
declare(strict_types=1);

namespace Crimson\Reports\Model\ResourceModel\Quote;

use Magento\Reports\Model\ResourceModel\Quote\Collection as OriginalCollection;

class Collection extends OriginalCollection
{
    /**
     * Resolve SKUs data based on quote IDs.
     *
     * @return void
     */
    public function resolveSkus(): void
    {
        $quoteIds = $this->getColumnValues('quote_id');
        if (empty($quoteIds)) {
            return;
        }

        $itemTable = $this->getTable('quote_item');
        $select = $this->getConnection()->select()
            ->from(
                $itemTable,
                ['quote_id', 'skus' => new \Zend_Db_Expr('GROUP_CONCAT(sku SEPARATOR ", ")')]
            )
            ->where('quote_id IN (?)', $quoteIds)
            ->group('quote_id');

        $skuData = $this->getConnection()->fetchPairs($select);

        foreach ($this->getItems() as $item) {
            if (isset($skuData[$item->getData('quote_id')])) {
                $item->setData('skus', $skuData[$item->getData('quote_id')]);
            }
        }
    }
}

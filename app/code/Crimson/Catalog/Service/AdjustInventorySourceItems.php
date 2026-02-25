<?php

namespace Crimson\Catalog\Service;

use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item as StockItemResource;
use Magento\InventoryApi\Api\Data\SourceItemInterface as SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryImportExport\Model\Import\SourceItemConvert;
use Magento\InventoryIndexer\Indexer\InventoryIndexer;


/**
 * Class AdjustInventorySourceItems
 * @package Crimson\Catalog\Service
 */
class AdjustInventorySourceItems
{

    CONST PRODUCT_STATUS_ATTR_CODE = 'status';

    public function __construct(
        protected ResourceConnection $resourceConnection,
        protected ProductResource $productResource,
        protected StockItemResource $stockItemResource,
        protected DefaultSourceProviderInterface $defaultSourceProvider,
        protected SourceItemConvert $sourceItemConvert,
        protected SourceItemsSaveInterface $sourceItemsSave,
        protected IndexerRegistry $indexerRegistry,
        protected ProductAttributeRepositoryInterface $productAttributeRepositoryInterface
    ) {}

    public function execute(): void
    {
        $skusToAdjust = $this->_getSkusToAdjust();
        if (!$skusToAdjust) {
            return;
        }

        $itemDataRows = [];
        foreach ($skusToAdjust as $item) {

            if (!$this->_isValidRow($item)) {
                continue;
            }

            $itemDataRows[] = [
                SourceItemInterface::SOURCE_CODE => $this->defaultSourceProvider->getCode(),
                SourceItemInterface::SKU         => $item["sku"],
                SourceItemInterface::QUANTITY    => $item["qty"],
                SourceItemInterface::STATUS      => SourceItemInterface::STATUS_IN_STOCK,
            ];
        }

        if (!$itemDataRows) {
            return;
        }

        //inserting/updating source items
        $this->_insertBunch($itemDataRows);

        // reindexing the Inventory
        $this->_reindexInventory();
    }

    /**
     *
     */
    protected function _reindexInventory(): void
    {
        $indexer = $this->indexerRegistry->get(InventoryIndexer::INDEXER_ID);
        $indexer->invalidate();
    }

    /**
     * @param array $itemDataRows
     */
    protected function _insertBunch(array $itemDataRows): void
    {
        if (empty($itemDataRows)) {
            return;
        }

        try {
            // updating
            $sourceItems = $this->sourceItemConvert->convert($itemDataRows);
            $this->sourceItemsSave->execute($sourceItems);
        } catch (\Exception $e) {

        }
    }

    /**
     * @param array $itemData
     * @return bool
     */
    protected function _isValidRow(array $itemData): bool
    {
        if (!empty($itemData["sku"]) && $itemData["qty"] >= 0) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    protected function _getSkusToAdjust(): array
    {
        try {
            $attributeId = $this->getStatusAttrId();
            if (!$attributeId) {
                return [];
            }

            $saleableSkus = $this->_getAllSaleableSkus();

            $select = $this->productResource->getConnection()->select()
                ->from(['main_table' => $this->productResource->getTable('catalog_product_entity')])
                ->joinInner(
                    ['c_p_e_i' => $this->productResource->getTable('catalog_product_entity_int')],
                    $this->productResource->getConnection()->quoteInto('main_table.row_id = c_p_e_i.row_id AND c_p_e_i.attribute_id = ?', $attributeId))
                ->joinInner(
                    ['c_s_i' => $this->stockItemResource->getMainTable()],
                    'main_table.entity_id = c_s_i.product_id'
                    );

            if (!$saleableSkus) {
                $select->where('c_p_e_i.value = 1');
            } else {
                $select->where('c_p_e_i.value = 1 AND main_table.sku NOT IN (?)', $saleableSkus);
            }

            //clearing the select, just getting the columns we need, sku and qty
            $select->reset(\Zend_Db_Select::COLUMNS)
                ->columns(['main_table.sku', 'c_s_i.qty']);

            $data = $this->productResource->getConnection()->fetchAll($select);
            if (!$data) {
                $data = [];
            }

            return $data;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * @return array
     */
    protected function _getAllSaleableSkus(): array
    {
        $select = $this->resourceConnection->getConnection()->select()
            ->from(
                ['main_table' => $this->resourceConnection->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM)],
                'main_table.sku'
            )->where('main_table.status = 1');

        $data = $this->resourceConnection->getConnection()->fetchCol($select);
        if (!$data) {
            $data = [];
        }

        return $data;
    }

    /**
     * @return int|null
     */
    private function getStatusAttrId(): ?int
    {
        try {
            $attribute = $this->productAttributeRepositoryInterface->get(self::PRODUCT_STATUS_ATTR_CODE);

            return $attribute->getAttributeId();
        } catch (\Exception $e) {
            return null;
        }
    }
}

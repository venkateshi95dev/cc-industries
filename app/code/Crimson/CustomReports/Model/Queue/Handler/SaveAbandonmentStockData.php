<?php
declare(strict_types=1);

namespace Crimson\CustomReports\Model\Queue\Handler;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Quote\Model\ResourceModel\Quote\Item as QuoteItemResource;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Framework\App\ResourceConnection;

class SaveAbandonmentStockData
{
    public function __construct(
        protected StockRegistryInterface $stockRegistry,
        protected QuoteItemResource $quoteItemResource,
        protected LoggerInterface $logger,
        protected ItemFactory $quoteItemFactory,
        protected StoreManagerInterface $storeManager,
        protected ResourceConnection $resourceConnection
    ) {
    }

    public function execute(int $quoteItemId): void
    {
        try {
            $connection = $this->quoteItemResource->getConnection();
            $table = $this->quoteItemResource->getTable('quote_item');

            $select = $connection->select()
                ->from($table, ['item_id','product_id','store_id','parent_item_id'])
                ->where('item_id = ?', $quoteItemId);
            $row = $connection->fetchRow($select);

            if (!$row) {
                $this->logger->warning('AbandonmentReportBySku Consumer: Quote Item not found with ID: ' . $quoteItemId);
                return;
            }

            if (!empty($row['parent_item_id'])) {
                return;
            }

            $store = $this->storeManager->getStore((int)$row['store_id']);
            $websiteId = (int)$store->getWebsiteId();

            $stockItem = $this->stockRegistry->getStockItem((int)$row['product_id'], $websiteId);
            $qty = (float)$stockItem->getQty();
            $isInStock  = $qty > 0 ? 1 : 0;

            $connection->update(
                $table,
                ['qty_on_hand_at_add' => $qty, 'is_stock_at_add' => $isInStock],
                ['item_id = ?' => $quoteItemId]
            );
        } catch (\Exception $e) {
            $this->logger->critical(
                'AbandonmentReportBySku Consumer Error for quote_item_id ' . $quoteItemId . ': ' . $e->getMessage()
            );
        }
    }
}

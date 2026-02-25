<?php

namespace Crimson\MachProductsImport\Plugin\Model;

use Magento\CatalogImportExport\Model\StockItemImporter;

class StockItemImporterPlugin
{
    CONST IN_STOCK_COLUMN_CODE   = 'is_in_stock';
    CONST QTY_COLUMN_CODE        = 'qty';
    CONST BACKORDERS_COLUMN_CODE = 'backorders';

    /**
     * @param StockItemImporter $subject
     * @param array $stockData
     * @return array[]
     */
    public function beforeImport(StockItemImporter $subject, array $stockData): array
    {
        $stockImportData = array_map(
            function ($stockItemData) {
                $stockItemData[self::IN_STOCK_COLUMN_CODE] = 1;
                if ($stockItemData[self::QTY_COLUMN_CODE] > 0) {
                    $stockItemData[self::BACKORDERS_COLUMN_CODE] = 0;
                }

                return $stockItemData;
            },
            $stockData
        );

        return [$stockImportData];
    }
}

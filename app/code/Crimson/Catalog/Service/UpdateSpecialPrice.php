<?php

namespace Crimson\Catalog\Service;

use Crimson\Catalog\Model\Config;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item as StockItemResource;
use Magento\Store\Model\StoreManagerInterface;
use \Psr\Log\LoggerInterface as Logger;

class UpdateSpecialPrice
{

    CONST SPECIAL_PRICE_ATTR_CODE = 'special_price';

    public function __construct(
        protected ProductResource $productResource,
        protected ProductAttributeRepositoryInterface $productAttributeRepositoryInterface,
        protected StockItemResource $stockItemResource,
        protected StoreManagerInterface $storeManagerInterface,
        protected Logger $logger,
        protected Config $config,
    ) {}

    public function run(int $websiteId): void
    {
        try {
            $oosProductIds = $this->_getOOSProductsByWebsite($websiteId);
            if (!$oosProductIds) {
                return;
            }

            $this->_updateSpecialPrice($oosProductIds);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function runBySKUsAndWebsite(array $skus, int $websiteId): void
    {
        try {
            if (!$this->config->isSpecialPriceCronEnabled($websiteId)) {
                return;
            }

            if (empty($skus)) {
                return;
            }

            $oosProductIds = $this->_getOOSProductsBySKUsAndWebsite($skus, $websiteId);
            if (!$oosProductIds) {
                return;
            }

            $this->_updateSpecialPrice($oosProductIds);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    private function _updateSpecialPrice(array $oosRowIds): void
    {
        if (!$oosRowIds) {
            return;
        }

        $specialPriceAttrId = $this->_getSpecialPriceIdByCode();
        if (!$specialPriceAttrId) {
            return;
        }

        $connection = $this->productResource->getConnection();
        $connection->beginTransaction();
        $connection->delete(
            $this->productResource->getTable('catalog_product_entity_decimal'),
            $connection->quoteInto('attribute_id = ' . $specialPriceAttrId . ' AND row_id IN (?)', $oosRowIds)
        );
        $connection->commit();
    }

    private function _getOOSProductsBySKUsAndWebsite(array $skus, int $websiteId): array
    {
        $connection = $this->productResource->getConnection();
        $select = $connection
            ->select()
            ->from(['c_p_e' => $this->productResource->getTable('catalog_product_entity')])
            ->joinInner(
                ['c_p_w' => $this->productResource->getTable('catalog_product_website')],
                $connection->quoteInto('c_p_w.product_id = c_p_e.entity_id AND c_p_w.website_id = ?', $websiteId)
            )->joinInner(
                ['c_s_i' => $this->stockItemResource->getMainTable()],
                'c_p_e.entity_id = c_s_i.product_id AND c_s_i.qty < 1'
            )->where(
                'c_p_e.sku IN (?)', $skus
            )->reset(\Zend_Db_Select::COLUMNS)
            ->columns([
                'row_id'    => 'c_p_e.row_id'
            ]);

        $data = $connection->fetchCol($select);
        if (!$data) {
            $data = [];
        }

        return $data;
    }

    private function _getOOSProductsByWebsite(int $websiteId): array
    {
        $connection = $this->productResource->getConnection();
        $select = $connection
            ->select()
            ->from(['c_p_e' => $this->productResource->getTable('catalog_product_entity')])
            ->joinInner(
                ['c_p_w' => $this->productResource->getTable('catalog_product_website')],
                $connection->quoteInto('c_p_w.product_id = c_p_e.entity_id AND c_p_w.website_id = ?', $websiteId)
            )->joinInner(
                ['c_s_i' => $this->stockItemResource->getMainTable()],
                'c_p_e.entity_id = c_s_i.product_id AND c_s_i.qty < 1'
            )->reset(\Zend_Db_Select::COLUMNS)
            ->columns([
                'row_id'    => 'c_p_e.row_id'
            ]);

        $data = $connection->fetchCol($select);
        if (!$data) {
            $data = [];
        }

        return $data;
    }

    private function _getSpecialPriceIdByCode(): ?int
    {
        try {
            return $this->productAttributeRepositoryInterface
                ->get( self::SPECIAL_PRICE_ATTR_CODE)
                ->getAttributeId();
        } catch (\Exception $e) {
            return null;
        }
    }
}

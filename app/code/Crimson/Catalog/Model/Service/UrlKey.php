<?php
/**
 * @namespace   Crimson
 * @module      Catalog
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        5/17/2019 3:45 PM
 * @brief
 */

namespace Crimson\Catalog\Model\Service;

use Magento\Catalog\Model\Product\Url;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class UrlKey
 * @package Crimson\Catalog\Model\Service
 */
class UrlKey
{
    /**
     * @var ProductResource
     */
    protected $productResource;
    /**
     * @var Url
     */
    protected $productUrl;
    protected $productUrlSuffix;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        ProductResource $productResource,
        Url $productUrl,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->productResource = $productResource;
        $this->productUrl      = $productUrl;
        $this->scopeConfig     = $scopeConfig;
    }

    /**
     * @param string $sku
     * @param string $urlKey
     * @param array  $urlKeys - url key array in the structure of [$storeId => [...$urlKeys]]
     *
     * @param int    $storeId
     *
     * @return string - unique url key from DB and passed $urlKeys array
     */
    public function makeUniqueUrlKey(string $sku, string $urlKey, array $urlKeys = [], int $storeId = 1): string
    {
        $productUrlSuffix = $this->getProductUrlSuffix($storeId);
        $urlPath          = $urlKey . $productUrlSuffix;

        //get sku from database by url key.
        $dbSku = $this->_getDbSkuByUrlPath($sku, $urlPath);

        //if db sku and sku are the same, the given url key is valid.
        if ($sku === $dbSku) {
            return $urlKey;
        }

        /*
         * if they don't match we need to determine if we should return the current url key or append our sku to it.
         */
        //append the sku to the url key, this should all but guarantee uniqueness.
        $urlKeyWithSku = $urlKey . '-' . $this->productUrl->formatUrlKey($sku);

        // if the url key is already in the DB but not on this sku, then we add our sku.
        if ($dbSku !== null) {
            return $urlKeyWithSku;
        }

        //if the url key is not in the DB we check if another product in this batch has already claimed it.
        // if so, then we add our sku.
        if (!empty($urlKeys[$storeId][$urlPath]) && $urlKeys[$storeId][$urlPath] !== $sku) {
            return $urlKeyWithSku;
        }

        //if it isn't in the db and another product hasn't claimed it, we are good to go.
        return $urlKey;
    }

    /**
     * @param string $sku
     * @param string $urlPath
     *
     * @return string|null
     */
    protected function _getDbSkuByUrlPath(string $sku, string $urlPath): ?string
    {
        $connection = $this->productResource->getConnection();

        $select = $connection->select()->from(
            ['url_rewrite' => $this->productResource->getTable('url_rewrite')],
            []
        )->joinLeft(
            ['cpe' => $this->productResource->getTable('catalog_product_entity')],
            "cpe.entity_id = url_rewrite.entity_id", ['sku']
        )->where('request_path = ?', $urlPath)
            ->where('store_id IN (?)', [1])
            ->where('cpe.sku != ?', $sku);

        return $connection->fetchOne($select) ?: null;
    }

    /**
     * Retrieve product rewrite suffix for store
     *
     * @param int $storeId
     *
     * @return string
     * @since 100.0.3
     */
    protected function getProductUrlSuffix($storeId = null): string
    {
        if (!isset($this->productUrlSuffix[$storeId])) {
            $this->productUrlSuffix[$storeId] = $this->scopeConfig->getValue(
                ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }

        return $this->productUrlSuffix[$storeId];
    }
}

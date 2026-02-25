<?php
/**
 * @namespace   Crimson
 * @module      CatalogImportExport
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        5/24/2019 3:57 PM
 * @brief
*/

namespace Crimson\CatalogImportExport\Model;

use Crimson\Catalog\Model\Service\UrlKey;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ProductUrlPathGenerator
 * @package Crimson\CatalogImportExport\Model
 */
class ProductUrlPathGenerator extends \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator
{

    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        CategoryUrlPathGenerator $categoryUrlPathGenerator,
        ProductRepositoryInterface $productRepository,
        protected UrlKey $urlKey
    )
    {
        parent::__construct($storeManager, $scopeConfig, $categoryUrlPathGenerator, $productRepository);
    }

    protected function prepareProductUrlKey(Product $product): string
    {
        $urlKey = $product->getUrlKey();
        $urlKey = $urlKey === '' || $urlKey === null ? $product->getName() : $urlKey;
        $urlKey = $product->formatUrlKey($urlKey);

        return $this->urlKey->makeUniqueUrlKey($product->getSku(), $urlKey);
    }

    /**
     * Prepare URL Key with stored product data (fallback for "Use Default Value" logic)
     */
    protected function prepareProductDefaultUrlKey(Product $product): string
    {
        $storedProduct = $this->productRepository->getById($product->getId());
        $storedUrlKey = $storedProduct->getUrlKey();
        $urlKey = $storedUrlKey ?: $product->formatUrlKey($storedProduct->getName());

        return $this->urlKey->makeUniqueUrlKey($storedProduct->getSku(), $urlKey);
    }
}

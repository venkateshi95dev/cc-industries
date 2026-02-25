<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product Url model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Silk\Coker\Model\Product;

use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class Url extends \Magento\Catalog\Model\Product\Url
{
    public function getUrl(\Magento\Catalog\Model\Product $product, $params = [])
    {
        $routePath = '';
        $routeParams = $params;

        $storeId = $product->getStoreId();

        $categoryId = null;

        if (!isset($params['_ignore_category']) && $product->getCategoryId() && !$product->getDoNotUseCategoryId()) {
            $categoryId = $product->getCategoryId();
        }


        if ($product->hasUrlDataObject()) {
            $requestPath = $product->getUrlDataObject()->getUrlRewrite();
            $routeParams['_scope'] = $product->getUrlDataObject()->getStoreId();
        } else {
            $requestPath = $product->getRequestPath();

            if (empty($requestPath)) {
                $filterData = [
                    UrlRewrite::ENTITY_ID => $product->getId(),
                    UrlRewrite::ENTITY_TYPE => \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator::ENTITY_TYPE,
                    UrlRewrite::STORE_ID => $storeId,
                ];
                if ($categoryId) {
                    $filterData[UrlRewrite::METADATA]['category_id'] = $categoryId;
                }
                $rewrite = $this->urlFinder->findOneByData($filterData);
                if ($rewrite) {
                    $requestPath = $rewrite->getRequestPath();
                    $product->setRequestPath($requestPath);
                } else {
                    $filterData1 = [
                        UrlRewrite::ENTITY_ID => $product->getId(),
                        UrlRewrite::ENTITY_TYPE => \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator::ENTITY_TYPE,
                        UrlRewrite::STORE_ID => "1",
                    ];
                    if ($categoryId) {
                        $filterData1[UrlRewrite::METADATA]['category_id'] = $categoryId;
                    }
                    $rewrite1 = $this->urlFinder->findOneByData($filterData1);
                    if ($rewrite1) {
                        $requestPath = $rewrite1->getRequestPath();
                        $product->setRequestPath($requestPath);
                    } else {
                        $product->setRequestPath(false);
                    }
                }
            }
        }

        if (isset($routeParams['_scope'])) {
            $storeId = $this->storeManager->getStore($routeParams['_scope'])->getId();
        }

        if ($storeId != $this->storeManager->getStore()->getId()) {
            $routeParams['_scope_to_url'] = true;
        }

        if (!empty($requestPath)) {
            $routeParams['_direct'] = $requestPath;
        } else {
            $routePath = 'catalog/product/view';
            $routeParams['id'] = $product->getId();
            $routeParams['s'] = $product->getUrlKey();
            if ($categoryId) {
                $routeParams['category'] = $categoryId;
            }
        }

        // reset cached URL instance GET query params
        if (!isset($routeParams['_query'])) {
            $routeParams['_query'] = [];
        }

        return $this->getUrlFactory()->setScope($storeId)->getUrl($routePath, $routeParams);
    }

    public function getUrlFactory()
    {
        return $this->urlFactory->create();
    }
}

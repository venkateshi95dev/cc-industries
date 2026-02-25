<?php

namespace Crimson\ZipCokerWvConsolidation\Preference\Silk\Coker\Block\Product;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Crimson\ZipCokerWvConsolidation\Model\Config;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Url\Helper\Data;
use Magento\Catalog\Helper\Output as OutputHelper;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Block\Product\ListProduct as OriginalClass;

/**
 * Product list
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ListProduct extends \Silk\Coker\Block\Product\ListProduct
{

    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        Context                                $context,
        PostHelper                             $postDataHelper,
        Resolver                               $layerResolver,
        CategoryRepositoryInterface            $categoryRepository,
        RequestInterface                       $request,
        Collection                             $collection,
        ResourceConnection                     $resourceConnection,
        Data                                   $urlHelper,
        array                                  $data = [],
        ?OutputHelper                          $outputHelper = null
    )
    {
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $request,
            $collection,
            $resourceConnection,
            $urlHelper,
            $data,
            $outputHelper
        );
    }

    protected function _getProductCollection()
    {
        if (in_array($this->storeManager->getWebsite()->getCode(), [Config::ZIP_WEBSITE_CODE, CorvetteCentralStoreInterface::CORVETTE_CENTRAL_WEBSITE_CODE])) {
            return OriginalClass::_getProductCollection();
        }

        return parent::_getProductCollection();
    }
}

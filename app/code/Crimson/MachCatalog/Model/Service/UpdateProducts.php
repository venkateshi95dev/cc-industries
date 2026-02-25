<?php
/**
 * @namespace   Crimson
 * @module      MachCatalog
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/13/2019 3:01 PM
 * @brief       If product name changes, update url key.  Original task: ZIP-700
 */

namespace Crimson\MachCatalog\Model\Service;

use Crimson\MachBase\Model\MachConfig;
use Crimson\MachCatalog\Model\Service\UpdateProducts\ProcessorPool;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Action;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;
use Crimson\MachCatalog\Model\Config;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class UpdateProducts
 * @package Crimson\MachCatalog\Model\Service
 */
class UpdateProducts
{
    const ATTRIBUTE_UPDATED_FROM_MACH = 'updated_from_mach';

    protected ?int $websiteId = null;

    public function __construct(
        protected ProcessorPool $processorPool,
        protected ProductRepositoryInterface $productRepository,
        protected Action $productAction,
        protected SearchCriteriaBuilder $searchCriteriaBuilder,
        protected Config $machCatalogConfig,
        protected StoreManagerInterface $storeManager,
        protected LoggerInterface $logger
    ) {
        $this->websiteId = $this->storeManager->getWebsite(MachConfig::ZIP_WEBSITE_CODE)->getId();
    }

    /**
     * @throws LocalizedException
     */
    public function execute(): void
    {
        $searchResults = $this->_getProductsUpdatedByMach();
        if (!$searchResults->getTotalCount()) {
            $this->logger->debug(__('No products updated from MACH. Exciting.'));

            return;
        }

        if (!$this->websiteId) {
            $this->logger->debug(__('No website id detected. Exciting.'));

            return;
        }

        foreach ($searchResults->getItems() as $product) {
            $this->processorPool->process($product);
        }

        $this->_updateMachFlag($searchResults);
    }

    protected function _getProductsUpdatedByMach()
    {
        $poolLimit = $this->machCatalogConfig->getNumberOfProducts();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(self::ATTRIBUTE_UPDATED_FROM_MACH, 1)
            ->addFilter('website_id', $this->websiteId)
            ->setPageSize($poolLimit)
            ->create();

        return $this->productRepository->getList($searchCriteria);
    }

    protected function _updateMachFlag(SearchResultsInterface $productSearchResults)
    {
        $productIds = [];

        foreach ($productSearchResults->getItems() as $product) {
            $productIds[] = $product->getId();
        }

        $this->productAction->updateAttributes($productIds, [
            self::ATTRIBUTE_UPDATED_FROM_MACH => 0,
        ], Store::DEFAULT_STORE_ID);
    }
}

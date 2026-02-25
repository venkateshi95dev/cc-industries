<?php

namespace Crimson\BuyNow\Service;

use Crimson\BuyNow\Model\BuyNowConfig;
use Crimson\CokerWV\Api\CokerStoreInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\Filesystem\DirectoryList;
use Crimson\BuyNow\Model\Logger;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\File\Csv;
use Magento\Framework\UrlInterface;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;

class Exporter
{

    public function __construct(
        protected StoreRepositoryInterface    $storeRepository,
        protected SearchCriteriaInterface     $searchCriteriaInterface,
        protected SearchCriteriaBuilder       $searchCriteriaBuilder,
        protected ProductRepositoryInterface  $productRepositoryInterface,
        protected SortOrderBuilder            $sortOrderBuilder,
        protected Status                      $status,
        protected BuyNowConfig                $buyNowConfig,
        protected FileFactory                 $fileFactory,
        protected Csv                         $csvProcessor,
        protected DirectoryList               $directoryList,
        protected GetSalableQuantityDataBySku $salableQtyBySku,
        protected StockRegistryInterface      $stockRegistry,
        protected StoreManagerInterface       $storeManager,
        protected Logger                      $logger
    ) {}

    public function execute(): void
    {
        $cokerStore = $this->_getCokerStore();
        if (!$cokerStore) {
            return;
        }

        $brands = $this->buyNowConfig->getBrandFilter();
        if (!$brands) {
            return;
        }

        /** Add you header name here */
        $content[] = [
            'product_id'           => __('product_id'),
            'upc_code'             => __('upc_code'),
            'manufacturer_id_code' => __('manufacturer_id_code'),
            'product_name'         => __('product_name'),
            'price'                => __('price'),
            'currency'             => __('currency'),
            'image'                => __('image'),
            'url'                  => __('url'),
            'brand'                => __('brand'),
            'quantity'             => __('quantity'),
            'availability'         => __('availability'),
            'delivery_delay'       => __('delivery_delay'),
            'weight'               => __('weight'),
            'pack'                 => __('pack')
        ];

        $sortOrder = $this->sortOrderBuilder->setField('entity_id')->setDirection('DESC')->create();
        $this->searchCriteriaBuilder->addFilter('brand_item', $brands, 'in');
        $this->searchCriteriaBuilder->addFilter(ProductInterface::STATUS, $this->status->getVisibleStatusIds(), 'in');
        $this->searchCriteriaBuilder->addFilter('store_id', $cokerStore->getId());
        $this->searchCriteriaBuilder->setSortOrders([$sortOrder]);

        $productsSearch = $this->productRepositoryInterface->getList($this->searchCriteriaBuilder->create());
        if (!$productsSearch->getTotalCount()) {
            return;
        }

        $filePath =  $this->directoryList->getPath(DirectoryList::MEDIA) . "/" . BuyNowConfig::BUYNOW_CSV_FILENAME;
        $total = 0;
        foreach ($productsSearch->getItems() as $product) {

            /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
            $finalPrice = is_null($product->getFinalPrice()) ? '' : number_format($product->getFinalPrice(), '2', '.', '');
            $image      = $product->getImage() ? $cokerStore->getBaseUrl(UrlInterface::URL_TYPE_WEB) . 'media/catalog/product' . $product->getImage() : '';
            $weight     = number_format($product->getWeight() ?? 0.00, '2', '.', '');

            $content[] = [
                $product->getSku(),
                $product->getGtin() ?? '',
                $product->getMpn() ?? '',
                str_replace('|', '', $product->getName()),
                $finalPrice,
                'USD',
                $image,
                $product->getProductUrl(),
                $product->getResource()->getAttribute('brand_item')->getFrontend()->getValue($product),
                $product->getTypeId() == 'simple' ? $this->getSalableQty($product->getSku()) : '',
                $this->getStockStatus($product->getId()),
                $product->getDeliveryDelay(),
                $weight,
                $product->getPack() ?? ''
            ];
            $total++;
        }

        try {
            $this->csvProcessor
                ->setEnclosure('"')
                ->setDelimiter('|')
                ->appendData($filePath, $content)
            ;

            $this->fileFactory->create(
                BuyNowConfig::BUYNOW_CSV_FILENAME,
                [
                    'type'  => "filename",
                    'value' => BuyNowConfig::BUYNOW_CSV_FILENAME,
                    'rm'    => false,
                ],
                DirectoryList::MEDIA,
                'text/csv',
                null
            );
            $this->logger->info('Feed Generated Successfully: ' . $total . ' products were exported.');
        } catch (\Exception $e) {
            $this->logger->error('Feed Generation Failed: ' . $e->getMessage());
        }
    }

    private function _getCokerStore(): ?StoreInterface
    {
        try {
            return $this->storeRepository->get(CokerStoreInterface::COKER_STORE_CODE);
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function getSalableQty($sku): int
    {
        $salable = $this->salableQtyBySku->execute($sku);
        return  isset($salable[0]) ? (int)$salable[0]['qty'] : 0;
    }

    public function getStockStatus($productId): string
    {
        $stockItem = $this->stockRegistry->getStockItem($productId);
        if ($stockItem && $stockItem->getIsInStock()) {
            return 'true';
        }

        return 'false';
    }
}

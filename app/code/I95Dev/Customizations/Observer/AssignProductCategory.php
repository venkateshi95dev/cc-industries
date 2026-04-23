<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2022 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_Category
 */

namespace I95Dev\Customizations\Observer;

use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\Category\Helper\Data as CategoryHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Assign categories to product
 */
class AssignProductCategory implements ObserverInterface
{
    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * @var CategoryHelper
     */
    public $categoryHelper;

    /**
     * @var CategoryCollectionFactory
     */
    public $categoryCollectionFactory;

    /**
     * @var ProductRepositoryInterface
     */
    public $productRepository;

    /**
     * constructor to include dependency classes
     * @param Data $dataHelper
     * @param CategoryHelper $categoryHelper
     */
    public function __construct(
        Data $dataHelper,
        CategoryHelper $categoryHelper,
        CategoryCollectionFactory $categoryCollectionFactory,
        ProductRepositoryInterface $productRepository
    ) {
        $this->dataHelper = $dataHelper;
        $this->categoryHelper = $categoryHelper;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->productRepository = $productRepository;
    }

    /**
     * Assign categories to product
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $productData = $observer->getData('currentObject');
        $categoryIds = $this->dataHelper->getValueFromArray("categoryIds", $productData->stringData);
        $websiteIds = $this->dataHelper->getValueFromArray("websiteIds", $productData->stringData);
        $sku = $this->dataHelper->getValueFromArray("sku", $productData->stringData);
        $magCatIds = [];
        if (!empty($categoryIds) && is_array($categoryIds)) {
            foreach ($categoryIds as $path) {
                $parts = explode('/', $path);
                $parentId = $this->dataHelper->getCategoryParentId($websiteIds); // Default root catalog in Magento is ID=2
                foreach ($parts as $part) {
                    /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
                    
                    $collection = $this->categoryCollectionFactory->create()
                        ->addAttributeToFilter('name', trim($part))
                        ->addAttributeToFilter('parent_id', $parentId)
                        ->setPageSize(1);
            
                    $category = $collection->getFirstItem();
            
                    if (!$category || !$category->getId()) {
                        continue; // Not found
                    }
                    $magCatIds[] = (int)$category->getId();
                    $parentId = $category->getId(); // move deeper
                }
            }
            $currentCategoryIds = $this->getCategoryIdsBySku($sku);
            $updatedCategoryIds = array_unique(array_merge($currentCategoryIds, $magCatIds));
            $productData->productInterface->setCategoryIds($updatedCategoryIds);
        }
        
    }
    public function getCategoryIdsBySku($sku)
    {
        try {
            $product = $this->productRepository->get($sku);
            $categoryCurrentIds = $product->getCategoryIds();  // returns an array of category IDs
            return $categoryCurrentIds;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            // SKU not found
            return [];
        }
    }
}

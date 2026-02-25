<?php

namespace Crimson\Catalog\Model\Service\Sections;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\CategoryLinkRepositoryInterface;
use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Indexer\ActionInterface;
use Crimson\Catalog\Model\Service\Sections\Purchased\BestsellersCollectionFactory as BestSellerCollectionFactory;
use Crimson\MachBase\Model\MachConfig;
use Crimson\Catalog\Model\Config as CatalogConfig;

/**
 * Class CategoriesAndProductsForSections
 * @package Crimson\Catalog\Model\Service\Sections
 */
class CategoriesAndProductsForSections
{
    CONST CATEGORY_GENERATION_STATUS_CODE = "generation_config_status";
    CONST CATEGORY_GENERATION_CODE = "generation";
    CONST CATEGORY_SECTION_CODE = "page_section";

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var CategoryListInterface
     */
    protected $categoryListInterface;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepositoryInterface;

    /** @var Status */
    protected $productStatus;

    /** @var Visibility */
    protected $productVisibility;

    /** @var CategoryLinkManagementInterface */
    protected $categoryLinkManagementInterface;

    /** @var CategoryLinkRepositoryInterface */
    protected $categoryLinkRepositoryInterface;

    /** @var ActionInterface */
    protected $actionInterface;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var BestSellerCollectionFactory
     */
    protected $bestSellerCollectionFactory;

    /**
     * @var MachConfig
     */
    protected $machConfig;

    /**
     * @var CatalogConfig
     */
    protected $catalogConfig;

    /**
     * CategoriesAndProductsForSections constructor.
     * @param CategoryListInterface $categoryListInterface
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductRepositoryInterface $productRepositoryInterface
     * @param Status $productStatus
     * @param Visibility $productVisibility
     * @param CategoryLinkManagementInterface $categoryLinkManagementInterface
     * @param CategoryLinkRepositoryInterface $categoryLinkRepositoryInterface
     * @param CategoryRepositoryInterface $categoryRepository
     * @param BestSellerCollectionFactory $bestSellerCollectionFactory
     * @param CatalogConfig $catalogConfig
     * @param MachConfig $machConfig
     */
    public function __construct(
        CategoryListInterface $categoryListInterface,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductRepositoryInterface $productRepositoryInterface,
        Status $productStatus,
        Visibility $productVisibility,
        CategoryLinkManagementInterface $categoryLinkManagementInterface,
        CategoryLinkRepositoryInterface $categoryLinkRepositoryInterface,
        CategoryRepositoryInterface $categoryRepository,
        BestSellerCollectionFactory $bestSellerCollectionFactory,
        CatalogConfig $catalogConfig,
        MachConfig $machConfig
    )
    {
        $this->categoryListInterface = $categoryListInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->categoryLinkManagementInterface = $categoryLinkManagementInterface;
        $this->categoryLinkRepositoryInterface = $categoryLinkRepositoryInterface;
        $this->categoryRepository = $categoryRepository;
        $this->bestSellerCollectionFactory = $bestSellerCollectionFactory;
        $this->machConfig   = $machConfig;
        $this->catalogConfig = $catalogConfig;
    }

    /**
     * @param string $generationId
     * @param int $websiteId
     * @return array
     */
    public function getPurchasedProductsGeneration(string $generationId, int $websiteId): array
    {
        $productSkus = [];
        $bestSellerIds = $this->getBestsellersProductIds($websiteId);
        if (empty($bestSellerIds)) {
            return [];
        }
        //----FILTERS------------------------------------------------------------------------------------
        $this->searchCriteriaBuilder->addFilter('generation', [$generationId], 'finset');
        $this->searchCriteriaBuilder->addFilter('price', $this->catalogConfig->getPurchasedPriceFilter($websiteId),'gteq');
        $this->searchCriteriaBuilder->addFilter('image', null,'notnull');
	    $this->searchCriteriaBuilder->addFilter('image', 'no_selection','neq');
        $this->searchCriteriaBuilder->addFilter(
            ProductInterface::STATUS,
            $this->productStatus->getVisibleStatusIds(),
            'in'
        );
        $this->searchCriteriaBuilder->addFilter(
            ProductInterface::VISIBILITY,
            $this->productVisibility->getVisibleInSiteIds(),
            'in'
        );
        $this->searchCriteriaBuilder->addFilter('row_id', $bestSellerIds, 'in');
        $this->searchCriteriaBuilder->setPageSize($this->catalogConfig->getResultsLimit($websiteId));
        $this->searchCriteriaBuilder->setCurrentPage(1);
        //-------------------------------------------------------------------------------------------
        $productsSearch = $this->productRepositoryInterface->getList($this->searchCriteriaBuilder->create());
        if (!$productsSearch->getTotalCount()) {
            return [];
        }

        foreach ($productsSearch->getItems() as $product) {
            $productSkus[$product->getSku()] = $product->getCategoryIds();
        }

        return $productSkus;
    }

    /**
     * @param int $websiteId
     * @return array
     */
    public function getBestsellersProductIds(int $websiteId): array
    {
        $result = [];
        $bestSellers = $this->bestSellerCollectionFactory->create();
        $date = $this->getDatesToFilter();
        $bestSellers->addFieldToFilter('product_price',['gteq' => $this->catalogConfig->getPurchasedPriceFilter($websiteId)]);
        $bestSellers->setDateRange($date['from'], $date['to']);
        $bestSellers->load();
        if ($bestSellers->getSize()) {
            foreach ($bestSellers as $bestSeller) {
                $result[] = $bestSeller->getProductId();
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getDatesToFilter(): array
    {
        $result = [];
        try {
            $date   = new \DateTime('now');
            $timeZone = new \DateTimeZone($this->machConfig->getIntegrationTimezone());
            $date->setTimezone($timeZone);
            $to = $date->format('Y-m-d H:i:s');
            $from = $date->modify('-50 day')->format('Y-m-d H:i:s');

            $result['to'] = $to;
            $result['from'] = $from;
        } catch (\Exception $e) {

        }

        return $result;
    }

    /**
     * @param string $section
     * @param int    $generationId
     *
     * @return SearchResultsInterface
     */
    public function getCategoriesForSection(string $section, int $generationId): SearchResultsInterface
    {
        //adding filters
        $this->searchCriteriaBuilder->addFilter(CategoryInterface::KEY_IS_ACTIVE, 1);
        $this->searchCriteriaBuilder->addFilter(self::CATEGORY_GENERATION_STATUS_CODE, 1);
        $this->searchCriteriaBuilder->addFilter(self::CATEGORY_SECTION_CODE,$section);
        $this->searchCriteriaBuilder->addFilter(self::CATEGORY_GENERATION_CODE, $generationId);

        return $this->categoryListInterface->getList($this->searchCriteriaBuilder->create());
    }

    /**
     * @param string $generationId
     * @param SortOrder $sortOrder
     * @param int $websiteId
     * @return array
     */
    public function getNewProductsGeneration(string $generationId, SortOrder $sortOrder, int $websiteId): array
    {
        $productSkus = [];
        $this->searchCriteriaBuilder->addFilter('generation', [$generationId], 'finset');
        $this->searchCriteriaBuilder->addFilter('image', null,'notnull');
        $this->searchCriteriaBuilder->addFilter('image', 'no_selection','neq');
        $this->searchCriteriaBuilder->addFilter(
            ProductInterface::STATUS,
            $this->productStatus->getVisibleStatusIds(),
            'in'
        );
        $this->searchCriteriaBuilder->addFilter(
            ProductInterface::VISIBILITY,
            $this->productVisibility->getVisibleInSiteIds(),
            'in'
        );
        $this->searchCriteriaBuilder->setSortOrders([$sortOrder]);
        $this->searchCriteriaBuilder->setPageSize($this->catalogConfig->getResultsLimit($websiteId));
        $this->searchCriteriaBuilder->setCurrentPage(1);

        $productsSearch = $this->productRepositoryInterface->getList($this->searchCriteriaBuilder->create());
        if (!$productsSearch->getTotalCount()) {
            return [];
        }

        foreach ($productsSearch->getItems() as $product) {
            /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
            $productSkus[$product->getSku()] = $product->getCategoryIds();
        }

        return $productSkus;
    }

    /**
     * @param int   $categoryId
     * @param array $productDataArray
     */
    public function processProductsForCategory(int $categoryId, array $productDataArray)
    {
        try {
            $assignedSkus = $this->getCategoryAssignedProductSkus($categoryId);
            if (count($assignedSkus) > 0) {
                //if there are Products assigned we check each one to keep it or remove it
                foreach ($assignedSkus as $assignedSku) {
                    if (!isset($productDataArray[$assignedSku])) {
                        $this->categoryLinkRepositoryInterface->deleteByIds($categoryId, $assignedSku);
                    }
                }

            }

            //we check now for products that need to be assigned and they are not already assigned to assign them
            foreach ($productDataArray as $productSku => $categoryIds) {
                if (!in_array($productSku,$assignedSkus)) {
                    //this is important to keep the current product category assigment
                    $finalCategoriesArray = array_unique(array_merge($categoryIds,[$categoryId]));
                    $this->categoryLinkManagementInterface->assignProductToCategories($productSku, $finalCategoriesArray);
                }
            }

        } catch (\Exception $e) {

        }
    }

    /**
     * @param int $categoryId
     *
     * @return array
     */
    public function getCategoryAssignedProductSkus(int $categoryId): array
    {
        $result = [];
        $assignedProducts = $this->categoryLinkManagementInterface->getAssignedProducts($categoryId);
        if (count($assignedProducts) > 0) {
            foreach ($assignedProducts as $assignedProduct) {
                $result[] = $assignedProduct->getSku();
            }
        }

        return $result;
    }

    /**
     * @param        $attribute
     * @param string $generation
     *
     * @return int|null
     */
    public function getGenerationOptionId($attribute, string $generation): ?int
    {
        $generationId = (int)$attribute->getSource()->getOptionId($generation);
        if ($generationId > 0) {
            return $generationId;
        }

        return null;
    }
}

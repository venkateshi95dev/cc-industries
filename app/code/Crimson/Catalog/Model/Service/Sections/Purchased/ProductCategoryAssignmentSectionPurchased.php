<?php

namespace Crimson\Catalog\Model\Service\Sections\Purchased;

use Crimson\Catalog\Logger\Logger;
use Crimson\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\CategoryLinkRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Crimson\Catalog\Model\Service\Sections\CategoriesAndProductsForSections;
use Crimson\Catalog\Model\Category\Attribute\Source\PageSections;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ProductCategoryAssignmentSectionPurchased
 * @package Crimson\Catalog\Model\Service\Sections\Purchased
 */
class ProductCategoryAssignmentSectionPurchased
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var CatalogConfig
     */
    protected $catalogConfig;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepositoryInterface;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepositoryInterface;

    /**
     * @var CategoryLinkRepositoryInterface
     */
    protected $categoryLinkRepositoryInterface;

    /** @var Status */
    protected $productStatus;

    /** @var Visibility */
    protected $productVisibility;

    /**
     * @var ProductResource
     */
    protected $productResource;

    /**
     * @var CategoriesAndProductsForSections
     */
    protected $cateProdForSections;

    /**
     * ProductsForHomepageSectionNew constructor.
     *
     * @param CatalogConfig                                                   $catalogConfig
     * @param SearchCriteriaBuilder                                           $searchCriteriaBuilder
     * @param SortOrderBuilder                                                $sortOrderBuilder
     * @param CategoriesAndProductsForSections $cateProdForSections
     * @param ProductRepositoryInterface                                      $productRepositoryInterface
     * @param CategoryRepositoryInterface                                     $categoryRepositoryInterface
     * @param CategoryLinkRepositoryInterface                                 $categoryLinkRepositoryInterface
     * @param Status                                                          $productStatus
     * @param Visibility                                                      $productVisibility
     * @param ProductResource                                                 $productResource
     * @param Logger                                                          $logger
     */
    public function __construct(
        CatalogConfig $catalogConfig,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        CategoriesAndProductsForSections $cateProdForSections,
        ProductRepositoryInterface $productRepositoryInterface,
        CategoryRepositoryInterface $categoryRepositoryInterface,
        CategoryLinkRepositoryInterface $categoryLinkRepositoryInterface,
        Status $productStatus,
        Visibility $productVisibility,
        ProductResource $productResource,
        Logger $logger
    )
    {
        $this->catalogConfig = $catalogConfig;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->cateProdForSections = $cateProdForSections;
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->categoryRepositoryInterface = $categoryRepositoryInterface;
        $this->categoryLinkRepositoryInterface = $categoryLinkRepositoryInterface;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->productResource = $productResource;
        $this->logger = $logger;
    }

    /**
     * @param int $websiteId
     * @return $this
     * @throws LocalizedException
     */
    public function execute(int $websiteId): ProductCategoryAssignmentSectionPurchased
    {
        $this->logger->debug(__("= = ='Purchased': Starting the process. = = ="));

        $generations = $this->catalogConfig->getGenerationValues($websiteId);
        if (empty($generations)) {
            $this->logger->debug(
                __("= = ='Purchased': No generation-category values, please check the Admin configuration. = = =")
            );

            return $this;
        }

        $attribute = $this->productResource->getAttribute('generation');
        if (!$attribute || !$attribute->getId()) {
            $this->logger->debug(
                __("= = ='Purchased': Generation attribute doesn't exist. = = =")
            );

            return $this;
        }

        try {
            foreach ($generations as $generation) {

                if (empty($generation)) {
                    continue;
                }

                $generationId = $this->cateProdForSections->getGenerationOptionId($attribute, $generation);
                if (!$generationId)  {
                    continue;
                }
                $this->logger->debug(
                    __("= ='Purchased': Processing generation: " .$generation. " = =")
                );

                //getting categories
                $categoriesSearch = $this->cateProdForSections->getCategoriesForSection(PageSections::PURCHASED_SECTION, $generationId);
                if (!$categoriesSearch->getTotalCount()) {
                    continue;
                }
                $this->logger->debug(
                    __("= ='Purchased': Categories were found.= =")
                );

                $productDataArray = $this->cateProdForSections->getPurchasedProductsGeneration((string) $generationId, $websiteId);
                if (empty($productDataArray)) {
                    continue;
                }
                $this->logger->debug(
                    __("= ='Purchased': Products were found.= =")
                );

                //at this point we have categories and products to assign, so we go to the category and remove, keep
                //and assign the necessary products.
                foreach ($categoriesSearch->getItems() as $category) {
                    $this->logger->debug(
                        __("= ='Purchased': Processing category ID: " . $category->getId())
                    );
                    /** @var CategoryInterface $category */
                    $this->cateProdForSections->processProductsForCategory($category->getId(), $productDataArray);
                    $this->logger->debug(
                        __("= ='Purchased': The following products have been assigned: " . print_r($productDataArray,true))
                    );
                }

                $this->logger->debug(
                    __("= = ='Purchased': Finishing processing for generation: " .$generation. " = = =")
                );
            }

        } catch(\Exception $e) {

        }

        $this->logger->debug(__("= = ='Purchased': Finishing the process. = = ="));
        return $this;
    }


}

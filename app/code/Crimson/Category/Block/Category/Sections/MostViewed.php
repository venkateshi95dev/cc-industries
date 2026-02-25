<?php

namespace Crimson\Category\Block\Category\Sections;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\ReviewRendererInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Reports\Model\ResourceModel\Product\Collection;
use Magento\Reports\Model\ResourceModel\Product\CollectionFactory as MostViewedCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Crimson\Category\Helper\Data as ModuleConfig;

/**
 * Class MostViewed
 * @package Crimson\Category\Block\Category\Sections
 */
class MostViewed extends AbstractProduct
{

    CONST LIMIT = 15;
    CONST HEADER_TEXT = "Most Viewed From ";
    CONST TYPE = "most_viewed";

    /** @var StoreManagerInterface */
    protected $storeManager;

    /** @var Resolver */
    protected $layerResolver;

    /** @var MostViewedCollectionFactory */
    protected $mostViewedCollectionFactory;

    /** @var Status */
    protected $productStatus;

    /** @var Visibility */
    protected $productVisibility;

    protected $_scopeConfig;

    /** @var CategoryResource */
    protected $categoryResource;

    /**
     * Catalog config
     *
     * @var CatalogConfig
     */
    protected $_catalogConfig;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var ModuleConfig
     */
    protected $moduleConfig;

    public function __construct(
        MostViewedCollectionFactory $mostViewedCollectionFactory,
        Status $productStatus,
        Visibility $productVisibility,
        StoreManagerInterface $storeManager,
        Context $context,
        Resolver $layerResolver,
        ScopeConfigInterface $scopeConfig,
        CategoryResource $categoryResource,
        CatalogConfig $_catalogConfig,
        Registry $_registry,
        ModuleConfig $moduleConfig,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->layerResolver = $layerResolver->get();
        $this->mostViewedCollectionFactory = $mostViewedCollectionFactory;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->_scopeConfig = $scopeConfig;
        $this->categoryResource = $categoryResource;
        $this->_catalogConfig = $_catalogConfig;
        $this->_registry = $_registry;
        $this->moduleConfig = $moduleConfig;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isMostViewedSectionEnabled(): bool
    {
        return $this->moduleConfig->isMostViewedSectionEnabled();
    }

    /**
     * @return string|null
     */
    public function getMostViewedEventAge(): ?string
    {
        return $this->moduleConfig->getMostViewedEventAge();
    }

    /**
     * @return array
     */
    public function generateRange(): array
    {
        $age = $this->getMostViewedEventAge();
        if (!$age) {
            return [];
        }

        return [
            'to' => date('Y-m-d'),
            'from'   => date('Y-m-d', strtotime($age))
        ];
    }

    /**
     * @param $childrenIds
     * @return Collection|null
     * @throws NoSuchEntityException
     */
    public function getMostViewedCollection($childrenIds): ?Collection
    {
        $collection = null;
        $category = $this->layerResolver->getCurrentCategory();
        if ($category && $category->getId() && !empty($childrenIds)) {
            $store = $this->storeManager->getStore();
            $storeId = $store->getId();
            $limit = self::LIMIT;
            if ((int) $this->getLimit() > 0) {
                $limit = (int) $this->getLimit();
            }

            $ids = $this->getCategoryChildrenIds($childrenIds);
            if (!$ids) {
                return null;
            }

            $range = $this->generateRange();
            $from = !empty($range['from']) ? $range['from'] : "";
            $to = !empty($range['to']) ? $range['to'] : "";

            /** @var \Magento\Reports\Model\ResourceModel\Product\Collection $collection */
            $collection = $this->mostViewedCollectionFactory->create();
            $collection->setStoreId($storeId)
                ->addViewsCount($from, $to)
                ->addStoreFilter($store);

            if ($collection->isEnabledFlat()) {
                $collection->getSelect()->joinInner(
                    ['e2' => 'catalog_product_flat_' . $storeId], 'e2.entity_id = e.entity_id'
                );
            } else {
                $collection
                    ->addAttributeToSelect(
                        [
                            'name',
                            'price',
                            'url_key',
                            'price',
                            'image',
                            'small_image',
                        ],
                        'inner'
                    );
            }

            $collection
                ->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()])
                ->setVisibility($this->productVisibility->getVisibleInSiteIds())
            ;

	        $collection->getSelect()->join(
		        ['cat_prod' => $collection->getTable('catalog_category_product')],
		        'cat_prod.product_id = e.entity_id',
                [
                    'cat_prod_entity_id'  => 'cat_prod.entity_id',
                    'cat_prod_product_id' => 'cat_prod.product_id',
                ]
	        )->where($collection->getConnection()->prepareSqlCondition('cat_prod.category_id', ['in' => $ids]));

	        $collection->getSelect()->limit($limit);
            $collection->load();
        }

        return $collection;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return (string)self::TYPE;
    }

    /**
     * @return string
     */
    public function getHeaderText(): string
    {
        $header = self::HEADER_TEXT;
        if (!empty($this->getHeader())) {
            $header = (string)$this->getHeader();
        }

        $header = $header . " " . $this->getCurrentCategoryName();


        return $header;
    }

    /**
     * @param $childrenIds
     * @return Collection|null
     * @throws NoSuchEntityException
     */
    public function getMostViewed($childrenIds): ?Collection
    {
        return $this->getMostViewedCollection($childrenIds);
    }

    /**
     * @return string
     */
    public function getCurrentCategoryName(): string
    {
      $categoryName = '';
      $category = $this->layerResolver->getCurrentCategory();
      if ($category && $category->getId()) {
          $categoryName = (string)$category->getName();
      }

      return $categoryName;
    }

    /**
     * @return string
     */
    public function getReviewTemplateType(): string
    {
        return ReviewRendererInterface::DEFAULT_VIEW;
    }

    /**
     * @return mixed|null
     */
    public function getIsAnchorAttributeModel()
    {
        try {
            if (!$this->_registry->registry('_category_is_anchor_attribute')) {
                $model = $this->_catalogConfig->getAttribute(Category::ENTITY, 'is_anchor');
                $this->_registry->register('_category_is_anchor_attribute', $model);
            }

            return $this->_registry->registry('_category_is_anchor_attribute');
        } catch (LocalizedException $e) {
            return null;
        }
    }

    /**
     * @param $childrenIds
     * @return array
     */
    public function getCategoryChildrenIds($childrenIds): array
    {
        if (empty($childrenIds)) {
            return [];
        }

        $isAnchorAttribute = $this->getIsAnchorAttributeModel();
        if (!$isAnchorAttribute) {
            return [];
        }

        return $this->categoryResource->findWhereAttributeIs($childrenIds, $isAnchorAttribute, 1);
    }

    /**
     * @return array
     */
    public function hasChildren(): array
    {
        try {
            $currentCategory = $this->layerResolver->getCurrentCategory();
            $children = $currentCategory->getAllChildren(true);
            if (in_array($currentCategory->getId(), $children)) {
                unset($children[array_search($currentCategory->getId(), $children)]);
            }

            return $children;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * @return mixed
     */
    public function getProductUrlSuffix()
    {
        return $this->_scopeConfig->getValue('catalog/seo/product_url_suffix');
    }
}

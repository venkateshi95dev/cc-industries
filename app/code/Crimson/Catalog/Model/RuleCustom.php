<?php

namespace Crimson\Catalog\Model;

use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogRule\Helper\Data;
use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
use Magento\CatalogRule\Model\ResourceModel\Product\ConditionsToCollectionApplier;
use Magento\CatalogRule\Model\ResourceModel\Rule as RuleResourceModel;
use Magento\CatalogRule\Model\Rule;
use Magento\CatalogRule\Model\Rule\Action\CollectionFactory as RuleCollectionFactory;
use Magento\CatalogRule\Model\Rule\Condition\CombineFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

class RuleCustom extends Rule
{

    private ConditionsToCollectionApplier $conditionsToCollectionApplier;
    private RuleResourceModel $ruleResourceModel;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        CollectionFactory $productCollectionFactory,
        StoreManagerInterface $storeManager,
        CombineFactory $combineFactory,
        RuleCollectionFactory $actionCollectionFactory,
        ProductFactory $productFactory,
        Iterator $resourceIterator,
        Session $customerSession,
        Data $catalogRuleData,
        TypeListInterface $cacheTypesList,
        DateTime $dateTime,
        RuleProductProcessor $ruleProductProcessor,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $relatedCacheTypes = [],
        array $data = [],
        ExtensionAttributesFactory $extensionFactory = null,
        AttributeValueFactory $customAttributeFactory = null,
        Json $serializer = null,
        RuleResourceModel $ruleResourceModel = null,
        ConditionsToCollectionApplier $conditionsToCollectionApplier = null
    )
    {
        parent::__construct($context, $registry, $formFactory, $localeDate, $productCollectionFactory, $storeManager, $combineFactory, $actionCollectionFactory, $productFactory, $resourceIterator, $customerSession, $catalogRuleData, $cacheTypesList, $dateTime, $ruleProductProcessor, $resource, $resourceCollection, $relatedCacheTypes, $data, $extensionFactory, $customAttributeFactory, $serializer, $ruleResourceModel, $conditionsToCollectionApplier);
        $this->ruleResourceModel = $ruleResourceModel ?: ObjectManager::getInstance()->get(RuleResourceModel::class);
        $this->conditionsToCollectionApplier = $conditionsToCollectionApplier ?? ObjectManager::getInstance()->get(ConditionsToCollectionApplier::class);
    }

    /**
     * Get array of product ids which are matched by rule
     *
     * @return array
     */
    public function getMatchingProductIds(): array
    {
        if ($this->_productIds === null) {
            $this->_productIds = [];
            $this->setCollectedAttributes([]);

            if ($this->getWebsiteIds()) {
                /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
                $productCollection = $this->_productCollectionFactory->create();
                $productCollection->setStoreId($this->_storeManager->getDefaultStoreView()->getId());
                $productCollection->addWebsiteFilter($this->getWebsiteIds());
                if ($this->_productsFilter) {
                    $productCollection->addIdFilter($this->_productsFilter);
                }
                $this->getConditions()->collectValidatedAttributes($productCollection);

                if ($this->canPreMapProducts()) {
                    $productCollection = $this->conditionsToCollectionApplier
                        ->applyConditionsToCollection($this->getConditions(), $productCollection);

                    if ($this->_isInStockAsFilter($this->getConditions()->asArray())) {
                        $productCollection->getSelect()->join(
                            ['c_s_i' => $this->ruleResourceModel->getTable('cataloginventory_stock_item')],
                            'e.entity_id = c_s_i.product_id AND c_s_i.qty > 0'
                        );
                    }
                }

                $this->_resourceIterator->walk(
                    $productCollection->getSelect(),
                    [[$this, 'callbackValidateProduct']],
                    [
                        'attributes' => $this->getCollectedAttributes(),
                        'product' => $this->_productFactory->create()
                    ]
                );
            }
        }

        return $this->_productIds;
    }

    /**
     * Check if we can use mapping for rule conditions
     *
     * @return bool
     */
    private function canPreMapProducts(): bool
    {
        $conditions = $this->getConditions();

        // No need to map products if there is no conditions in rule
        if (!$conditions || !$conditions->getConditions()) {
            return false;
        }

        return true;
    }

    private function _isInStockAsFilter($conditionsArray)
    {
        if (empty($conditionsArray['conditions'])) {
            return false;
        }

        foreach ($conditionsArray['conditions'] as $condition) {
            if (isset($condition['attribute']) && $condition['attribute'] === "quantity_and_stock_status") {
                return true;
            }
        }

        return false;
    }
}

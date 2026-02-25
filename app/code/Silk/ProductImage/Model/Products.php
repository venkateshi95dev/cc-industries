<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Silk\ProductImage\Model;

use Silk\ProductImage\Model\Product\PositionResolver;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\Manager;
use Silk\ProductImage\Model\Position\Cache;
use Zend_Db_Expr;
use Zend_Db_Select_Exception;

/**
 * This class is for manipulation products' collections and data
 */
class Products
{
    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var Manager
     */
    protected $_moduleManager;

    /**
     * @var Cache
     */
    protected $_cache;

    /**
     * @var string
     */
    protected $_cacheKey;


    /**
     * @var PositionResolver
     */
    private $positionResolver;

    /**
     * @var array|bool
     */
    private $positions = false;

    /**
     * @param ProductFactory $productFactory
     * @param Manager $moduleManager
     * @param Cache $cache
     * @param PositionResolver|null $positionResolver
     */
    public function __construct(
        ProductFactory $productFactory,
        Manager $moduleManager,
        Cache $cache,
        PositionResolver $positionResolver = null
    ) {
        $this->_productFactory = $productFactory;
        $this->_moduleManager = $moduleManager;
        $this->_cache = $cache;
        $this->positionResolver = $positionResolver ?: ObjectManager::getInstance()->get(PositionResolver::class);
    }

    /**
     * Sets cache key
     *
     * @param string $key
     * @return void
     */
    public function setCacheKey($key)
    {
        $this->_cacheKey = $key;
    }

    /**
     * Retrieves a product factory object
     *
     * @return ProductFactory
     */
    public function getFactory()
    {
        return $this->_productFactory;
    }

    /**
     * Builds the collection for a grid
     *
     * @param int $documentId
     * @param int $store (Optional)
     * @param array|null $productPositions (Optional)
     * @return Collection
     * @throws LocalizedException
     */
    public function getCollectionForGrid($documentId, $store = null, $productPositions = null)
    {
        /** @var Collection $collection */
        $collection = $this->getFactory()->create()->getCollection()
            ->addAttributeToSelect(
                [
                    'sku',
                    'name',
                    'price',
                    'small_image'
                ]
            );

        if (is_array($productPositions)) {
            $productIds = array_keys($productPositions);
            $collection->getSelect()->distinct()->where('e.entity_id IN(?)', $productIds);
        }


        $collection = $this->applyPositions($collection, $documentId, $productPositions);

        if ($store !== null) {
            $collection->addStoreFilter($store);
        }

        return $collection;
    }



    /**
     * Applies position information
     *
     * @param Collection $collection
     * @param int $documentId
     * @param array|null $productPositions (Optional)
     * @return Collection
     * @throws LocalizedException
     */
    private function applyPositions(Collection $collection, int $documentId, $productPositions = null)
    {
        $positions = $this->getPositions();

        if ($positions === false) {
            if (!is_array($productPositions)) {
                $collection->getSelect()->where('at_position.document_id = ?', $documentId);
                $collection->joinField(
                    'position',
                    'img_product',
                    'position',
                    'product_id=entity_id',
                    null,
                    'left'
                );
                $collection->setOrder('position', $collection::SORT_ORDER_ASC);
                $productPositions = $this->positionResolver->getPositions($documentId);
            }

            $this->positions = $productPositions;
        } else {
            $collection->getSelect()->reset(Select::WHERE)->reset(Select::HAVING);
            $collection->addAttributeToFilter('entity_id', ['in' => array_keys($positions)]);
        }

        return $this->applyCachedChanges($collection);
    }

    /**
     * Save positions from collection to cache
     *
     * @param Collection $collection
     * @return void
     * @throws LocalizedException
     * @throws Zend_Db_Select_Exception
     */
    public function savePositions(Collection $collection)
    {
        if (!$collection->isLoaded()) {
            $collection->load();
        }
        $select = clone $collection->getSelect();

        $select->reset(Select::LIMIT_COUNT);
        $select->reset(Select::LIMIT_OFFSET);
        $this->prependColumn($select, $collection->getEntity()->getIdFieldName());

        $positions = array_flip($collection->getConnection()->fetchCol($select));

        $this->savePositionsToCache($positions);
    }

    /**
     * Add needed column to the Select on the first position
     *
     * There are no problems for MySQL with several same columns in the result set
     *
     * @param Select $select
     * @param string $columnName
     * @return void
     * @throws Zend_Db_Select_Exception
     */
    private function prependColumn(Select $select, string $columnName)
    {
        $columns = $select->getPart(Select::COLUMNS);
        array_unshift($columns, ['e', $columnName, null]);
        $select->setPart(Select::COLUMNS, $columns);
    }

    /**
     * Apply cached positions, sort order products returns a base collection with WHERE IN filter applied
     *
     * @param Collection $collection
     * @return Collection
     */
    public function applyCachedChanges(Collection $collection)
    {
        $positions = $this->getPositions();
        if (!$positions) {
            return $collection;
        }

        $collection->getSelect()->reset(Select::ORDER);
        asort($positions, SORT_NUMERIC);

        $ids = implode(',', array_keys($positions));
        $select = $collection->getSelect();
        $field = $select->getAdapter()->quoteIdentifier('e.entity_id');
        $orderExpression = new Zend_Db_Expr("FIELD({$field}, {$ids})");
        $select->order($orderExpression);

        return $collection;
    }

    /**
     * Save products positions to cache
     *
     * @param array $positions
     * @return void
     */
    protected function savePositionsToCache($positions)
    {
        $this->_cache->saveData(
            $this->_cacheKey,
            $positions
        );
    }

    /**
     * Retrieves positions
     *
     * @return array|bool
     */
    private function getPositions()
    {
        $positions = $this->_cache->getPositions($this->_cacheKey);
        return is_array($positions) ? $positions : $this->positions;
    }
}

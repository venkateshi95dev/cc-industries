<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

declare(strict_types=1);

namespace Magedelight\Megamenu\Model;

use Magedelight\Megamenu\Api\CacheRepositoryInterface;
use Magedelight\Megamenu\Api\Data\CacheInterface;
use Magedelight\Megamenu\Api\Data\CacheInterfaceFactory;
use Magedelight\Megamenu\Api\Data\CacheSearchResultsInterfaceFactory;
use Magedelight\Megamenu\Model\ResourceModel\Cache as ResourceCache;
use Magedelight\Megamenu\Model\ResourceModel\Cache\CollectionFactory as CacheCollectionFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\DataObject;

class CacheRepository implements CacheRepositoryInterface
{

    /**
     * @var CacheCollectionFactory
     */
    protected $cacheCollectionFactory;

    /**
     * @var Cache
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var ResourceCache
     */
    protected $resource;

    /**
     * @var CacheInterfaceFactory
     */
    protected $cacheFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * Constructor
     *
     * @param ResourceCache $resource
     * @param CacheInterfaceFactory $cacheFactory
     * @param CacheCollectionFactory $cacheCollectionFactory
     * @param CacheSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        ResourceCache $resource,
        CacheInterfaceFactory $cacheFactory,
        CacheCollectionFactory $cacheCollectionFactory,
        CacheSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder
    ) {
        $this->resource = $resource;
        $this->cacheFactory = $cacheFactory;
        $this->cacheCollectionFactory = $cacheCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * Save Cache
     *
     * @param CacheInterface $cache
     * @return CacheInterface
     */
    public function save(CacheInterface $cache)
    {
        try {
            $this->resource->save($cache);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the cache: %1',
                $exception->getMessage()
            ));
        }
        return $cache;
    }

    /**
     * Get Cache
     *
     * @param int $cacheId
     * @return CacheInterface
     */
    public function get($cacheId)
    {
        $cache = $this->cacheFactory->create();
        $this->resource->load($cache, $cacheId);
        if (!$cache->getId()) {
            throw new NoSuchEntityException(__('Cache with id "%1" does not exist.', $cacheId));
        }
        return $cache;
    }

    /**
     * Load By Name
     *
     * @param string $name
     * @return DataObject
     */
    public function loadByName($name)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilters([$this->filterBuilder->setField('name')
                ->setValue($name)->create()])
            ->create();

        $collection = $this->cacheCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        return $collection->getFirstItem();
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->cacheCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(CacheInterface $cache)
    {
        try {
            $cacheModel = $this->cacheFactory->create();
            $this->resource->load($cacheModel, $cache->getCacheId());
            $this->resource->delete($cacheModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Cache: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($cacheId)
    {
        return $this->delete($this->get($cacheId));
    }
}

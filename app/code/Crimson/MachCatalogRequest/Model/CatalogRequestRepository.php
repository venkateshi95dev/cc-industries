<?php
/**
 * @namespace   Crimson
 * @module      MachCatalogRequest
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/14/2019 4:13 PM
 * @brief
 */

namespace Crimson\MachCatalogRequest\Model;

use Crimson\MachCatalogRequest\Api\Data\CatalogRequestInterface;
use Crimson\MachCatalogRequest\Api\Data\CatalogRequestSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Crimson\MachCatalogRequest\Api\Data\CatalogRequestSearchResultsInterfaceFactory;
use Crimson\MachCatalogRequest\Model\ResourceModel\CatalogRequest as ResourceCatalogRequest;
use Crimson\MachCatalogRequest\Model\ResourceModel\CatalogRequest\CollectionFactory as CatalogRequestCollectionFactory;
use Crimson\MachCatalogRequest\Api\CatalogRequestRepositoryInterface;

/**
 * Class CatalogRequestRepository
 * @package Crimson\MachCatalogRequest\Model
 */
class CatalogRequestRepository implements CatalogRequestRepositoryInterface
{
    /**
     * @var CatalogRequestCollectionFactory
     */
    protected $catalogRequestCollectionFactory;
    /**
     * @var CatalogRequestFactory
     */
    protected $catalogRequestFactory;
    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;
    /**
     * @var ResourceCatalogRequest
     */
    protected $resource;
    /**
     * @var CatalogRequestSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @param ResourceCatalogRequest $resource
     * @param CatalogRequestFactory $catalogRequestFactory
     * @param CatalogRequestCollectionFactory $catalogRequestCollectionFactory
     * @param CatalogRequestSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceCatalogRequest $resource,
        CatalogRequestFactory $catalogRequestFactory,
        CatalogRequestCollectionFactory $catalogRequestCollectionFactory,
        CatalogRequestSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->catalogRequestFactory = $catalogRequestFactory;
        $this->catalogRequestCollectionFactory = $catalogRequestCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @param CatalogRequestInterface $catalogRequest
     *
     * @return CatalogRequestInterface
     * @throws CouldNotSaveException
     */
    public function save(CatalogRequestInterface $catalogRequest): CatalogRequestInterface
    {
        try {
            /** @noinspection PhpParamsInspection */
            $this->resource->save($catalogRequest);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the catalog request: %1',
                $exception->getMessage()
            ));
        }

        return $catalogRequest;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($requestId)
    {
        $catalogRequest = $this->catalogRequestFactory->create();
        $this->resource->load($catalogRequest, $requestId);
        if (!$catalogRequest->getId()) {
            throw new NoSuchEntityException(__('Catalog Request with id "%1" does not exist.', $requestId));
        }
        return $catalogRequest;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $collection = $this->catalogRequestCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(CatalogRequestInterface $catalogRequest)
    {
        try {
            /** @noinspection PhpParamsInspection */
            $this->resource->delete($catalogRequest);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Catalog Request: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($requestId)
    {
        return $this->delete($this->getById($requestId));
    }
}

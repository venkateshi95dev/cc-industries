<?php
/**
 * @namespace   Crimson
 * @module      MachShipping
 * @date        06/07/2022 12:00 am
 * @brief
 */

namespace Crimson\MachShipping\Model;

use Crimson\MachShipping\Api\UpsDeliverySaturdaysRepositoryInterface;
use Crimson\MachShipping\Api\Data\UpsDeliverySaturdaysSearchResultsInterface;
use Crimson\MachShipping\Api\Data\UpsDeliverySaturdaysSearchResultsInterfaceFactory as SearchResultFactory;
use Crimson\MachShipping\Api\Data\UpsDeliverySaturdaysInterface;
use Crimson\MachShipping\Api\Data\UpsDeliverySaturdaysInterfaceFactory as InterfaceFactory;
use Crimson\MachShipping\Model\ResourceModel\UpsDeliverySaturdays as ObjectResource;
use Crimson\MachShipping\Model\ResourceModel\UpsDeliverySaturdays\Collection;
use Crimson\MachShipping\Model\ResourceModel\UpsDeliverySaturdays\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class UpsDeliverySaturdaysRepository implements UpsDeliverySaturdaysRepositoryInterface
{
    /**
     * @var ObjectResource
     */
    protected $resource;

    /**
     * @var InterfaceFactory
     */
    protected $interfaceFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var SearchResultFactory
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param ObjectResource                 $resource
     * @param InterfaceFactory               $interfaceFactory
     * @param CollectionFactory              $collectionFactory
     * @param SearchResultFactory            $searchResultsFactory
     * @param CollectionProcessorInterface   $collectionProcessor
     */
    public function __construct(
        ObjectResource $resource,
        InterfaceFactory $interfaceFactory,
        CollectionFactory $collectionFactory,
        SearchResultFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource             = $resource;
        $this->interfaceFactory     = $interfaceFactory;
        $this->collectionFactory    = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor  = $collectionProcessor;
    }

    /**
     * @param \Crimson\MachShipping\Api\Data\UpsDeliverySaturdaysInterface
     *
     * @return UpsDeliverySaturdaysInterface     * @throws CouldNotSaveException
     */
    public function save(UpsDeliverySaturdaysInterface $upsDeliverySaturdays)
    {
        try {
            $this->resource->save($upsDeliverySaturdays);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $upsDeliverySaturdays;
    }

    /**
     * @param string      $id
     * @param string|null $field
     *
     * @return UpsDeliverySaturdaysInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id, $field = null)
    {
        /** @var UpsDeliverySaturdaysInterface $upsDeliverySaturdays */
        $upsDeliverySaturdays = $this->interfaceFactory->create();
        $this->resource->load($upsDeliverySaturdays, $id, $field);
        if (!$upsDeliverySaturdays->getId()) {
            throw new NoSuchEntityException(
                __('The object UpsDeliverySaturdaysInterface with the "%1" ID doesn\'t exist.', $id)
            );
        }

        return $upsDeliverySaturdays;
    }

    
    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     *
     * @return UpsDeliverySaturdaysSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        /** @var UpsDeliverySaturdaysSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @param UpsDeliverySaturdaysInterface $upsDeliverySaturdays
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(UpsDeliverySaturdaysInterface $upsDeliverySaturdays)
    {
        try {
            $this->resource->delete($upsDeliverySaturdays);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }

        return true;
    }

    /**
     * @param string $id
     *
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }
}



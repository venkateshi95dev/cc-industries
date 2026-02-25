<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Silk\ProductImage\Model;

use Silk\ProductImage\Api\DocumentRepositoryInterface;
use Silk\ProductImage\Api\Data;
use Silk\ProductImage\Model\ResourceModel\Document as ResourceDocument;
use Silk\ProductImage\Model\ResourceModel\Document\CollectionFactory as DocumentCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;


/**
 * Class DocumentRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DocumentRepository implements DocumentRepositoryInterface
{
    /**
     * @var ResourceDocument
     */
    protected $resource;

    /**
     * @var DocumentFactory
     */
    protected $documentFactory;

    /**
     * @var DocumentCollectionFactory
     */
    protected $documentCollectionFactory;

    /**
     * @var Data\DocumentSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Silk\ProductImage\Api\Data\DocumentInterfaceFactory
     */
    protected $dataDocumentFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param ResourceDocument $resource
     * @param DocumentFactory $documentFactory
     * @param Data\DocumentInterfaceFactory $dataDocumentFactory
     * @param DocumentCollectionFactory $documentCollectionFactory
     * @param Data\DocumentSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceDocument $resource,
        DocumentFactory $documentFactory,
        \Silk\ProductImage\Api\Data\DocumentInterfaceFactory $dataDocumentFactory,
        DocumentCollectionFactory $documentCollectionFactory,
        Data\DocumentSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->resource = $resource;
        $this->documentFactory = $documentFactory;
        $this->documentCollectionFactory = $documentCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataDocumentFactory = $dataDocumentFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * Save Document data
     *
     * @param \Silk\ProductImage\Api\Data\DocumentInterface $document
     * @return Document
     * @throws CouldNotSaveException
     */
    public function save(Data\DocumentInterface $document)
    {

        try {
            $this->resource->save($document);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $document;
    }

    /**
     * Load Document data by given Document Identity
     *
     * @param string $documentId
     * @return Document
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($documentId)
    {
        $document = $this->documentFactory->create();
        $this->resource->load($document, $documentId);
        if (!$document->getId()) {
            throw new NoSuchEntityException(__('The CMS document with the "%1" ID doesn\'t exist.', $documentId));
        }
        return $document;
    }

    /**
     * Load Document data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Silk\ProductImage\Api\Data\DocumentSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        /** @var \Silk\ProductImage\Model\ResourceModel\Document\Collection $collection */
        $collection = $this->documentCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        /** @var Data\DocumentSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Delete Document
     *
     * @param \Silk\ProductImage\Api\Data\DocumentInterface $document
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\DocumentInterface $document)
    {
        try {
            $this->resource->delete($document);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Document by given Document Identity
     *
     * @param string $documentId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($documentId)
    {
        return $this->delete($this->getById($documentId));
    }

    /**
     * Retrieve collection processor
     *
     * @deprecated 102.0.0
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Silk\ProductImage\Model\Api\SearchCriteria\DocumentCollectionProcessor'
            );
        }
        return $this->collectionProcessor;
    }
}

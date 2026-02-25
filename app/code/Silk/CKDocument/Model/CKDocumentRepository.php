<?php

namespace Silk\CKDocument\Model;

use Silk\CKDocument\Api\CKDocumentRepositoryInterface;
use Silk\CKDocument\Api\Data;
use Silk\CKDocument\Model\ResourceModel\CKDocument as ResourceCKDocument;
use Silk\CKDocument\Model\ResourceModel\CKDocument\CollectionFactory as CKDocumentCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;


/**
 * Class CKDocumentRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CKDocumentRepository implements CKDocumentRepositoryInterface
{
    /**
     * @var ResourceCKDocument
     */
    protected $resource;

    /**
     * @var CKDocumentFactory
     */
    protected $documentFactory;

    /**
     * @var CKDocumentCollectionFactory
     */
    protected $documentCollectionFactory;

    /**
     * @var Data\CKDocumentSearchResultsInterfaceFactory
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
     * @var \Silk\CKDocument\Api\Data\CKDocumentInterfaceFactory
     */
    protected $dataCKDocumentFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param ResourceCKDocument $resource
     * @param CKDocumentFactory $documentFactory
     * @param Data\CKDocumentInterfaceFactory $dataCKDocumentFactory
     * @param CKDocumentCollectionFactory $documentCollectionFactory
     * @param Data\CKDocumentSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceCKDocument $resource,
        CKDocumentFactory $documentFactory,
        \Silk\CKDocument\Api\Data\CKDocumentInterfaceFactory $dataCKDocumentFactory,
        CKDocumentCollectionFactory $documentCollectionFactory,
        Data\CKDocumentSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->resource = $resource;
        $this->documentFactory = $documentFactory;
        $this->documentCollectionFactory = $documentCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataCKDocumentFactory = $dataCKDocumentFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * Save Document data
     *
     * @param \Silk\CKDocument\Api\Data\CKDocumentInterface $document
     * @return CKDocument
     * @throws CouldNotSaveException
     */
    public function save(Data\CKDocumentInterface $document)
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
     * @return CKDocument
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
     * @return \Silk\CKDocument\Api\Data\CKDocumentSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        /** @var \Silk\CKDocument\Model\ResourceModel\CKDocument\Collection $collection */
        $collection = $this->documentCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        /** @var Data\CKDocumentSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Delete Document
     *
     * @param \Silk\CKDocument\Api\Data\CKDocumentInterface $document
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\CKDocumentInterface $document)
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
                'Silk\CKDocument\Model\Api\SearchCriteria\CKDocumentCollectionProcessor'
            );
        }
        return $this->collectionProcessor;
    }
}

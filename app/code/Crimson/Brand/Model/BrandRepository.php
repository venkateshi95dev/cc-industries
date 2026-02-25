<?php
/**
 * @namespace   Crimson
 * @module      Brand
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        01/17/2019
 */
namespace Crimson\Brand\Model;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\NoSuchEntityException;
use Crimson\Brand\Api\BrandRepositoryInterface;
use Crimson\Brand\Api\Data\BrandInterface;
use Crimson\Brand\Api\Data\BrandInterfaceFactory;
use Crimson\Brand\Model\ResourceModel\Brand as ResourceBrand;
use Crimson\Brand\Model\ResourceModel\Brand\CollectionFactory as BrandCollectionFactory;
use Crimson\Brand\Api\Data\BrandSearchResultInterfaceFactory;

class BrandRepository implements BrandRepositoryInterface
{
    /**
     * @var array
     */
    protected $instances = [];

    /**
     * @var ResourceBrand
     */
    protected $resource;

    /**
     * @var BrandCollectionFactory
     */
    protected $brandCollectionFactory;

    /**
     * @var BrandInterfaceFactory
     */
    protected $brandInterfaceFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var BrandSearchResultInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * BrandRepository constructor.
     * @param ResourceBrand $resource
     * @param BrandCollectionFactory $brandCollectionFactory
     * @param BrandInterfaceFactory $brandInterfaceFactory
     * @param BrandSearchResultInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        ResourceBrand $resource,
        BrandCollectionFactory $brandCollectionFactory,
        BrandInterfaceFactory $brandInterfaceFactory,
        BrandSearchResultInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->resource = $resource;
        $this->brandCollectionFactory = $brandCollectionFactory;
        $this->brandInterfaceFactory = $brandInterfaceFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * @param BrandInterface $brand
     * @return BrandInterface|\Magento\Framework\Model\AbstractModel
     * @throws CouldNotSaveException
     */
    public function save(BrandInterface $brand)
    {
        try {
            /** @var BrandInterface|\Magento\Framework\Model\AbstractModel $brand */
            $this->resource->save($brand);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the Brand: %1',
                $exception->getMessage()
            ));
        }
        return $brand;
    }

    /**
     * Get Brand record
     * @param int $brandId
     * @return BrandInterface|mixed
     * @throws NoSuchEntityException
     */
    public function getById($brandId)
    {
        if (!isset($this->instances[$brandId])) {
            $brand = $this->brandInterfaceFactory->create();
            $this->resource->load($brand, $brandId);
            if (!$brand->getId()) {
                throw new NoSuchEntityException(__('Requested Brand doesn\'t exist'));
            }
            $this->instances[$brandId] = $brand;
        }
        return $this->instances[$brandId];
    }

    /**
     * @param BrandInterface $brand
     * @return bool
     * @throws CouldNotSaveException
     * @throws StateException
     */
    public function delete(BrandInterface $brand)
    {
        /** @var \Crimson\Brand\Api\Data\BrandInterface|\Magento\Framework\Model\AbstractModel $brand */
        $id = $brand->getId();
        try {
            unset($this->instances[$id]);
            $this->resource->delete($brand);
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new StateException(
                __('Unable to remove Brand %1', $id)
            );
        }
        unset($this->instances[$id]);
        return true;
    }

    /**
     * @param int $brandId
     * @return bool|BrandInterface
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function deleteById($brandId)
    {
        $brand = $this->getById($brandId);
        return $this->delete($brand);
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->brandCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var \Crimson\Brand\Api\Data\BrandSearchResultInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
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
                'Crimson\Brand\Model\Api\SearchCriteria\BrandCollectionProcessor'
            );
        }
        return $this->collectionProcessor;
    }
}

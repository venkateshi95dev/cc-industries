<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
declare(strict_types=1);

namespace Magedelight\Megamenu\Model;

use Magedelight\Megamenu\Api\Data\LabelInterface;
use Magedelight\Megamenu\Api\Data\LabelInterfaceFactory;
use Magedelight\Megamenu\Api\Data\LabelSearchResultsInterfaceFactory;
use Magedelight\Megamenu\Api\LabelRepositoryInterface;
use Magedelight\Megamenu\Model\ResourceModel\Label as ResourceLabel;
use Magedelight\Megamenu\Model\ResourceModel\Label\CollectionFactory as LabelCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class LabelRepository implements LabelRepositoryInterface
{

    /**
     * @var LabelInterfaceFactory
     */
    protected $labelFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var LabelCollectionFactory
     */
    protected $labelCollectionFactory;

    /**
     * @var ResourceLabel
     */
    protected $resource;

    /**
     * @var Label
     */
    protected $searchResultsFactory;


    /**
     * @param ResourceLabel $resource
     * @param LabelInterfaceFactory $labelFactory
     * @param LabelCollectionFactory $labelCollectionFactory
     * @param LabelSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceLabel $resource,
        LabelInterfaceFactory $labelFactory,
        LabelCollectionFactory $labelCollectionFactory,
        LabelSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->labelFactory = $labelFactory;
        $this->labelCollectionFactory = $labelCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(LabelInterface $label)
    {
        try {
            $this->resource->save($label);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the label: %1',
                $exception->getMessage()
            ));
        }
        return $label;
    }

    /**
     * @inheritDoc
     */
    public function get($labelId)
    {
        $label = $this->labelFactory->create();
        $this->resource->load($label, $labelId);
        if (!$label->getId()) {
            throw new NoSuchEntityException(__('Label with id "%1" does not exist.', $labelId));
        }
        return $label;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->labelCollectionFactory->create();
        
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
    public function delete(LabelInterface $label)
    {
        try {
            $labelModel = $this->labelFactory->create();
            $this->resource->load($labelModel, $label->getLabelId());
            $this->resource->delete($labelModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Label: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($labelId)
    {
        return $this->delete($this->get($labelId));
    }
}


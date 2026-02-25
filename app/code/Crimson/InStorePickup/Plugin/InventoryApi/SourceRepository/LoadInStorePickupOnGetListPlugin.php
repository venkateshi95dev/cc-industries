<?php

declare(strict_types=1);

namespace Crimson\InStorePickup\Plugin\InventoryApi\SourceRepository;

use Crimson\InStorePickup\Model\Source\InitPickupLocationExtensionAttributes;
use Magento\InventoryApi\Api\Data\SourceSearchResultsInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;

/**
 * Populate store pickup extension attribute when loading a list of orders.
 */
class LoadInStorePickupOnGetListPlugin
{

    public function __construct(
        private readonly InitPickupLocationExtensionAttributes $setExtensionAttributes
    ) {}

    /**
     * Enrich the given Source Objects with the In-Store pickup attribute
     *
     * @param SourceRepositoryInterface $subject
     * @param SourceSearchResultsInterface $sourceSearchResults
     *
     * @return SourceSearchResultsInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        SourceRepositoryInterface $subject,
        SourceSearchResultsInterface $sourceSearchResults
    ): SourceSearchResultsInterface {
        $items = $sourceSearchResults->getItems();
        array_walk(
            $items,
            [$this->setExtensionAttributes, 'execute']
        );

        return $sourceSearchResults;
    }
}

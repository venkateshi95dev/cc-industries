<?php

declare(strict_types=1);

namespace Crimson\InStorePickup\Plugin\InventoryApi\SourceRepository;

use Crimson\InStorePickup\Model\Source\InitPickupLocationExtensionAttributes;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;

/**
 * Populate store pickup extension attributes when loading single order.
 */
class LoadInStorePickupOnGetPlugin
{

    public function __construct(
        private readonly InitPickupLocationExtensionAttributes $setExtensionAttributes
    ) {}

    /**
     * Enrich the given Source Objects with the In-Store pickup attribute
     *
     * @param SourceRepositoryInterface $subject
     * @param SourceInterface $source
     *
     * @return SourceInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        SourceRepositoryInterface $subject,
        SourceInterface $source
    ): SourceInterface {
        $this->setExtensionAttributes->execute($source);

        return $source;
    }
}

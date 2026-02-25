<?php

declare(strict_types=1);

namespace Crimson\InStorePickup\Plugin\InventoryApi\SourceRepository;

use Magento\Framework\DataObject;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;

/**
 * Set data to Source itself from its extension attributes to save these values to `inventory_source` DB table.
 */
class SaveInStorePickupPlugin
{
    /**
     * Persist the In-Store pickup attribute on Source save
     *
     * @param SourceRepositoryInterface $subject
     * @param SourceInterface $source
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        SourceRepositoryInterface $subject,
        SourceInterface $source
    ): array {
        if (!$source instanceof DataObject) {
            return [$source];
        }

        $extensionAttributes = $source->getExtensionAttributes();
        if ($extensionAttributes !== null) {
            $source->setData('website_id', $extensionAttributes->getWebsiteId());
            $source->setData('allowed_sku', $extensionAttributes->getAllowedSku());
        }

        return [$source];
    }
}

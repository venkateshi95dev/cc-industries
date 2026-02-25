<?php

declare(strict_types=1);

namespace Crimson\InStorePickup\Model\Source;

use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\DataObject;
use Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * Set store-pickup related source extension attributes
 */
class InitPickupLocationExtensionAttributes
{

    public function __construct(
       private readonly ExtensionAttributesFactory $extensionAttributesFactory
    ){}

    /**
     * Set store-pickup related source extension attributes.
     *
     * @param SourceInterface $source
     */
    public function execute(SourceInterface $source): void
    {
        if (!$source instanceof DataObject) {
            return;
        }

        $websiteId  = $source->getData('website_id');
        $allowedSku = $source->getData('allowed_sku');
        $extensionAttributes = $source->getExtensionAttributes();
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->extensionAttributesFactory->create(SourceInterface::class);
            /** @noinspection PhpParamsInspection */
            $source->setExtensionAttributes($extensionAttributes);
        }

        $extensionAttributes->setWebsiteId($websiteId);
        $extensionAttributes->setAllowedSku($allowedSku);
    }
}

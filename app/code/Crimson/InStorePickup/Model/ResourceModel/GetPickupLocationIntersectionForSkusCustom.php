<?php

namespace Crimson\InStorePickup\Model\ResourceModel;

use Crimson\InStorePickup\Model\InStorePickupConfig;
use Magento\Checkout\Model\Session;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\ExpressionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Inventory\Model\ResourceModel\Source;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryInStorePickup\Model\ResourceModel\GetPickupLocationIntersectionForSkus;
use Magento\InventoryInStorePickupApi\Api\Data\PickupLocationInterface;
use Magento\Store\Model\StoreManagerInterface;

class GetPickupLocationIntersectionForSkusCustom extends GetPickupLocationIntersectionForSkus
{

    public function __construct(
        protected SourceItem            $sourceItemResource,
        protected Source                $sourceResource,
        protected StoreManagerInterface $storeManager,
        protected InStorePickupConfig   $inStorePickupConfig,
        protected ExpressionFactory     $expressionFactory
    )
    {
        parent::__construct($sourceItemResource, $sourceResource, $expressionFactory);
    }

    /**
     * Provide intersection of products availability in sources.
     *
     * @param string[] $skus
     *
     * @return array
     * @throws LocalizedException
     */
    public function execute(array $skus): array
    {
        $select = $this->sourceResource->getConnection()
            ->select()
            ->from($this->sourceResource->getMainTable())
            ->where(SourceInterface::ENABLED . ' = 1')
            ->where(PickupLocationInterface::IS_PICKUP_LOCATION_ACTIVE . ' = 1')
            ->where('website_id' . ' = ' . $this->storeManager->getWebsite()->getId())
            ->reset(Select::COLUMNS)
            ->columns([SourceInterface::SOURCE_CODE, InStorePickupConfig::SOURCE_COLUMN_ALLOWED_SKU]);
        $result = $this->sourceItemResource->getConnection()->fetchAssoc($select);

        return $this->_filterSourcesLimitations($skus, $result);
    }

    private function _filterSourcesLimitations(array $skus, array $queryResult): array
    {
        $sourceCodes = [];
        if ($this->inStorePickupConfig->isLocationAllMustMatchEnabled()) {
            foreach ($queryResult as $row) {
                if (empty($row[InStorePickupConfig::SOURCE_COLUMN_ALLOWED_SKU])) {
                    $sourceCodes[] = $row[SourceItemInterface::SOURCE_CODE];
                    continue;
                }

                if (count(array_intersect($skus, explode(',', $row[InStorePickupConfig::SOURCE_COLUMN_ALLOWED_SKU]))) === count($skus)) {
                    $sourceCodes[] = $row[SourceItemInterface::SOURCE_CODE];
                    continue;
                }
            }
        } else {
            foreach ($queryResult as $row) {
                $sourceCodes[] = $row[SourceItemInterface::SOURCE_CODE];
            }
        }

        return $sourceCodes;
    }
}

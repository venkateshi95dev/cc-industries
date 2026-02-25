<?php

declare(strict_types=1);

namespace Crimson\InStorePickup\Model\SearchCriteria;

use Magento\InventoryInStorePickupApi\Api\Data\SearchRequestInterface;
use Magento\InventoryInStorePickupApi\Model\SearchCriteria\ResolverInterface;
use Magento\InventoryInStorePickupApi\Model\SearchCriteria\SearchCriteriaBuilderDecorator;

/**
 * Add filter by Source Codes which are related to Requested Scope.
 *
 * In case of Distance Filter present in Search Request, the filter will not be added.
 */
class ResolveScopeFilter implements ResolverInterface
{

    public function resolve(
        SearchRequestInterface $searchRequest,
        SearchCriteriaBuilderDecorator $searchCriteriaBuilder
    ): void {

    }
}

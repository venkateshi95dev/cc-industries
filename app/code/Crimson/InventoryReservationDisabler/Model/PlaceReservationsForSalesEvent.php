<?php

namespace Crimson\InventoryReservationDisabler\Model;

use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface;

class PlaceReservationsForSalesEvent implements PlaceReservationsForSalesEventInterface
{
    public function execute(
        array $items,
        SalesChannelInterface $salesChannel,
        SalesEventInterface $salesEvent
    ): void {
        //do nothing
    }
}

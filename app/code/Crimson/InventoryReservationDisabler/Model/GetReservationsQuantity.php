<?php

namespace Crimson\InventoryReservationDisabler\Model;

use Magento\InventoryReservationsApi\Model\GetReservationsQuantityInterface;

class GetReservationsQuantity implements GetReservationsQuantityInterface
{
    public function execute(string $sku, int $stockId): float
    {
        return 0;
    }
}

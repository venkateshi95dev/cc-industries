<?php

namespace Crimson\InventoryReservationDisabler\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryReservationsApi\Model\CleanupReservationsInterface;

class CleanupReservations implements CleanupReservationsInterface
{
    public function __construct(
        protected ResourceConnection $resource
    ) {}

    public function execute(): void
    {
        $connection = $this->resource->getConnection();
        $reservationTable = $this->resource->getTableName('inventory_reservation');

        //we don't use reservations, remove them all.
        $connection->delete($reservationTable);
    }
}

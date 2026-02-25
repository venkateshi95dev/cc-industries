<?php

namespace Crimson\InventoryReservationDisabler\Model;

use Magento\InventoryReservationsApi\Model\AppendReservationsInterface;

class AppendReservations implements AppendReservationsInterface
{
    public function execute(array $reservations): void
    {
        //do nothing
    }
}

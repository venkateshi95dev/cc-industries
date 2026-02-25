<?php

namespace Crimson\MachInventoryImport\Service;

use Magento\Framework\App\ResourceConnection;

/**
 * Class ClearAllReservations
 * @package Crimson\MachInventoryImport\Service
 */
class ClearAllReservations
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     *
     */
    public function execute(): void
    {
        $connection = $this->resource->getConnection();
        $reservationTable = $this->resource->getTableName('inventory_reservation');
        $connection->delete($reservationTable);
    }
}

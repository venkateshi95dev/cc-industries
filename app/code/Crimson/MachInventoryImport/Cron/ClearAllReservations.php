<?php

namespace Crimson\MachInventoryImport\Cron;

use Crimson\MachInventoryImport\Service\ClearAllReservations as ClearAllReservationsService;

class ClearAllReservations
{

    protected ClearAllReservationsService $clearAllReservationsService;

    public function __construct(
        ClearAllReservationsService $clearAllReservationsService
    ) {
        $this->clearAllReservationsService = $clearAllReservationsService;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        try {
            $this->clearAllReservationsService->execute();
        } catch (\Exception $e) {
        }
    }
}

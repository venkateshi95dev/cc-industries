<?php

namespace Crimson\Catalog\Cron;

use Crimson\Catalog\Service\AdjustInventorySourceItems as ServiceAdjustInventorySourceItems;

/**
 * Class AdjustInventorySourceItems
 * @package Crimson\Catalog\Cron
 */
class AdjustInventorySourceItems
{

    public function __construct(
        protected ServiceAdjustInventorySourceItems $serviceAdjustInventorySourceItems
    ) {}


    public function execute()
    {
        $this->serviceAdjustInventorySourceItems->execute();
    }
}

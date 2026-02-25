<?php

namespace Crimson\InventorySalesDeduction\Plugin\Model;

use Crimson\InventorySalesDeduction\Service\DeductOrderInventory;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class OrderPlugin
{

    public function __construct(
        protected DeductOrderInventory $deductOrderInventory,
        protected LoggerInterface $loggerInterface
    ) {
    }

    public function afterPlace(Order $order, $result)
    {
        try {
            $this->deductOrderInventory->deduct($order);
        } catch (\Exception $e) {
            $this->loggerInterface->error($e->getMessage());
        } finally {
            return $result;
        }
    }
}

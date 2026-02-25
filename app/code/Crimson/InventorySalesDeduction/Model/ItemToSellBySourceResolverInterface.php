<?php
declare(strict_types=1);

namespace Crimson\InventorySalesDeduction\Model;

use Magento\InventorySalesApi\Api\Data\ItemToSellInterface;

interface ItemToSellBySourceResolverInterface
{
    public function getSourceCode(): string;

    public function resolve(ItemToSellInterface $itemToSell): ?ItemToSellInterface;
}

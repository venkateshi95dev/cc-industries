<?php

namespace Crimson\InventorySalesDeduction\Service;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory;
use Magento\InventorySourceDeductionApi\Model\GetSourceItemBySourceCodeAndSku;
use Magento\InventorySourceDeductionApi\Model\SourceDeductionServiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

class DeductOrderInventory
{

    public function __construct(
        protected IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType,
        protected ItemToSellInterfaceFactory                           $itemsToSellFactory,
        protected GenerateSourceDeductionRequests                      $generateSourceDeductionRequests,
        protected GetSourceItemBySourceCodeAndSku                      $getSourceItemBySourceCodeAndSku,
        protected DefaultSourceProviderInterface                       $defaultSourceProvider,
        protected SourceDeductionServiceInterface                      $sourceDeductionService
    ) {}

    /**
     * @param OrderInterface $order
     * @return void
     * @throws NoSuchEntityException
     */
    public function deduct(OrderInterface $order): void
    {
        $sourceCode = $this->defaultSourceProvider->getCode();
        $itemsToSell = [];
        foreach ($order->getItems() as $orderItem) {
            if (!$orderItem->getProductId()
                || false === $this->isSourceItemManagementAllowedForProductType->execute($orderItem->getProductType())
                || $this->shouldBeExcluded($orderItem, $sourceCode)
            ) {
                continue;
            }

            $itemToSell = $this->itemsToSellFactory->create([
                'sku' => $orderItem->getSku(),
                'qty' => (float)$orderItem->getQtyOrdered()
            ]);
            $itemToSell->getExtensionAttributes()->setOrderItem($orderItem);
            $itemsToSell[] = $itemToSell;
        }

        $sourceDeductionRequest = $this->generateSourceDeductionRequests->get($order, $itemsToSell, $sourceCode);
        $this->sourceDeductionService->execute($sourceDeductionRequest);
    }

    /**
     * @param OrderItemInterface $orderItem
     * @param string $sourceCode
     * @return bool
     */
    private function shouldBeExcluded(OrderItemInterface $orderItem, string $sourceCode): bool
    {
        try {
            $sourceItem = $this->getSourceItemBySourceCodeAndSku->execute($sourceCode, $orderItem->getSku());

            return !($sourceItem->getQuantity() > 0);
        } catch (\Exception $e) {
            return true;
        }

    }
}

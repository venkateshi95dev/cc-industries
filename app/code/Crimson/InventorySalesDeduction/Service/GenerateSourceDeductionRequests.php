<?php

namespace Crimson\InventorySalesDeduction\Service;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesEventExtensionFactory;
use Magento\InventorySalesApi\Api\Data\SalesEventExtensionInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory;
use Magento\InventorySourceDeductionApi\Model\ItemToDeductInterface;
use Magento\InventorySourceDeductionApi\Model\ItemToDeductInterfaceFactory;
use Magento\InventorySourceDeductionApi\Model\SourceDeductionRequestInterface;
use Magento\InventorySourceDeductionApi\Model\SourceDeductionRequestInterfaceFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class GenerateSourceDeductionRequests
{
    protected ItemToDeductInterfaceFactory $itemToDeductInterfaceFactory;
    protected SalesChannelInterfaceFactory $salesChannelFactory;
    protected SalesEventExtensionFactory $salesEventExtensionFactory;
    protected SalesEventInterfaceFactory $salesEventFactory;
    protected SourceDeductionRequestInterfaceFactory $sourceDeductionRequestFactory;
    protected StoreManagerInterface $storeManager;
    protected WebsiteRepositoryInterface $websiteRepository;

    public function __construct(
        SourceDeductionRequestInterfaceFactory $sourceDeductionRequestFactory,
        SalesChannelInterfaceFactory $salesChannelFactory,
        SalesEventInterfaceFactory $salesEventFactory,
        WebsiteRepositoryInterface $websiteRepository,
        SalesEventExtensionFactory $salesEventExtensionFactory,
        StoreManagerInterface $storeManager,
        ItemToDeductInterfaceFactory $itemToDeductInterfaceFactory
    ) {
        $this->sourceDeductionRequestFactory = $sourceDeductionRequestFactory;
        $this->salesChannelFactory = $salesChannelFactory;
        $this->salesEventFactory = $salesEventFactory;
        $this->websiteRepository = $websiteRepository;
        $this->salesEventExtensionFactory = $salesEventExtensionFactory;
        $this->storeManager = $storeManager;
        $this->itemToDeductInterfaceFactory = $itemToDeductInterfaceFactory;
    }

    /**
     * @param OrderInterface $order
     * @param array $itemsToSell
     * @param string $sourceCode
     * @return SourceDeductionRequestInterface
     * @throws NoSuchEntityException
     */
    public function get(OrderInterface $order, array $itemsToSell, string $sourceCode): SourceDeductionRequestInterface
    {
        $salesChannel = $this->_getSalesChannel($order);
        $salesEvent   = $this->_getSalesEvent($order);

        $itemsToDeduct = array_map(function (ItemToSellInterface $itemToSell) {
            return $this->_convertItemToSellToDeduct($itemToSell);
        }, $itemsToSell);

        return $this->sourceDeductionRequestFactory->create([
            'sourceCode' => $sourceCode,
            'items' => $itemsToDeduct,
            'salesChannel' => $salesChannel,
            'salesEvent' => $salesEvent
        ]);
    }

    /**
     * @param ItemToSellInterface $itemToSell
     * @return ItemToDeductInterface
     */
    protected function _convertItemToSellToDeduct(ItemToSellInterface $itemToSell): ItemToDeductInterface
    {
        /** @var ItemToDeductInterface $itemToDeduct */
        return $this->itemToDeductInterfaceFactory->create([
            'sku' => $itemToSell->getSku(),
            'qty' => $this->castQty($itemToSell->getExtensionAttributes()->getOrderItem(), $itemToSell->getQuantity()),
        ]);
    }

    /**
     * @param OrderInterface $order
     *
     * @return SalesEventInterface
     */
    protected function _getSalesEvent(OrderInterface $order): SalesEventInterface
    {
        /** @var SalesEventExtensionInterface */
        $salesEventExtension = $this->salesEventExtensionFactory->create([
            'data' => ['objectIncrementId' => (string)$order->getIncrementId()]
        ]);
        $salesEvent          = $this->salesEventFactory->create([
            'type'       => SalesEventInterface::EVENT_ORDER_PLACED,
            'objectType' => SalesEventInterface::OBJECT_TYPE_ORDER,
            'objectId'   => (string)$order->getEntityId()
        ]);
        $salesEvent->setExtensionAttributes($salesEventExtension);

        return $salesEvent;
    }

    /**
     * @param OrderInterface $order
     *
     * @return SalesChannelInterface
     * @throws NoSuchEntityException
     */
    protected function _getSalesChannel(OrderInterface $order): SalesChannelInterface
    {
        $storeId      = $order->getStoreId();
        $websiteId    = $this->storeManager->getStore($storeId)->getWebsiteId();
        $websiteCode  = $this->websiteRepository->getById($websiteId)->getCode();

        return $this->salesChannelFactory->create([
            'data' => [
                'type' => SalesChannelInterface::TYPE_WEBSITE,
                'code' => $websiteCode
            ]
        ]);
    }

    /**
     * @param OrderItemInterface $item
     * @param $qty
     * @return float|int
     */
    private function castQty(OrderItemInterface $item, $qty)
    {
        if ($item->getIsQtyDecimal()) {
            return (double)$qty;
        } else {
            return (int)$qty;
        }
    }
}

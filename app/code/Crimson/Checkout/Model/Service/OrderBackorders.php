<?php

namespace Crimson\Checkout\Model\Service;

use Crimson\MachBase\Model\Api\HealthCheck;
use Crimson\MachCatalog\Model\Api\MultiInventory as MultiInventoryApi;
use Crimson\MachCatalog\Model\Api\Result\MultiInventoryResult;
use Crimson\MachCatalog\Model\Service\GetProductStockInfo;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Store\Model\StoreManagerInterface;

class OrderBackorders
{
    /**
     * @var GetProductStockInfo
     */
    protected $getProductStockInfo;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var MultiInventoryApi
     */
    protected $inventoryApi;

    /**
     * @var HealthCheck
     */
    protected $healthCheck;


    public function __construct(
        GetProductStockInfo $getProductStockInfo,
        StoreManagerInterface $storeManager,
        HealthCheck $healthCheck,
        MultiInventoryApi $inventoryApi
    ) {
        $this->getProductStockInfo  = $getProductStockInfo;
        $this->storeManager         = $storeManager;
        $this->healthCheck          = $healthCheck;
        $this->inventoryApi         = $inventoryApi;
    }

    /**
     * @param OrderInterface $order
     *
     * @return bool
     */
    public function doesOrderHaveBackOrders(OrderInterface $order): bool
    {
        $result = false;
        $checkLocal = false;
        if ($this->healthCheck->isUp()) {
            $skuList = [];
            foreach ($order->getAllVisibleItems() as $item) {
                if (!empty($item->getSku())) {
                    $skuList[] = $item->getSku();
                }
            }

            if (count($skuList)) {
                //forcing live MultiItem inventory call
                $resultFromMach = $this->inventoryApi->get($skuList, true);
                if ($resultFromMach instanceof MultiInventoryResult && $resultFromMach->getResponseStatus()) {
                    $result = $this->doesCartHaveBackOrdersFromMachResponse($order, $resultFromMach);
                } else {
                    $checkLocal = true;
                }
            }

        } else {
            $checkLocal = true;
        }

        //Mach was down or even up didn't return a valid response, so we check locally
        if ($checkLocal) {
            $result = $this->doesOrderHaveBackOrdersFromLocalInventory($order);
        }

        return $result;
    }

    /**
     * @param OrderInterface $order
     * @param MultiInventoryResult $resultFromMach
     * @return bool
     */
    public function doesCartHaveBackOrdersFromMachResponse(OrderInterface $order, MultiInventoryResult $resultFromMach): bool
    {
        foreach ($order->getAllVisibleItems() as $item) {
            /** @var OrderItemInterface $item */
            if ($resultFromMach->getIsSkuInResponse($item->getSku())) {
                $qtyFromMach = $resultFromMach->getQtyAvailable($item->getSku());
                if ($qtyFromMach === false) {
                    $qtyFromMach = 0;
                }
                $requestedQty = $item->getQtyOrdered();
                $backOrderQty = $requestedQty - $qtyFromMach;
                if ($backOrderQty > 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function doesOrderHaveBackOrdersFromLocalInventory(OrderInterface $order): bool
    {
        $storeId   = $order->getStoreId();

        foreach ($order->getItems() as $item) {

            if ($item->getProductType() != "simple") {
                continue;
            }

            if ($this->_isItemBackOrder($item, $storeId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $item
     * @param int $storeId
     * @return bool
     */
    protected function _isItemBackOrder($item, int $storeId): bool
    {
        try {
            $product = $item->getProduct();
            if ($product && $product->getId()) {
                //Forcing to get the stock info from Magento
                $stockInfo = $this->getProductStockInfo->get($product, $storeId, true);
                if ((int)$stockInfo[GetProductStockInfo::BACKORDERS_CODE] >= 0
                    && (int)$stockInfo[GetProductStockInfo::QTY_CODE] <= 0
                ) {
                    return true;
                }
            }

            return false;

        } catch (\Exception $e) {
            return false;
        }
    }

}

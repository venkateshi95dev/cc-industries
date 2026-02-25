<?php
/**
 * @namespace   Crimson
 * @module      Checkout
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/13/2019 5:58 PM
 * @brief
 */

namespace Crimson\Checkout\Model\Service;

use Crimson\MachBase\Model\Api\HealthCheck;
use Crimson\MachCatalog\Model\Api\MultiInventory as MultiInventoryApi;
use Crimson\MachCatalog\Model\Api\Result\MultiInventoryResult;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Exception\SkuIsNotAssignedToStockException;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Model\GetStockItemDataInterface;
use Crimson\MachCatalog\Model\ResourceModel\GetStockItemData as GetStockItemDataCustom;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;

class CartBackorders
{
    /**
     * @var int[]
     */
    protected $_stockId;
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;
    /**
     * @var GetStockItemConfigurationInterface
     */
    protected $getStockItemConfiguration;
    /**
     * @var GetStockItemDataInterface
     */
    protected $getStockItemData;
    /**
     * @var \Magento\InventorySalesApi\Api\StockResolverInterface
     */
    protected $stockResolver;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var HealthCheck
     */
    protected $healthCheck;

    /**
     * @var MultiInventoryApi
     */
    protected $inventoryApi;

    /**
     * @var GetStockItemDataCustom
     */
    protected $getStockItemDataCustom;


    public function __construct(
        StoreManagerInterface $storeManager,
        CheckoutSession $checkoutSession,
        \Magento\InventorySalesApi\Api\StockResolverInterface $stockResolver,
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        GetStockItemDataInterface $getStockItemData,
        HealthCheck $healthCheck,
        GetStockItemDataCustom $getStockItemDataCustom,
        MultiInventoryApi $inventoryApi
    ) {
        $this->checkoutSession           = $checkoutSession;
        $this->storeManager              = $storeManager;
        $this->stockResolver             = $stockResolver;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->getStockItemData          = $getStockItemData;
        $this->healthCheck               = $healthCheck;
        $this->getStockItemDataCustom    = $getStockItemDataCustom;
        $this->inventoryApi              = $inventoryApi;
    }

    /**
     * @param CartInterface|null $quote
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws SkuIsNotAssignedToStockException
     */
    public function doesCartHaveBackOrders(CartInterface $quote = null): bool
    {
        $result = false;
        $checkLocal = false;
        if ($this->healthCheck->isUp()) {
            $skuList = [];
            foreach ($quote->getAllVisibleItems() as $item) {
                if (!empty($item->getSku())) {
                    $skuList[] = $item->getSku();
                }
            }

            if (count($skuList)) {
                $resultFromMach = $this->inventoryApi->get($skuList);
                if ($resultFromMach instanceof MultiInventoryResult && $resultFromMach->getResponseStatus()) {
                    $result = $this->doesCartHaveBackOrdersFromMachResponse($quote, $resultFromMach);
                } else {
                    $checkLocal = true;
                }
            }

        } else {
            $checkLocal = true;
        }

        //Mach was down or even up didn't return a valid response, so we check locally
        if ($checkLocal) {
            $result = $this->doesCartHaveBackOrdersFromLocalInventory($quote);
        }

        return $result;
    }

    /**
     * @param CartInterface $quote
     * @param MultiInventoryResult $resultFromMach
     * @return bool
     */
    public function doesCartHaveBackOrdersFromMachResponse(CartInterface $quote, MultiInventoryResult $resultFromMach): bool
    {
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($resultFromMach->getIsSkuInResponse($item->getSku())) {
                $qtyFromMach = $resultFromMach->getQtyAvailable($item->getSku());
                if ($qtyFromMach === false) {
                    $qtyFromMach = 0;
                }
                $requestedQty = $item->getQty();
                $backOrderQty = $requestedQty - $qtyFromMach;
                if ($backOrderQty > 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param CartInterface|null $quote
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws SkuIsNotAssignedToStockException
     */
    public function doesCartHaveBackOrdersFromLocalInventory(CartInterface $quote = null): bool
    {
        $store   = $this->storeManager->getStore($quote->getStoreId());
        $stockId = $this->_getStockId($store->getWebsiteId());

        foreach ($quote->getItems() as $item) {
            /** @var Item $item */
            if ($this->_isItemOnBackOrder($item, $stockId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $websiteId
     *
     * @return int
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function _getStockId($websiteId): int
    {
        if (!($this->_stockId[$websiteId] ?? false)) {
            $website     = $this->storeManager->getWebsite($websiteId);
            $websiteCode = $website->getCode();

            $this->_stockId[$websiteId] = $this->stockResolver
                ->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode)->getStockId();
        }

        return $this->_stockId[$websiteId];
    }

    /**
     * @param Item $item
     * @param int $stockId
     * @return bool
     * @throws LocalizedException
     * @throws SkuIsNotAssignedToStockException
     */
    protected function _isItemOnBackOrder(Item $item, int $stockId): bool
    {
        $sku                    = $item->getSku();
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);

        //if item cannot have backorders, why check?
        if ($stockItemConfiguration->getBackorders() === StockItemConfigurationInterface::BACKORDERS_NO) {
            return false;
        }

        //Forcing here to check locally on Magento inventory
        $stockItemData = $this->getStockItemDataCustom->execute($sku, $stockId, true);
        if (null === $stockItemData) {
            //if no stock item data, exit
            return false;
        }

        $requestedQty = $this->getProductQty($item);
        $backOrderQty = $requestedQty - $stockItemData[GetStockItemDataInterface::QUANTITY];
        if ($backOrderQty <= 0) {
            return false;
        }

        return true;
    }

    /**
     * @param Item $item
     *
     * @return float|int
     */
    public function getProductQty(Item $item)
    {
        $qty = 0;
        $productId = $item->getProductId();
        if (!$productId) {
            return $qty;
        }

        $children = $item->getChildren();
        if ($children) {
            foreach ($children as $childItem) {
                $qty += $this->getProductQty($childItem);
            }

            return $qty;
        } else {
            return $item->getTotalQty();
        }
    }
}

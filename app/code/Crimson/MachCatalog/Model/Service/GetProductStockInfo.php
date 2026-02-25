<?php

namespace Crimson\MachCatalog\Model\Service;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryConfigurationApi\Exception\SkuIsNotAssignedToStockException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Model\GetStockItemDataInterface;
use Crimson\MachCatalog\Model\ResourceModel\GetStockItemData as GetStockItemDataCustom;

/**
 * Class GetProductStockInfo
 * @package Crimson\MachCatalog\Model\Service
 */
class GetProductStockInfo
{

    CONST QTY_CODE        = 'qty';
    CONST BACKORDERS_CODE = 'backorders';

    /**
     * @var int[]
     */
    protected $_stockId;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var GetStockItemDataInterface
     */
    protected $getStockItemData;

    /**
     * @var GetStockItemDataCustom
     */
    protected $getStockItemDataCustom;

    /**
     * @var StockResolverInterface
     */
    protected $stockResolver;

    /**
     * @var GetStockItemConfigurationInterface
     */
    protected $getStockItemConfiguration;

    public function __construct(
        StockResolverInterface $stockResolver,
        StoreManagerInterface $storeManager,
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        GetStockItemDataCustom $getStockItemDataCustom,
        GetStockItemDataInterface $getStockItemData
    ) {
        $this->storeManager  = $storeManager;
        $this->stockResolver = $stockResolver;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->getStockItemDataCustom = $getStockItemDataCustom;
        $this->getStockItemData       = $getStockItemData;
    }

    /**
     * $forceLocalInventory is gonna define if the info is needed from Magento or Mach
     *
     * @param ProductInterface $product
     * @param int $storeId
     * @param bool $forceLocalInventory
     * @return array|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws SkuIsNotAssignedToStockException
     */
    public function get(ProductInterface $product, int $storeId, $forceLocalInventory = false): ?array
    {
        $result = [];
        $store   = $this->storeManager->getStore($storeId);
        $stockId = $this->_getStockId($store->getWebsiteId());

        $sku           = $product->getSku();
        $stockItemData = $this->getStockItemDataCustom->execute($sku, $stockId, $forceLocalInventory);

        if (null === $stockItemData) {
            //if no stock item data, exit
            return null;
        }

        //Qty
        $result[self::QTY_CODE] = $stockItemData[GetStockItemDataInterface::QUANTITY];

        //Backorders
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);
        $result[self::BACKORDERS_CODE] = $stockItemConfiguration->getBackorders();

        return $result;
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

}

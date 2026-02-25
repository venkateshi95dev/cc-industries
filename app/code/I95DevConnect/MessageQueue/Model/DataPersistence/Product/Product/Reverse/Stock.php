<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev (https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Product\Product\Reverse;

use I95DevConnect\MessageQueue\Helper\Data;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * set stock details for product
 * @createdBy Arushi Bansal
 */
class Stock
{
    /**
     * @var StockItemInterfaceFactory
     */
    public $stockItem;

    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * Stock constructor.
     *
     * @param StockItemInterfaceFactory $stockItem
     * @param Data $dataHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StockItemInterfaceFactory $stockItem,
        Data $dataHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->stockItem = $stockItem;
        $this->dataHelper = $dataHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * Set product stock information
     *
     * @param string $stringData
     * @param bool $manageStock
     *
     * @return object
     * @createdBy Arushi Bansal
     */
    public function setStockInformation($stringData, $manageStock = true)
    {
        /* adding the inventory fields for product if its new*/
        $qty = $this->dataHelper->getValueFromArray("qty", $stringData);
        $thresholdQty = $this->threshholdQty();

        if (array_key_exists("backorders", $stringData)) {
            $useConfigBackorders = false;
            $backorders = $this->dataHelper->getValueFromArray("backorders", $stringData);
            $isInStock = ($backorders || $qty >= $thresholdQty) ? 1 : 0;
        } else {
            $useConfigBackorders = true;
            $backorders = $this->dataHelper->getManageStock();
            $isInStock = ($backorders != 0 || $qty >= $thresholdQty) ? 1 : 0;
        }

        $stockItem = $this->stockItem->create();
        $stockItem->setQty($qty);
        $stockItem->setManageStock($manageStock);
        /* updatedBy Ranjith R, setUseConfigManageStock has been set to true if not set unable to add
        configurable product to cart throwing the error product out of stock though it is in stock */
        $stockItem->setUseConfigManageStock(true);
        $stockItem->setIsInStock($isInStock);
        $stockItem->setUseConfigBackorders($useConfigBackorders);

        if (!$useConfigBackorders) {
            $stockItem->setBackorders($backorders);
        }

        return $stockItem;
    }

    /**
     * Get the threshhold quantity from config
     *
     * @return int
     * @createdBy Arushi Bansal
     */
    public function threshholdQty()
    {
        return $this->dataHelper->getscopeConfig(
            'cataloginventory/item_options/min_qty',
            ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getDefaultStoreView()->getWebsiteId()
        );
    }
}

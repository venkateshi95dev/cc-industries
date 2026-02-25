<?php

namespace Crimson\MachShipping\Model\Shipping\Method;

use Crimson\MachCatalog\Model\Service\GetProductStockInfo;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Crimson\MachShipping\Model\Config;
use Crimson\MachCatalog\Model\Service\GetMultiItemInventory;
use Crimson\MachCatalog\Model\Api\Result\MultiInventoryResult;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;

class Filter
{
    const DROPSHIP_OPTIONS   = 'carriers/mach/allow_if_dropship';
    const BACKORDER_OPTIONS  = 'carriers/mach/allow_if_backorder';

    protected int $_storeId = 0;
    protected bool $_isDropShip = false;
    protected bool $_isHazardousMaterial = false;
    protected ?array $_items = null;
    protected bool $_isBackOrdered = false;
    protected bool $_isEtaPossible = false;
    protected array $_dropShipMethods = array();

    protected StoreManagerInterface $_storeManager;
    protected ScopeConfigInterface $_scopeConfig;
    protected ProductRepositoryInterface $_productRepository;
    protected GetProductStockInfo $getProductStockInfo;
    protected Config $config;
    protected GetMultiItemInventory $getMultiInventory;

    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        ProductRepositoryInterface $productRepository,
        GetProductStockInfo $getProductStockInfo,
        Config $config,
        GetMultiItemInventory $getMultiInventory
    ) {
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_productRepository = $productRepository;
        $this->_storeId  = $this->_storeManager->getStore()->getId();
        $this->getProductStockInfo = $getProductStockInfo;
        $this->config = $config;
        $this->getMultiInventory = $getMultiInventory;
    }

    /**
     * @param array $items
     * @param array $allowedMethods
     * @return array
     * @throws NoSuchEntityException
     */
    public function filterShippingMethods(array $items, array $allowedMethods): array
    {
        $this->_items			= $items;
        $this->_dropShipMethods = $this->_getMethods(self::DROPSHIP_OPTIONS);
        $this->_isEtaPossible   = false;

        if ($this->config->getCanCallMach()) {
            $processed = $this->_processItemsCallingMach();
            //if something failed with the call we check locally on Magento
            if (!$processed) {
                $this->_processItems();
            }
        } else {
            $this->_processItems();
        }

        $_result = [
            'filtered_methods' => $allowedMethods,
            'is_eta_possible'  => $this->_isEtaPossible
        ];

        if ($this->_isDropShip || $this->_isBackOrdered || $this->_isHazardousMaterial) {
            $_result['filtered_methods'] = $this->_filterMethods($allowedMethods);
        }

        return $_result;
    }

    protected function _filterMethods(array $allowedMethods): array
    {
        $_allowedFilteredMethods = [];
        $_methodIds = array_keys($allowedMethods);
        foreach ($_methodIds as $_methodId) {
            if (in_array($_methodId, $this->_dropShipMethods)) {
                $_allowedFilteredMethods[$_methodId] = $allowedMethods[$_methodId];
            }
        }

        return $_allowedFilteredMethods;
    }

    /**
     * Processing items to exclude shipping options if some conditions, BUT not calling Mach, forcing to get
     * the info from Magento
     */
    protected function _processItems(): void
    {
        foreach ($this->_items as $_item) {
            if ($_item->getProductType() !== "simple") {
                continue;
            }

            try {
                $_product = $this->_productRepository->getById($_item->getProductId());
            } catch (\Exception $e) {
                continue;
            }

            if ((int)$_product->getId() === 0) {
                continue;
            }

            $dropShip = $_product->getCustomAttribute('ships_from_manufacturer')
                ? (bool) $_product->getCustomAttribute('ships_from_manufacturer')->getValue()
                : false;
            if ($dropShip) {
                $this->_isDropShip = true;
                continue;
            }

            try {
                //this needs to be determined based on the info in Magento, NOT Mach, so we force it
                $stockInfo = $this->getProductStockInfo->get($_product, $_item->getStoreId(), true);
            } catch (\Exception $exception) {
                continue;
            }

            if (!$stockInfo) {
                continue;
            }

            if (
                (isset($stockInfo[GetProductStockInfo::BACKORDERS_CODE])
                    && (int)$stockInfo[GetProductStockInfo::BACKORDERS_CODE] >= 0)
                && (isset($stockInfo[GetProductStockInfo::QTY_CODE])
                    && (int)$stockInfo[GetProductStockInfo::QTY_CODE] <= 0)
            ) {
                $this->_isBackOrdered = true;
                continue;
            }

            //verifying hazardous material
            $hazardousMaterial = $_product->getCustomAttribute('hazardous_material')
                ? $this->_castStringToBool($_product->getAttributeText('hazardous_material'))
                : false;
            if ($hazardousMaterial) {
                $this->_isHazardousMaterial = true;
                continue;
            }

            //Item found that makes ETA possible
            $this->_isEtaPossible = true;
        }
    }

    protected function _processItemsCallingMach(): bool
    {
        try {
            $multiInvResult = $this->getMultiInventory->getMultiInvFilteringShipping($this->_items);
            if ($multiInvResult instanceof MultiInventoryResult && $multiInvResult->getResponseStatus()) {
                foreach ($this->_items as $_item) {
                    if ($_item->getProductType() !== "simple") {
                        continue;
                    }

                    $_product = $_item->getProduct();

                    if (!$_product || (int)$_product->getId() === 0 || empty($_product->getSku())) {
                        continue;
                    }

                    //verifying dropship
                    if ($multiInvResult->getDropship($_product->getSku())) {
                        $this->_isDropShip = true;
                        continue;
                    }

                    //checking stock from Mach, if partial availability or backorder then no express ship
                    $qtyAvailFromMach = $multiInvResult->getQtyAvailable($_product->getSku());
                    if ($qtyAvailFromMach === false || $qtyAvailFromMach == 0 || $this->isPartialAvailability($_item, $qtyAvailFromMach)) {
                        $this->_isBackOrdered = true;
                        continue;
                    }

                    //verifying hazardous material
                    $hazardousMaterial = ($_product->getCustomAttribute('hazardous_material')
                        ? $this->_castStringToBool($_product->getAttributeText('hazardous_material')) : false);
                    if ($hazardousMaterial) {
                        $this->_isHazardousMaterial = true;
                        continue;
                    }

                    //Item found that makes ETA possible
                    $this->_isEtaPossible = true;
                }

                return true;
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            return false;
        }
    }

    protected function _getMethods(string $key): array
    {
        return explode(',',$this->_getStoreConfig($key));
    }

    protected function _getStoreConfig(string $key): string
    {
        $result = $this->_scopeConfig->getValue($key, 'store', $this->_storeId);
        if (empty($result)) {
            $result = '';
        }

        return $result;
    }

    protected function _castStringToBool($value, $true = 'Yes'): bool
    {
        return ((string)$value) === $true;
    }

    protected function isPartialAvailability(Item $item, $qtyFromMach): bool
    {
        $qtyRequested = $this->getProductQty($item);
        $result = false;
        if ($qtyRequested - $qtyFromMach > 0) {
            $result = true;
        }

        return $result;
    }

    protected function getProductQty(Item $item)
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

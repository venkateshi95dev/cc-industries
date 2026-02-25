<?php

namespace Crimson\MachCatalog\Model\Service;

use Crimson\Catalog\Block\Product\View\Type\Simple;
use Crimson\CorvetteCentral\Block\Product\View\Type\Simple as SimpleCC;
use Crimson\MachBase\Model\Api\HealthCheck;
use Crimson\MachCatalog\Model\Api\MultiInventory as MultiInventoryApi;
use Crimson\MachCatalog\Model\Api\Result\MultiInventoryResult;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class GetMultiItemInventory
 * @package Crimson\MachCatalog\Model\Service
 */
class GetMultiItemInventory
{

    public function __construct(
        protected HealthCheck $healthCheck,
        protected MultiInventoryApi $inventoryApi,
        protected StoreManagerInterface $storeManager,
        protected TimezoneInterface $_timezone,
        protected GetProductStockInfo $productStockInfo,
        protected readonly SimpleCC $simpleCC,
    ) {
    }

    public function getMultiInventoryCartPage(array $items): ?MultiInventoryResult
    {
        $result = null;
        if ($this->healthCheck->isUp() && count($items) > 0) {
            $skuList = [];
            foreach ($items as $item) {
                /** @var Item $item */
                if (!empty($item->getSku())) {
                    $skuList[] = $item->getSku();
                }
            }

            $result = $this->inventoryApi->get($skuList);
        }

        return $result;
    }

    /**
     * We are not calling Mach inventory on configurable PDP anymore
     */
    public function getMultiInventoryPdpConfigurable(
        array $products,
        string $timeShippingText,
        string $msgStock,
        string $msgNoStockDropship,
        array $msgETAConfig
    ): array {
        $resultArray = [];
        if (count($products) > 0) {
            foreach ($products as $product) {
                $storeId = $this->storeManager->getStore()->getId();
                //forcing to get the info from Magento
                $stockData = $this->productStockInfo->get($product, $storeId, true);
                if (isset($stockData[GetProductStockInfo::QTY_CODE])) {
                    $qty = (float)$stockData[GetProductStockInfo::QTY_CODE];

                    $dropship = ($product->getCustomAttribute('ships_from_manufacturer')
                        ? (bool)$product->getCustomAttribute('ships_from_manufacturer')->getValue() : false);

                    $resultArray[$product->getId()] = $this->buildStockElement(
                        $product, $qty, $dropship, $timeShippingText, $msgStock, $msgNoStockDropship, $msgETAConfig
                    );
                }
            }
        }

        return $resultArray;
    }

    private function buildStockElement(
        $product,
        float $qty,
        bool $dropship,
        string $timeShippingText,
        string $msgStock,
        string $msgNoStockDropship,
        array $msgETAConfig
    ): array {

        if ($qty > 0) {
            $stockInfo = [
                "stockLabel"  => __($msgStock, $timeShippingText),
                "stockQty"    => $qty,
                "class"       => 'stock available',
                "is_in_stock" => true
            ];
        } elseif ($dropship && $qty <= 0) {
            $stockInfo = [
                "stockLabel"  => __($msgNoStockDropship),
                "stockQty"    => $qty,
                "class"       => 'stock dropship',
                "is_in_stock" => true
            ];
        } else {
            $stockInfo = [
                "stockLabel"  => $this->_getETACorrectMessage($product, $msgETAConfig),
                "stockQty"    => $qty,
                "class"       => 'stock unavailable',
                "is_in_stock" => false
            ];
        }

        return $stockInfo;
    }

    private function _getETACorrectMessage($product, array $msgETAConfig): Phrase
    {
        $websiteCode = $this->storeManager->getWebsite()->getCode();
        if ($websiteCode === "corvette_central_webiste") {
            $this->simpleCC->setData('product', $product);
            return $this->simpleCC->getBackorderETAMessage();
        }

        $etaFromMach = $product->getData(Simple::ETA_ATTR_CODE);
        if (empty($etaFromMach)) {
            return __($msgETAConfig['default']);
        }

        try {
            $etaFromMach = $this->_timezone->date($etaFromMach);
            $currentDate = $this->_timezone->date();
            $daysDifference = $currentDate->diff($etaFromMach);
            if ($daysDifference->invert == 1) {
                return __($msgETAConfig['default']);
            }

            if ($daysDifference->days > 0 && $daysDifference->days <= 14) {
                return __($msgETAConfig['first_range']);
            }

            if ($daysDifference->days >= 15 && $daysDifference->days <= 45) {
                return __($msgETAConfig['second_range'], $etaFromMach->format('F j, Y'));
            }

            if ($daysDifference->days > 45) {
                return __($msgETAConfig['third_range']);
            }

            return __($msgETAConfig['default']);
        } catch (\Exception $e) {
            return __($msgETAConfig['default']);
        }
    }

    public function getMultiInvFilteringShipping(array $items): ?MultiInventoryResult
    {
        $result = null;
        if (count($items) > 0) {
            $skuList = [];
            foreach ($items as $item) {
                /** @var Item $item */
                if (!empty($item->getSku()) && $item->getProductType() == "simple") {
                    $skuList[] = $item->getSku();
                }
            }

            $result = $this->inventoryApi->get($skuList);
        }

        return $result;
    }
}

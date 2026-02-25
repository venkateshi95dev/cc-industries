<?php

namespace Crimson\AmastySeoRichData\Plugin\Model\JsonLd;

use Amasty\SeoRichData\Block\Product as ProductBlock;
use Amasty\SeoRichData\Helper\Config;
use Amasty\SeoRichData\Model\JsonLd\ProductInfo;
use Amasty\SeoRichData\Model\Source\Product\Offer;
use Crimson\AmastySeoRichData\Model\AmastySeoRichDataConfig;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedType;

class StockSchemaUpdate
{

    const BACKORDER = 'backorder';

    public function __construct(
        protected AmastySeoRichDataConfig $amastySeoRichDataConfig,
        protected TimezoneInterface $timezone,
        protected Config $seoRichDataHelper,
        protected StockRegistryInterface $stockRegistry
    ) {}

    public function aroundGetAvailabilityCondition(ProductInfo $subject, callable $proceed, ProductModel $product): string
    {
        $qty = 0;
        $isProductDropship = (bool)$product->getData('ships_from_manufacturer');
        if ($stockItem = $this->stockRegistry->getStockItem($product->getId())) {
            $qty = (int) $stockItem->getQty();
        }

        return !$isProductDropship && $qty <= 0 ? self::BACKORDER : ProductBlock::IN_STOCK;
    }

    public function afterExtract(ProductInfo $subject, $result, ProductModel $product = null)
    {
        //no product
        if (!$product || empty($result['offers'])) {
            return $result;
        }

        //showing availability we add our customization
        if ($this->_isAvailabilityCustomizationPossible($product)) {
            $result = $this->_adjustAvailability($result, $product);
        }

        if ($this->amastySeoRichDataConfig->isShippingWeightAdded()) {
            $result = $this->_adjustShippingWeight($result, $product);
        }

        return $result;
    }

    private function _adjustShippingWeight(array $result, ProductModel $product): array
    {
        foreach ($this->_getSimpleProducts($product) as $productSimple) {
            $productSimpleSku = $productSimple->getSku();
            $foundOffers = array_filter($result['offers'],function($v,$k) use ($productSimpleSku){
                return $v['sku'] === $productSimpleSku;
            },ARRAY_FILTER_USE_BOTH);

            foreach ($foundOffers as $key => $offer) {
                $result['offers'][$key]['shippingDetails'] = [
                    "@type"           => "OfferShippingDetails",
                    "weight" => number_format((float)$productSimple->getWeight(), 2, '.', '') . " lb"
                ];
            }
        }

        return $result;
    }

    private function _adjustAvailability($result, ProductModel $product): array
    {
        //starting the customization
        $futureDate = $this->amastySeoRichDataConfig->getFutureDate();
        $backOrderPreOrderValue = $this->amastySeoRichDataConfig->getBackOrderOrPreOrder();
        foreach ($this->_getSimpleProducts($product) as $productSimple) {
            $productSimpleSku = $productSimple->getSku();
            $foundOffers = array_filter($result['offers'],function($v,$k) use ($productSimpleSku){
                return $v['sku'] === $productSimpleSku && $v['availability'] === self::BACKORDER;
            },ARRAY_FILTER_USE_BOTH);

            foreach ($foundOffers as $key => $offer) {
                $result['offers'][$key]['availability']       = $backOrderPreOrderValue;
                $result['offers'][$key]['availabilityStarts'] = $this->_getAvailabilityData($productSimple->getData('avg_lt'), $futureDate);
            }
        }

        return $result;
    }

    private function _isAvailabilityCustomizationPossible(ProductModel $product): bool
    {
        return $this->seoRichDataHelper->showAvailability() &&
            $this->amastySeoRichDataConfig->isBackorderEnabled() &&
            $this->_isProductTypeConfigCorrect($product);
    }

    private function _getSimpleProducts(ProductModel $product): array
    {
        $list = [];
        $typeInstance = $product->getTypeInstance();
        switch ($product->getTypeId()) {
            case ConfigurableType::TYPE_CODE:
                $list = $typeInstance->getUsedProducts($product);
                break;
            case GroupedType::TYPE_CODE:
                $list = $typeInstance->getAssociatedProducts($product);
                break;
            default:
                $list[] = $product;
        }

        return $list;
    }

    private function _isProductTypeConfigCorrect(ProductModel $product): bool
    {
        if (in_array($product->getTypeId(), [ConfigurableType::TYPE_CODE, GroupedType::TYPE_CODE]) &&
            !($this->seoRichDataHelper->showAsList($product->getTypeId()) == Offer::LIST_OF_SIMPLES)
        ) {
            return false;
        }

        return true;
    }

    private function _getAvailabilityData($avgLt, $futureDate): string
    {
        try {
            return $avgLt ?: $this->timezone->date()->modify($futureDate)->format('c');
        } catch (\Exception $e) {
            return $this->timezone->date()->modify($futureDate)->format('c');
        }
    }
}

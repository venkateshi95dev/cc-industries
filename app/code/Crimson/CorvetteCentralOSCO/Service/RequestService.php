<?php

namespace Crimson\CorvetteCentralOSCO\Service;

use Crimson\CorvetteCentralCustomFees\Service\CustomFeeCalculator;
use Crimson\CorvetteCentralOSCO\Model\CorvetteCentralOSCOConfig;
use Crimson\CorvetteCentralShipping\Model\CorvetteCentralShippingConfig;
use Magento\Catalog\Model\Product;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Item;
use Crimson\CorvetteCentralShipping\Service\QuoteType;
use Magento\Shipping\Model\Carrier\AbstractCarrier;

class RequestService
{

    public function __construct(
        protected CorvetteCentralOSCOConfig $oscoConfig,
        protected SerializerInterface $serializer,
        protected QuoteType $quoteTypeService,
    ) {}

    public function createOSCOSKUDataPayload(RateRequest $request): array
    {
        $items = [];
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                /* @var $item Item */
                if ($this->quoteTypeService->isNotShippableItem($item)) {
                    continue;
                }

                //if sku is freight prepaid we skip it for OSCO
                if (!$this->_isOSCOItem($item, $request)) {
                    continue;
                }

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if (!$child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                            $items[] = $child;
                        }
                    }
                } else {
                    // Ship together - count compound item as one solid
                    $items[] = $item;
                }
            }
        }

        $result = [];
        foreach ($items as $itemFinal) {
            if ($this->doesItemHavePackages($itemFinal->getProduct())) {
                $packagesResult = $this->_createPackagePayloadByItem($itemFinal);
                if ($packagesResult) {
                    $result = array_merge($result, $packagesResult);
                }
            } else {
                $result[] = [
                    'SKU_ID' => $itemFinal->getSku(),
                    'QTY'    => $itemFinal->getQty(),
                    'LENGTH' => $this->_getClearDimensionParameter($itemFinal->getProduct()->getData('cc_length')),
                    'WIDTH'  => $this->_getClearDimensionParameter($itemFinal->getProduct()->getData('cc_width')),
                    'HEIGHT' => $this->_getClearDimensionParameter($itemFinal->getProduct()->getData('cc_height')),
                    'WEIGHT' => $this->_getClearDimensionParameter($itemFinal->getProduct()->getData('cc_net_weight')),
                ];
            }
        }

        return $result;
    }

    private function _createPackagePayloadByItem(Item $item): array
    {
        $result = [];
        $packagesData = $this->getPackageByProduct($item->getProduct());
        if ($packagesData) {
            $boxNumber = 1;
            foreach ($packagesData as $packageData) {
                $result[] = [
                    'SKU_ID'  => $this->_getBoxName($item->getSku(), $boxNumber),
                    'QTY'     => $item->getQty(),
                    'LENGTH'  => $this->_getClearDimensionParameter($packageData['length'] ?? null),
                    'WIDTH'   => $this->_getClearDimensionParameter($packageData['width'] ?? null),
                    'HEIGHT'  => $this->_getClearDimensionParameter($packageData['height'] ?? null),
                    'WEIGHT'  => $this->_getClearDimensionParameter($packageData['weight'] ?? null),
                    'NO_LOAD' => 1
                ];
                $boxNumber++;
            }
        }

        return $result;
    }

    public function getPackageByProduct(Product $product): array
    {
        try {
            $packagesData = $this->serializer->unserialize($product->getData(CorvetteCentralShippingConfig::PRODUCT_PACKAGES_ATTR_CODE));
        } catch (\Exception $e) {
            $packagesData = [];
        } finally {
            return $packagesData;
        }
    }

    private function _getClearDimensionParameter($dimensionValue): float
    {
        return $dimensionValue ? (float)number_format($dimensionValue, 4, '.', '') : 0.00;
    }

    private function _getBoxName($itemSku, $boxNumber): string
    {
        return $itemSku . "-Box$boxNumber";
    }

    public function doesItemHavePackages(Product $product): bool
    {
        return (bool) $product->getData(CorvetteCentralShippingConfig::PRODUCT_HAS_PACKAGES_ATTR_CODE);
    }

    public function isOSCOQuote(RateRequest $request): bool
    {
        foreach ($request->getAllItems() as $item) {
            if ($this->quoteTypeService->isNotShippableItem($item)) {
                continue;
            }

            if ($this->_isOSCOItem($item, $request)) {
                return true;
            }
        }

        return false;
    }

    public function isAirShipmentOkQuote(RateRequest $request): bool
    {
        foreach ($request->getAllItems() as $item) {
            if ($this->quoteTypeService->isNotShippableItem($item)) {
                continue;
            }

            if (!(bool)$item->getProduct()->getData(CorvetteCentralShippingConfig::AIR_SHIPMENT_OK_ATTR_CODE)) {
                return false;
            }
        }

        return true;
    }

    private function _isOSCOItem($item, RateRequest $request): bool
    {
        if (!((float)$item->getProduct()->getData(CustomFeeCalculator::CC_FREIGHT_CHARGE_ATTR)) &&
            $item->getProduct()->getAttributeText(CorvetteCentralShippingConfig::PRODUCT_TRUCK_FREIGHT_TYPE_ATTR_CODE) == ''
        ) {
            return true;
        }

        if ((float)$item->getProduct()->getData(CustomFeeCalculator::CC_FREIGHT_CHARGE_ATTR) &&
            $request->getDestCountryId() === AbstractCarrier::CANADA_COUNTRY_ID &&
            $item->getProduct()->getAttributeText(CorvetteCentralShippingConfig::PRODUCT_TRUCK_FREIGHT_TYPE_ATTR_CODE) == ''
        ) {
            return true;
        }

        return false;
    }
}

<?php
/**
 * @namespace   Crimson
 * @module      MachOrderFees
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/12/2019 11:30 AM
 * @brief
 */

namespace Crimson\MachOrderFees\Model\Total\Quote;

use Crimson\MachBase\Model\Api\HealthCheck;
use Crimson\MachBase\Model\MachConfig;
use Crimson\MachCatalog\Model\Api\Inventory as InventoryApi;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CoreCharge
 * @package Crimson\MachOrderFees\Model\Total\Quote
 */
class CoreCharge extends AbstractFee
{
    protected $_code = 'core_charge';

    public function __construct(
        MachConfig $machConfig,
        InventoryApi $inventoryApi,
        PriceCurrencyInterface $priceCurrency,
        Json $json,
        protected Product $productResource,
        protected HealthCheck $healthCheck,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($machConfig, $inventoryApi, $priceCurrency,$json, $storeManager);
    }

    /**
     * We are not calling Mach for this info anymore
     *
     * @param ShippingAssignmentInterface $shippingAssignment
     * @return float|int
     */
    protected function _calculateBaseAmount(ShippingAssignmentInterface $shippingAssignment)
    {
        $address = $shippingAssignment->getShipping()->getAddress();
        $address->setCoreChargeSkuList(null);

        $coreChargeSkuList = [];
        $coreCharges       = 0;
        foreach ($shippingAssignment->getItems() as $item) {
            try {
                /** @var ProductInterface $product */
                $product = $item->getProduct();

                if ($product->getTypeId() !== 'simple') {
                    continue;
                }

                $isKit = $product->getCustomAttribute('iskit') ? $product->getCustomAttribute('iskit')->getValue()
                    : ($product->getData('iskit') ?: false);

                $coreCharge = 0;
                $qty = 0;
                $coreItemCharges = 0;
                if ($product->getCustomAttribute('corecharge')
                    && $product->getCustomAttribute('corecharge')->getValue()
                ) {
                    $optionValueId = $product->getCustomAttribute('corecharge')->getValue();
                    $attribute     = $this->productResource->getAttribute('corecharge');
                    $coreCharge    = (float)$attribute->getSource()->getOptionText($optionValueId);
                }

                if ($coreCharge >= 0.01) {
                    $qty = $item->getParentItemId() && $item->getParentItem() ? $item->getParentItem()->getQty() : $item->getQty();
                    $coreItemCharges += $coreCharge * $qty;
                    if ($isKit) {
                        $coreChargeSku = __('Core Kit %1', $product->getSku());
                    } elseif ($product->getCustomAttribute('core_charge_sku')
                        && $product->getCustomAttribute('core_charge_sku')->getValue()) {
                        $coreChargeSku = $product->getCustomAttribute('core_charge_sku')->getValue();
                    } else {
                        $coreChargeSku = $product->getSku() . '-CORE';
                    }
                } else {
                    continue;
                }

                $coreCharges += $coreItemCharges;
                $coreChargeSkuList[] = [
                    'core_charge_sku'    => (string) $coreChargeSku,
                    'core_charge_base_price_amount' => (float) $coreCharge,
                    'core_charge_amount' => (float) $coreItemCharges,
                    'qty'                => (float) $qty,
                    'iskit'              => (int) $isKit,
                ];

            } catch (\Exception $e) {

            }
        }

        if (!empty($coreChargeSkuList)) {
            $address->setCoreChargeSkuList($this->json->serialize($coreChargeSkuList));
        }

        return $coreCharges;
    }

    /**
     * @return Phrase|string
     */
    public function getLabel()
    {
        return __('Core Charges');
    }
}

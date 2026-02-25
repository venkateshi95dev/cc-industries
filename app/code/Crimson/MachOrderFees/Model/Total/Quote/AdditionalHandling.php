<?php
/**
 * @namespace   Crimson
 * @module      MachOrderFees
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/12/2019 12:22 PM
 * @brief
 */

namespace Crimson\MachOrderFees\Model\Total\Quote;

use Crimson\InStorePickup\Service\InStorePickupMethod;
use Crimson\MachBase\Model\Api\HealthCheck;
use Crimson\MachBase\Model\MachConfig;
use Crimson\MachCatalog\Model\Api\Inventory as InventoryApi;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class AdditionalHandling
 * @package Crimson\MachOrderFees\Model\Total\Quote
 */
class AdditionalHandling extends AbstractFee
{
    protected $_code = 'additional_handling';

    public function __construct(
        MachConfig $machConfig,
        InventoryApi $inventoryApi,
        PriceCurrencyInterface $priceCurrency,
        Json $json,
        protected HealthCheck $healthCheck,
        protected Product $productResource,
        protected InStorePickupMethod $inStorePickupMethod,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($machConfig, $inventoryApi, $priceCurrency, $json, $storeManager);
    }

    /**
     * @param ShippingAssignmentInterface $shippingAssignment
     * @return float|int
     */
    protected function _calculateBaseAmount(ShippingAssignmentInterface $shippingAssignment)
    {
        $shippingMethod = $this->_getShippingMethod($shippingAssignment);
        if ($this->inStorePickupMethod->is($shippingMethod)) {
            return 0;
        }

        $additionalHandlingAmount = 0;
        foreach ($shippingAssignment->getItems() as $item) {
            try {

                /**
                 * We don't analyze configurable products, this could generate double Additional Handling fee.
                 */
                if ($item->getProductType() == Configurable::TYPE_CODE) {
                    continue;
                }

                /**
                 * We now only use Magento
                 */

                /** @var ProductInterface $product */
                $product = $item->getProduct();

                if ($product->getCustomAttribute('shippingcharge')
                    && $product->getCustomAttribute('shippingcharge')->getValue()
                ) {
                    $optionValueId  = $product->getCustomAttribute('shippingcharge')->getValue();
                    $attribute      = $this->productResource->getAttribute('shippingcharge');
                    $handlingCharge = (float)$attribute->getSource()->getOptionText($optionValueId);

                    if ($handlingCharge > 0.01) {
                        $additionalHandlingAmount += $handlingCharge * $item->getQty();
                    }
                }

            } catch (\Exception $e) {

            }
        }

        return $additionalHandlingAmount;
    }

    /**
     * @return Phrase|string
     */
    public function getLabel()
    {
        return __('Additional Handling');
    }

    private function _getShippingMethod(ShippingAssignmentInterface $shippingAssignment): string
    {
        try {
            return (string)$shippingAssignment->getShipping()->getMethod();
        } catch (\Exception $e) {
            return '';
        }
    }
}

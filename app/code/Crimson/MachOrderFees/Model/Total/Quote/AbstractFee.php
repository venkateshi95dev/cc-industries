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

use Crimson\MachBase\Model\MachConfig;
use Crimson\MachCatalog\Model\Api\Inventory as InventoryApi;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class AbstractFee
 * @package Crimson\MachOrderFees\Model\Total\Quote
 */
abstract class AbstractFee extends AbstractTotal
{

    abstract protected function _calculateBaseAmount(
        ShippingAssignmentInterface $shippingAssignment
    );

    public function __construct(
        protected MachConfig $machConfig,
        protected InventoryApi $inventoryApi,
        protected PriceCurrencyInterface $priceCurrency,
        protected Json $json,
        protected StoreManagerInterface $storeManager,
    ) {}

    public function collect(Quote $quote, ShippingAssignmentInterface $shippingAssignment, Total $total): AbstractFee
    {
        $webSite = $this->storeManager->getWebsite();
        if ($webSite->getCode() != MachConfig::ZIP_WEBSITE_CODE) {
            return $this;
        }

        AbstractTotal::collect($quote, $shippingAssignment, $total);

        if (!$this->machConfig->isEnabled($webSite->getId())) {
            return $this;
        }

        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return $this;
        }

        $baseAmount = $this->_calculateBaseAmount($shippingAssignment);
        if (abs($baseAmount) < 0.01 || $baseAmount === false) {
            return $this;
        }

        $amount = $this->priceCurrency->convert($baseAmount);

        $this->_addAmount($amount);
        $this->_addBaseAmount($baseAmount);

        return $this;
    }

    /**
     * @param Quote $quote
     * @param Total $total
     * @return array|null
     */
    public function fetch(Quote $quote, Total $total): ?array
    {
        $key = $this->getCode() . '_amount';
        $amt = $total->getData($key) ?: 0;
        if (abs($amt) >= 0.01) {
            return [
                'code'  => $this->getCode(),
                'title' => $this->getLabel(),
                'value' => $amt,
            ];
        }

        return null;
    }
}

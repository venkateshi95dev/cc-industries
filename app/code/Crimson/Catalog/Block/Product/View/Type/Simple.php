<?php

namespace Crimson\Catalog\Block\Product\View\Type;

use Crimson\Catalog\Helper\Data as CatalogHelper;
use Crimson\Catalog\Model\Config;
use Crimson\Catalog\Service\ServiceProduct;
use Crimson\MachBase\Model\Api\HealthCheck;
use Crimson\MachCatalog\Model\Api\Inventory as InventoryApi;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\View\Type\Simple as SimpleNoMach;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Simple
 * @package Crimson\Catalog\Block\Product\View\Type
 */
class Simple extends SimpleNoMach
{

    CONST ETA_ATTR_CODE = 'avg_lt';

    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        protected HealthCheck $healthCheck,
        protected CatalogHelper $catalogHelper,
        protected InventoryApi $inventoryApi,
        protected ServiceProduct $serviceProduct,
        protected TimezoneInterface $_timezone,
        protected Config $config,
        array $data = []
    ) {
        parent::__construct($context, $arrayUtils, $data);
    }

    /**
     * We know that the StockItem already has the stock info from Mach or Magento
     *
     * @return int
     */
    public function getSimpleStockInfo() :int
    {
        $result = 0;
        $product = $this->getProduct();
        if ($stockItem = $product->getExtensionAttributes()->getStockItem()) {
            $result = (int) $stockItem->getQty();
        }

        return $result;
    }

    public function isDisableDiscontinuedProduct(): bool
    {
        return (bool)$this->config->isDisableDiscontinuedProduct();
    }

    public function getShippingTimeInfo(): string
    {
        return $this->catalogHelper->getShippingTimeInfo();
    }

    public function getIsServiceProduct(): bool
    {
        return $this->serviceProduct->is($this->getProduct());
    }

    public function getOOSMPDPCustomMessage(): string
    {
        return $this->config->getOOSMPDPCustomMessage();
    }

    public function getBackorderETAMessage(): Phrase
    {
        $etaFromMach = $this->getProduct()->getData(self::ETA_ATTR_CODE);
        if (empty($etaFromMach)) {
            return __($this->config->getETADefaultMsg());
        }

        try {
            $etaFromMach = $this->_timezone->date($etaFromMach);
            $currentDate = $this->_timezone->date();
            $daysDifference = $currentDate->diff($etaFromMach);
            if ($daysDifference->invert == 1) {
                return __($this->config->getETADefaultMsg());
            }

            if ($daysDifference->days > 0 && $daysDifference->days <= 14) {
                return __($this->config->getETAFirstRangeMsg());
            }

            if ($daysDifference->days >= 15 && $daysDifference->days <= 45) {
                return __($this->config->getETASecondRangeMsg(), $etaFromMach->format('F j, Y'));
            }

            if ($daysDifference->days > 45) {
                return __($this->config->getETAThirdRangeMsg());
            }

            return __($this->config->getETADefaultMsg());
        } catch (\Exception $e) {
            return __($this->config->getETADefaultMsg());
        }
    }
}

<?php

namespace Crimson\MachCatalog\Observer;

use Crimson\MachBase\Model\Api\HealthCheck;
use Crimson\MachBase\Model\MachConfig;
use Crimson\MachCatalog\Model\Service\GetMultiItemInventory;
use Magento\Catalog\Model\Product\Type;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Quote\Model\Quote\Item;

class AddItemsToQuoteDropshipUpdate implements ObserverInterface
{

    public function __construct(
        private readonly StoreManagerInterface $storeManager
    ) {}

    public function execute(Observer $observer)
    {
        if ($this->storeManager->getWebsite()->getCode() == MachConfig::ZIP_WEBSITE_CODE) {
            $itemsAdded = $observer->getEvent()->getItems();
            foreach ($itemsAdded as $item) {
                /** @var Item $item */
                if ($item->getProductType() === Type::TYPE_SIMPLE) {
                    $simpleValue = $item->getProduct()->getCustomAttribute('ships_from_manufacturer') ?
                        $item->getProduct()->getCustomAttribute('ships_from_manufacturer')->getValue() :
                        0;
                    $item->setData('ships_from_manufacturer', $simpleValue);
                    continue;
                }

                if ($item->getProductType() === Configurable::TYPE_CODE) {
                    $configurableValue = $this->_getParentChildDropshipValue($itemsAdded);
                    $item->setData('ships_from_manufacturer', $configurableValue);
                    continue;
                }
            }
        }
    }

    private function _getParentChildDropshipValue(array $items)
    {
        foreach ($items as $item) {
            /** @var Item $item */
            if ($item->getProductType() === Type::TYPE_SIMPLE) {
                return $item->getProduct()->getCustomAttribute('ships_from_manufacturer') ?
                    $item->getProduct()->getCustomAttribute('ships_from_manufacturer')->getValue() :
                    0;
            }
        }

        return 0;
    }
}

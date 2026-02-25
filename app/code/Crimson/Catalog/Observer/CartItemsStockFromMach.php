<?php

namespace Crimson\Catalog\Observer;

use Crimson\MachBase\Model\Api\HealthCheck;
use Crimson\MachBase\Model\MachConfig;
use Crimson\MachCatalog\Model\Service\GetMultiItemInventory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Crimson\MachCatalog\Model\Api\Result\MultiInventoryResult;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CartItemsStockFromMach
 * @package Crimson\Catalog\Observer
 */
class CartItemsStockFromMach implements ObserverInterface
{

    public function __construct(
        protected HealthCheck $healthCheck,
        protected CheckoutSession $checkoutSession,
        protected GetMultiItemInventory $multiInventoryService,
        private readonly StoreManagerInterface $storeManager
    ) {}

    /**
     * @param Observer $observer
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if ($this->storeManager->getWebsite()->getCode() == MachConfig::ZIP_WEBSITE_CODE) {
            /** @var Quote $quote */
            $quote  = $this->checkoutSession->getQuote();

            if ($quote && $quote->getId() && $quote->getItemsQty() > 0 && $this->healthCheck->isUp()) {
                //Calling for MultiItem Stock Info
                $stockInfo = $this->multiInventoryService->getMultiInventoryCartPage($quote->getAllVisibleItems());
                if ($stockInfo instanceof MultiInventoryResult && $stockInfo->getResponseStatus()) {
                    foreach ($quote->getAllVisibleItems() as $item) {
                        $qtyAvilFromMach = $stockInfo->getQtyAvailable($item->getSku());
                        if ($qtyAvilFromMach !== false) {
                            $item->setData('stock_from_mach', $qtyAvilFromMach);
                        }

                        $dropshipFromMach = $stockInfo->getDropshipForCartPage($item->getSku());
                        if ($dropshipFromMach !== null) {
                            $item->setData('dropship_from_mach', $dropshipFromMach);
                        }
                    }
                }
            }
        }
    }
}

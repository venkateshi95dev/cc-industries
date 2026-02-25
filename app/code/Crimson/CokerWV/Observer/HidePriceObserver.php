<?php

declare(strict_types=1);

namespace Crimson\CokerWV\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\StoreManagerInterface;
use Crimson\CokerWV\Api\CokerStoreInterface;

class HidePriceObserver implements ObserverInterface
{
    public function __construct(
        protected Registry $registry,
        protected StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer) : void
    {
        //Only apply for Coker
        try {
            $websiteCode = $this->storeManager->getStore()->getWebsite()->getCode();
            if ($websiteCode !== CokerStoreInterface::COKER_WEBSITE_CODE) {
                return;
            }
        } catch (\Exception $e) {}

        /** @var LayoutInterface $layout */
        $layout = $observer->getLayout();

        // Only proceed on product view pages
        $handles = $layout->getUpdate()->getHandles();
        if (!in_array('catalog_product_view', $handles)) {
            return;
        }

        $product = $this->registry->registry('current_product');
        if ($product && $product->getData('hide_add_to_cart_button')) {
            $layout->unsetElement('product.info.price');
        }
    }
}

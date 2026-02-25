<?php
namespace Crimson\CorvetteCentral\Observer;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Store\Model\StoreManagerInterface;

class ClearStateObserver implements ObserverInterface
{
    public function __construct(
        private StoreManagerInterface $storeManager,
        private DirectoryHelper $directoryHelper
    )
    {
    }

    /**
     * Execute observer
     */
    public function execute(Observer $observer)
    {
        $CCStoreId = $this->storeManager->getStore(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE)->getId();
        if ($this->storeManager->getStore()->getId()!==$CCStoreId) {
            return;
        }
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        // Billing address
        $billing = $order->getBillingAddress();
        if ($billing && !in_array($billing->getCountryId(),['US','CA']) && $billing->getRegionId()) {
            $billing->setRegion(null);
            $billing->setRegionId(null);
        }

        // Shipping address
        $shipping = $order->getShippingAddress();
        if ($shipping && !in_array($shipping->getCountryId(),['US','CA']) && $shipping->getRegionId()) {
            $shipping->setRegion(null);
            $shipping->setRegionId(null);
        }
    }
}

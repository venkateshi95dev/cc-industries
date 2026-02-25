<?php

namespace Crimson\InStorePickup\Observer;

use Crimson\InStorePickup\Model\InStorePickupConfig;
use Crimson\InStorePickup\Service\InStorePickupMethod;
use Crimson\ZipCokerWvConsolidation\Model\Config;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class AdjustOrderEmailTemplateVariable implements ObserverInterface
{

    public function __construct(
        protected InStorePickupConfig $inStorePickupConfig
    ) {}

    public function execute(Observer $observer)
    {
        $transportObject = $observer->getData('transportObject');
        if (!$transportObject) {
            return $this;
        }

        /** @var Order $order */
        $order = $transportObject->getOrder();
        if (!$order || !$order->getId()) {
            return $this;
        }

        if ($this->_isCokerOrWv($order)) {
            $message = $this->_isOrderBackorder($order) ? "Order Status :: Back Order" : "Order Status :: " . $order->getStatus();
            $transportObject->setData('orderstatus', $message);
            $order->setData("orderstatus", $message);
        }

        if ($order->getShippingMethod() === InStorePickupMethod::IN_STORE_PICKUP_SHIPPING_METHOD &&
            $order->getSourceCode() &&
            $order->getSourceEmail() &&
            $this->inStorePickupConfig->isLocationEmailNotificationEnabled($order->getStore()->getWebsiteId())
        ) {
            $transportObject->setData('source_email', $order->getSourceEmail());
        }

        return $this;
    }

    private function _isCokerOrWv(Order $order): bool
    {
        return !($order->getStore()->getWebsite()->getCode() === Config::ZIP_WEBSITE_CODE);
    }

    private function _isOrderBackorder(Order $order): bool
    {
        $isBackorder = false;
        foreach ($order->getAllItems() as $item) {
            if($item->getQtyBackordered() > 0){
                $isBackorder = true;
                break;
            }
        }

        return $isBackorder;
    }
}

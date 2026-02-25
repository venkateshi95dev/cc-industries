<?php
namespace Silk\Coker\Observer;

use Crimson\MachBase\Model\MachConfig;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;

class OrderSubmitAfterObserver  implements ObserverInterface
{

	public function execute(Observer $observer)
    {
        /* @var $order Order */
        $order = $observer->getEvent()->getOrder();
        if (!$order || !$order->getId()) {
            return;
        }

        //We don't want this for ZIP
        if (MachConfig::ZIP_WEBSITE_CODE == $order->getStore()->getWebsite()->getCode()) {
            return;
        }

        $isBackorder = false;
        $orderItems = $order->getAllItems();
        foreach ($orderItems as $item) {
            $backorder = $item->getQtyBackordered();
            if($backorder > 0) {
                $isBackorder = true;
                break;
            }
        }

        if($isBackorder){
            $order
                ->setState("processing")
                ->setStatus("backorder")
                ->save();
        }
	}
}

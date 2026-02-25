<?php

namespace Crimson\AmastyStorePickupWithLocator\Observer;

use Amasty\StorePickupWithLocator\Model\Carrier\Shipping;
use Amasty\StorePickupWithLocator\Model\OrderRepository;
use Crimson\AmastyStorePickupWithLocator\Service\NewShipmentLocationNotification;
use Crimson\AmastyStorePickupWithLocator\Service\PickupLocationFromOrder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\ShipmentInterface;

class StorePickupShipmentNotification implements ObserverInterface
{

    public function __construct(
        protected OrderRepository $orderRepository,
        protected PickupLocationFromOrder $pickupLocationFromOrder,
        protected NewShipmentLocationNotification $newShipmentLocationNotification,
    ) {}

    public function execute(Observer $observer)
    {
        try {
            /** @var ShipmentInterface $shipment */
            $shipment = $observer->getEvent()->getShipment();
            $order = $shipment->getOrder();

            if ($order->getShippingMethod() !== Shipping::SHIPPING_NAME) {
                return $this;
            }

            $location = $this->pickupLocationFromOrder->get($order);
            if (!$location) {
                return $this;
            }

            $this->newShipmentLocationNotification->process($shipment->getId(), $order, $location);
        } catch (\Exception $e) {
            return $this;
        }

        return $this;
    }
}

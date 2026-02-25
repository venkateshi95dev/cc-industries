<?php

namespace Crimson\AmastyStorePickupWithLocator\Service;

use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Model\DataCollector\Location\AttributeCollector;
use Amasty\Storelocator\Model\Repository\LocationRepository;
use Amasty\StorePickupWithLocator\Model\OrderRepository;
use Magento\Sales\Model\Order;

class PickupLocationFromOrder
{

    public function __construct(
        protected OrderRepository $orderRepository,
        protected LocationRepository $locationRepository,
        protected AttributeCollector $attributeCollector,
    ) {}

    public function get(Order $order): ?LocationInterface
    {
        try {
            $locationId = $this->orderRepository->getByOrderId((int)$order->getId())->getStoreId();

            return $this->locationRepository->getById((int)$locationId);
        } catch (\Exception $e) {
            return null;
        }
    }
}

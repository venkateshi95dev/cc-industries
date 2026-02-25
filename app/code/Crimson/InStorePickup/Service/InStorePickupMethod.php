<?php

namespace Crimson\InStorePickup\Service;

class InStorePickupMethod
{
    CONST IN_STORE_PICKUP_METHOD          = 'instore_';
    CONST IN_STORE_PICKUP_SHIPPING_METHOD = 'instore_pickup';

    public function is(string $shippingMethod): bool
    {
        return str_starts_with($shippingMethod, self::IN_STORE_PICKUP_METHOD);
    }

}

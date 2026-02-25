<?php

namespace Crimson\WeltPixelGA4\Block;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Address;
use WeltPixel\GA4\Block\Order;

class OrderCustom extends Order
{

    /**
     * @return array
     */
    public function getCustomerData(): array
    {
        $order = $this->getOrder();
        $shippingAddress = $order->getShippingAddress();

        return [
            'email'                => $order->getCustomerEmail(),
            'sha256_email_address' => hash('sha256', $order->getCustomerEmail()),
            'phone_number'         => $shippingAddress?->getTelephone(),
            'address' => [
                'first_name'  => $order->getCustomerFirstname(),
                'last_name'   => $order->getCustomerLastname(),
                'street'      => $shippingAddress?->getStreetLine(1) . " " .  $shippingAddress?->getStreetLine(2),
                'city'        => $shippingAddress?->getCity(),
                'region'      => $shippingAddress?->getRegion(),
                'country'     => $shippingAddress?->getCountryId(),
                'postal_code' => $shippingAddress?->getPostcode(),
            ]
        ];
    }
}

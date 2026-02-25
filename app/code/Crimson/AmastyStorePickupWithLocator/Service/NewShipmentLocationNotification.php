<?php

namespace Crimson\AmastyStorePickupWithLocator\Service;

use Amasty\Storelocator\Api\Data\LocationInterface;
use Crimson\AmastyStorePickupWithLocator\Model\Queue\Email\Publisher;
use Crimson\AmastyStorePickupWithLocator\Model\Queue\Email\Dto\EmailConfigFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order;

class NewShipmentLocationNotification
{

    CONST SHIPMENT_NOTIFICATION_ATTR           = "shipment_notification";
    CONST SHIPMENT_NOTIFICATION_COPY_TO_ATTR   = "shipment_notification_copy_to";
    CONST SHIPMENT_NOTIFICATION_EMAIL_TEMPLATE = "storepickup_locator_email_notification_email_template_new_shipment";
    CONST SHIPMENT_NOTIFICATION_EMAIL_SENDER   = "general";

    public function __construct(
        protected EmailConfigFactory $emailConfigFactory,
        protected Json $jsonSerializer,
        protected Publisher $publisher,
    ) {}

    public function process(int $shipmentId, Order $order, LocationInterface $location): void
    {
        if (!$this->_isShipmentEmailNotificationEnabledByLocation($location)) {
            return;
        }

        if (!$this->_isShipmentEmailNotificationPossibleByLocation($location)) {
            return;
        }

        $this->_sendEmailNotification($shipmentId, $order, $location);
    }

    private function _sendEmailNotification(int $shipmentId, Order $order, LocationInterface $location): void
    {
        $shippingAddress = $order->getShippingAddress();
        $locationName = $shippingAddress ? $shippingAddress->getLastname() : '';
        $data = [
            'order_increment_id' => $order->getIncrementId(),
            'order_id'           => $order->getId(),
            'shipment_id'        => $shipmentId,
            'location_name'      => $locationName,
            'customer_name'      => $order->getCustomerName(),
            'customer_email'     => $order->getCustomerEmail(),
            'customer_phone'     => $order->getBillingAddress() ? $order->getBillingAddress()->getTelephone() : '',
        ];

        $recipients = array_filter($this->_getSendCopyTo($location));
        $emailConfig = $this->emailConfigFactory->create();
        $emailConfig->setTemplate(self::SHIPMENT_NOTIFICATION_EMAIL_TEMPLATE);
        $emailConfig->setSender(self::SHIPMENT_NOTIFICATION_EMAIL_SENDER);
        $emailConfig->setRecipients(array_filter($recipients));
        $emailConfig->setStoreId((int)$order->getStoreId());
        $emailConfig->setJsonData($this->jsonSerializer->serialize($data));

        $this->publisher->publish($emailConfig);
    }

    private function _getSendCopyTo(LocationInterface $location): array
    {
        $copyTo = '';
        if (!empty($location['attributes'][self::SHIPMENT_NOTIFICATION_COPY_TO_ATTR]['value'])) {
            $copyTo = $location['attributes'][self::SHIPMENT_NOTIFICATION_COPY_TO_ATTR]['value'];
        }

        try {
            return array_unique(array_merge([$location['email']], explode(',',  $copyTo)), SORT_REGULAR);
        } catch (\Exception $e) {
            return [$location['email']];
        }
    }

    private function _isShipmentEmailNotificationPossibleByLocation(LocationInterface $location): bool
    {
        return !empty($location['email']);
    }

    private function _isShipmentEmailNotificationEnabledByLocation(LocationInterface $location): bool
    {
        if (empty($location['attributes'][self::SHIPMENT_NOTIFICATION_ATTR])) {
            return false;
        }

        return (bool)$location['attributes'][self::SHIPMENT_NOTIFICATION_ATTR]['value'];
    }
}

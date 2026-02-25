<?php
/**
 * @namespace   Crimson
 * @module      MachCustomer
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/28/2019 8:43 AM
 * @brief
 */

namespace Crimson\MachCustomer\Model\Order\Attributes;

use Crimson\Sales\Model\Order\Attributes\AbstractAttachHandler;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class AttachHandler
 * @package Crimson\MachCustomer\Model\Order\Attributes
 */
class AttachHandler extends AbstractAttachHandler
{
    /**
     * @param OrderInterface $order
     *
     * @return AbstractAttachHandler
     * @throws LocalizedException
     */
    public function attachAttributes(OrderInterface $order): AbstractAttachHandler
    {
        if (!$order->getEntityId()) {
            return $this;
        }

        $orderData = $this->machDataResource->getOrderData($order->getEntityId());

        $order->getExtensionAttributes()
            ->setMachCustomerNumber($orderData['mach_customer_number'] ?? null);

        return $this;
    }
}

<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/28/2019 8:43 AM
 * @brief
 */

namespace Crimson\MachOrder\Model\Order\Attributes;

use Crimson\Sales\Model\Order\Attributes\AbstractAttachHandler;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class AttachHandler
 * @package Crimson\MachOrder\Model\Order\Attributes
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
            ->setMachOrderNumber($orderData['mach_order_number'] ?? null)
            ->setMachSubmitted((bool)($orderData['mach_submitted'] ?? false))
            ->setMachSubmittedAt($orderData['mach_submitted_at'] ?? null)
            ->setMachUpdateScheduled((bool)($orderData['mach_update_scheduled'] ?? false))
            ->setMachSubmitErrorCount((int)($orderData['mach_submit_error_count'] ?? 0))
            ->setMachSubmitLastErrorNumber($orderData['mach_submit_last_error_number'] ?? null)
            ->setMachSubmitLastErrorMessage($orderData['mach_submit_last_error_message'] ?? null)
            ->setMachSubmitHold((bool)($orderData['mach_submit_hold'] ?? false));

        return $this;
    }
}

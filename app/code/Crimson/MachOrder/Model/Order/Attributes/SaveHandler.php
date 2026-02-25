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

use Crimson\Sales\Model\Order\Attributes\AbstractSaveHandler;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class SaveHandler
 * @package Crimson\MachOrder\Model\Order\Attributes
 */
class SaveHandler extends AbstractSaveHandler
{

    /**
     * @param OrderInterface $order
     * @return AbstractSaveHandler
     * @throws LocalizedException
     */
    public function saveAttributes(OrderInterface $order): AbstractSaveHandler
    {
        $data = [
            'order_id'                       => $order->getEntityId(),
            'mach_order_number'              => $order->getExtensionAttributes()->getMachOrderNumber() ?? null,
            'mach_submitted'                 => (bool)$order->getExtensionAttributes()->getMachSubmitted() ?? false,
            'mach_submitted_at'              => $order->getExtensionAttributes()->getMachSubmittedAt() ?? null,
            'mach_update_scheduled'          => (bool)$order->getExtensionAttributes()->getMachUpdateScheduled() ?? false,
            'mach_submit_error_count'        => (int)$order->getExtensionAttributes()->getMachSubmitErrorCount() ?? 0,
            'mach_submit_last_error_number'  => $order->getExtensionAttributes()->getMachSubmitLastErrorNumber() ?? null,
            'mach_submit_last_error_message' => $order->getExtensionAttributes()->getMachSubmitLastErrorMessage() ?? null,
            'mach_submit_hold'               => (bool)$order->getExtensionAttributes()->getMachSubmitHold() ?? false,
        ];

        $this->machDataResource->getConnection()->insertOnDuplicate($this->machDataResource->getMainTable(), $data);

        return $this;
    }
}

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

use Crimson\Sales\Model\Order\Attributes\AbstractSaveHandler;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class SaveHandler
 * @package Crimson\MachCustomer\Model\Order\Attributes
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
            'order_id'             => $order->getEntityId(),
            'mach_customer_number' => $order->getExtensionAttributes()->getMachCustomerNumber(),
        ];

        $this->machDataResource->getConnection()->insertOnDuplicate($this->machDataResource->getMainTable(), $data);

        return $this;
    }
}

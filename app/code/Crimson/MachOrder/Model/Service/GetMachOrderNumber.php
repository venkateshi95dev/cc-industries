<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/1/2019 3:03 PM
 * @brief
 */

namespace Crimson\MachOrder\Model\Service;

use Crimson\MachOrder\Model\ResourceModel\Order\MachData;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class GetMachOrderNumber
 * @package Crimson\MachOrder\Model\Service
 */
class GetMachOrderNumber
{
    /**
     * @var MachData
     */
    protected $machOrderDataResource;

    public function __construct(
        MachData $machOrderDataResource
    ) {
        $this->machOrderDataResource = $machOrderDataResource;
    }

    /**
     * @param OrderInterface $order
     *
     * @return string|null
     * @throws LocalizedException
     */
    public function get(OrderInterface $order): ?string
    {
        if ($order->getExtensionAttributes()->getMachOrderNumber()) {
            return $order->getExtensionAttributes()->getMachOrderNumber();
        }

        if (!$order->getEntityId()) {
            return null;
        }

        return $this->machOrderDataResource->getOrderNumberByOrderId((int) $order->getEntityId());
    }
}

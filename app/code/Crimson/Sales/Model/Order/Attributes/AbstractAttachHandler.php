<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/28/2019 8:43 AM
 * @brief
 */

namespace Crimson\Sales\Model\Order\Attributes;

use Crimson\Sales\Model\ResourceModel\Order\AbstractMachData;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class AbstractAttachHandler
 * @package Crimson\Sales\Model\Order\Attributes
 */
abstract class AbstractAttachHandler
{
    /**
     * @var AbstractMachData
     */
    protected $machDataResource;

    /**
     * AttachHandler constructor.
     *
     * @param AbstractMachData $machDataResource
     */
    public function __construct(
        AbstractMachData $machDataResource
    ) {
        $this->machDataResource = $machDataResource;
    }

    /**
     * @param OrderInterface $order
     *
     * @return AbstractAttachHandler
     */
    abstract public function attachAttributes(OrderInterface $order): AbstractAttachHandler;

    /**
     * @param OrderInterface[] $orders
     *
     * @return AbstractAttachHandler
     */
    public function attachAttributesToMultipleOrders(array $orders): AbstractAttachHandler
    {
        foreach (array_chunk($orders, 20) as $ordersChunk) {
            $entityIds = [];
            foreach ($orders as $order) {
                $entityIds[] = $order->getEntityId();
            }

            if (empty($entityIds)) {
                continue;
            }

            $this->machDataResource->preloadOrderIds($entityIds);

            foreach ($orders as $order) {
                $this->attachAttributes($order);
            }
        }

        $this->machDataResource->clearPreloadedData();

        return $this;
    }
}

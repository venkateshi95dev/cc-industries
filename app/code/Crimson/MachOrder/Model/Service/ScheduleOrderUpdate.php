<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/1/2019 5:38 PM
 * @brief
 */

namespace Crimson\MachOrder\Model\Service;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class ScheduleOrderUpdate
 * @package Crimson\MachOrder\Model\Service
 */
class ScheduleOrderUpdate
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param OrderInterface $order
     */
    public function execute(OrderInterface $order): void
    {
        $order->getExtensionAttributes()->setMachUpdateScheduled(true);
        $this->orderRepository->save($order);
    }
}

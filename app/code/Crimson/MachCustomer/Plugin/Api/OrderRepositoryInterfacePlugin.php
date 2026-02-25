<?php
 /**
 * @namespace   Crimson
 * @module      MachCustomer
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/27/2019 5:09 PM
 * @brief
 */

namespace Crimson\MachCustomer\Plugin\Api;

use Crimson\MachCustomer\Model\Order\Attributes\AttachHandler;
use Crimson\MachCustomer\Model\Order\Attributes\SaveHandler;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class OrderRepositoryInterfacePlugin
 * @package Crimson\MachCustomer\Plugin\Api
 */
class OrderRepositoryInterfacePlugin
{
    /**
     * @var AttachHandler
     */
    protected $attachHandler;
    /**
     * @var SaveHandler
     */
    protected $saveHandler;

    /**
     * OrderRepositoryInterfacePlugin constructor.
     *
     * @param AttachHandler $attachHandler
     * @param SaveHandler   $saveHandler
     */
    public function __construct(
        AttachHandler $attachHandler,
        SaveHandler $saveHandler
    ) {
        $this->attachHandler = $attachHandler;
        $this->saveHandler = $saveHandler;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface      $order
     *
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    ) {
        $this->attachHandler->attachAttributes($order);

        return $order;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(OrderRepositoryInterface $subject, OrderInterface $order): OrderInterface
    {
        $this->saveHandler->saveAttributes($order);

        return $order;
    }
}

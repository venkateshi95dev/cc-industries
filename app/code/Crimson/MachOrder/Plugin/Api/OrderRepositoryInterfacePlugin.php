<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        5/30/2019 1:11 AM
 * @brief
 */

namespace Crimson\MachOrder\Plugin\Api;

use Crimson\MachOrder\Model\Order\Attributes\AttachHandler;
use Crimson\MachOrder\Model\Order\Attributes\SaveHandler;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class OrderRepositoryInterfacePlugin
 * @package Crimson\MachOrder\Plugin\Api
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
     * @throws LocalizedException
     */
    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $order): OrderInterface
    {
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

<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Plugin;

use I95DevConnect\DiscountGroups\Api\Data\I95DevDiscountGroupInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderExtension;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemExtension;
use Magento\Sales\Api\Data\OrderItemExtensionFactory;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderGet
{
    /** @var OrderExtensionFactory */
    protected $orderExtensionFactory;

    /**
     * @var OrderItemExtensionFactory
     */
    protected $orderItemExtensionFactory;

    /**
     * Init plugin
     *
     * @param OrderExtensionFactory $orderExtensionFactory
     * @param OrderItemExtensionFactory $orderItemExtensionFactory
     */
    public function __construct(
        OrderExtensionFactory $orderExtensionFactory,
        OrderItemExtensionFactory $orderItemExtensionFactory
    ) {
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->orderItemExtensionFactory = $orderItemExtensionFactory;
    }

    /**
     * Get sort code
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $resultOrder
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        OrderRepositoryInterface $subject, //NOSONAR
        OrderInterface $resultOrder
    ) {
        $resultOrder = $this->getOrderDiscountGroupAmount($resultOrder);
        return $this->getOrderItemDiscountGroupAmount($resultOrder);
    }

    /**
     * Get gift message for items of order
     *
     * @param OrderInterface $order
     * @return OrderInterface
     */
    protected function getOrderItemDiscountGroupAmount(OrderInterface $order)
    {
        $orderItems = $order->getItems();
        if (null !== $orderItems) {
            /** @var OrderItemInterface $orderItem */
            foreach ($orderItems as $orderItem) {
                $extensionAttributes = $orderItem->getExtensionAttributes();
                if ($extensionAttributes && $extensionAttributes->getDiscountGroupAmount()) {
                    continue;
                }

                /** @var OrderItemExtension $orderItemExtension */
                $orderItemExtension = $extensionAttributes
                    ? $extensionAttributes
                    : $this->orderItemExtensionFactory->create();
                $orderItemExtension->setDiscountGroupAmount($orderItem->getDiscountGroupAmount());
                $orderItemExtension->setBaseDiscountGroupAmount($orderItem->getBaseDiscountGroupAmount());
                $orderItem->setExtensionAttributes($orderItemExtension);
            }
        }
        return $order;
    }

    /**
     * Get Discount Group Amount for order
     *
     * @param OrderInterface $order
     * @return OrderInterface
     */
    protected function getOrderDiscountGroupAmount(OrderInterface $order)
    {
        $extensionAttributes = $order->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getDiscountGroupAmount()) {
            return $order;
        }

        try {
            /** @var I95DevDiscountGroupInterface $discountGroupAmount */
            $discountGroupAmount = $order->getDiscountGroupAmount();
            $basediscountGroupAmount = $order->getBaseDiscountGroupAmount();
        } catch (NoSuchEntityException $e) {
            return $order;
        }

        /** @var OrderExtension $orderExtension */
        $orderExtension = $extensionAttributes ? $extensionAttributes : $this->orderExtensionFactory->create();
        $orderExtension->setDiscountGroupAmount($discountGroupAmount);
        $orderExtension->setBaseDiscountGroupAmount($basediscountGroupAmount);
        $order->setExtensionAttributes($orderExtension);

        return $order;
    }
}

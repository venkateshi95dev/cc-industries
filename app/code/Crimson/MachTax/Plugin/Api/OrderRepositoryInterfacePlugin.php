<?php

namespace Crimson\MachTax\Plugin\Api;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class OrderRepositoryInterfacePlugin
 * @package Crimson\MachTax\Plugin\Api
 */
class OrderRepositoryInterfacePlugin
{
    /**
     * @param OrderRepositoryInterface $subject
     * @param  OrderInterface     $result
     *
     * @return OrderInterface
     */
    public function afterGet(OrderRepositoryInterface $subject, $result): OrderInterface
    {
        $this->_attachExtensionAttributes($result);

        return $result;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $entities
     *
     * @return OrderSearchResultInterface
     */
    public function afterGetList(OrderRepositoryInterface $subject, OrderSearchResultInterface $entities): OrderSearchResultInterface
    {
        /** @var OrderInterface $entity */
        foreach ($entities->getItems() as $entity) {
            $this->afterGet($subject, $entity);
        }

        return $entities;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $entity
     * @return OrderInterface[]
     */
    public function beforeSave(OrderRepositoryInterface $subject, OrderInterface $entity): array
    {
        $entity->setData('tax_code', $entity->getExtensionAttributes()->getTaxCode());

        return [$entity];
    }

    /**
     * @param OrderInterface $order
     *
     * @return $this
     */
    protected function _attachExtensionAttributes(OrderInterface $order): OrderRepositoryInterfacePlugin
    {
        $order->getExtensionAttributes()->setTaxCode($order->getData('tax_code'));

        return $this;
    }
}

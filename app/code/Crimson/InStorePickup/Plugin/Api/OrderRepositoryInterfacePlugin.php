<?php

namespace Crimson\InStorePickup\Plugin\Api;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class OrderRepositoryInterfacePlugin
 * @package Crimson\InStorePickup\Plugin\Api
 */
class OrderRepositoryInterfacePlugin
{

    public function afterGet(OrderRepositoryInterface $subject, $result): OrderInterface
    {
        $this->_attachExtensionAttributes($result);

        return $result;
    }

    public function afterGetList(OrderRepositoryInterface $subject, OrderSearchResultInterface $entities): OrderSearchResultInterface
    {
        foreach ($entities->getItems() as $entity) {
            $this->afterGet($subject, $entity);
        }

        return $entities;
    }

    public function beforeSave(OrderRepositoryInterface $subject, OrderInterface $entity): array
    {
        $entity->setData('source_code', $entity->getExtensionAttributes()->getSourceCode());
        $entity->setData('source_email', $entity->getExtensionAttributes()->getSourceEmail());

        return [$entity];
    }

    protected function _attachExtensionAttributes(OrderInterface $order): OrderRepositoryInterfacePlugin
    {
        $order->getExtensionAttributes()->setSourceCode($order->getData('source_code'));
        $order->getExtensionAttributes()->setSourceEmail($order->getData('source_email'));

        return $this;
    }
}

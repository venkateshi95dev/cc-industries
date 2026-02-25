<?php

namespace Crimson\MachAddressVerification\Plugin\Api;

use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderAddressSearchResultInterface;
use Magento\Sales\Api\OrderAddressRepositoryInterface;

/**
 * Class OrderAddressRepositoryInterfacePlugin
 * @package Crimson\MachAddressVerification\Plugin\Api
 */
class OrderAddressRepositoryInterfacePlugin
{

    /**
     * @param OrderAddressRepositoryInterface $subject
     * @param $result
     * @return mixed
     */
    public function afterGet(OrderAddressRepositoryInterface $subject, $result)
    {
        $this->_attachExtensionAttributes($result);

        return $result;
    }

    /**
     * @param OrderAddressRepositoryInterface $subject
     * @param OrderAddressSearchResultInterface $entities
     * @return OrderAddressSearchResultInterface
     */
    public function afterGetList(OrderAddressRepositoryInterface $subject, OrderAddressSearchResultInterface $entities): OrderAddressSearchResultInterface
    {
        /** @var OrderAddressInterface $entity */
        foreach ($entities->getItems() as $entity) {
            $this->afterGet($subject, $entity);
        }

        return $entities;
    }

    /**
     * @param OrderAddressRepositoryInterface $subject
     * @param OrderAddressInterface $entity
     * @return array
     */
    public function beforeSave(OrderAddressRepositoryInterface $subject, OrderAddressInterface $entity): array
    {
        $entity->setData('ship_adv', $entity->getExtensionAttributes()->getShipAdv());
        $entity->setData('ship_adv_date', $entity->getExtensionAttributes()->getShipAdvDate());
        $entity->setData('ship_adv_dpi', $entity->getExtensionAttributes()->getShipAdvDpi());
        $entity->setData('ship_adv_di', $entity->getExtensionAttributes()->getShipAdvDi());

        return [$entity];
    }

    /**
     * @param OrderAddressInterface $orderAddress
     * @return $this
     */
    protected function _attachExtensionAttributes(OrderAddressInterface $orderAddress): OrderAddressRepositoryInterfacePlugin
    {
        $orderAddress->getExtensionAttributes()->setShipAdv($orderAddress->getData('ship_adv'));
        $orderAddress->getExtensionAttributes()->setShipAdvDate($orderAddress->getData('ship_adv_date'));
        $orderAddress->getExtensionAttributes()->setShipAdvDpi($orderAddress->getData('ship_adv_dpi'));
        $orderAddress->getExtensionAttributes()->setShipAdvDi($orderAddress->getData('ship_adv_di'));

        return $this;
    }
}

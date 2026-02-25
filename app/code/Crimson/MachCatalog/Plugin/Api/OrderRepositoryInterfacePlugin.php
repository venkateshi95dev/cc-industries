<?php
/**
 * @namespace   Crimson
 * @module      ${MODULE}
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/13/2019 11:33 PM
 * @brief
 */

namespace Crimson\MachCatalog\Plugin\Api;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class OrderRepositoryInterfacePlugin
 * @package Crimson\MachCatalog\Plugin\Api
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
     * @param OrderRepositoryInterface         $subject
     * @param  OrderSearchResultInterface $result
     *
     * @return OrderSearchResultInterface
     */
    public function afterGetList(OrderRepositoryInterface $subject, $result): OrderSearchResultInterface
    {
        foreach ($result->getItems() as $order) {
            $this->_attachExtensionAttributes($order);
        }

        return $result;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $entity
     * @return OrderInterface[]
     */
    public function beforeSave(
        OrderRepositoryInterface $subject,
        OrderInterface $entity
    ): array
    {
        $entity->setData('mach_hsc', $entity->getExtensionAttributes()->getMachHsc());

        return [$entity];
    }

    /**
     * @param OrderInterface $order
     *
     * @return $this
     */
    protected function _attachExtensionAttributes(OrderInterface $order): OrderRepositoryInterfacePlugin
    {
        $order->getExtensionAttributes()->setMachHsc((int) $order->getData('mach_hsc'));

        return $this;
    }
}

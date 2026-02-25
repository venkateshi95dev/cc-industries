<?php
/**
 * @namespace   Crimson
 * @module      MageworxOrderEdit
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @brief       We are adding this event observer to trigger an additional event that Adobe I/O events can hook into for
 *              the individual order actions that are updated.
 */

namespace Crimson\MageworxOrderEdit\Observer;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\ObserverInterface;

class MageworxOrderUpdated implements ObserverInterface
{
    public function __construct(
        private readonly ManagerInterface $eventManager
    ) {
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $action = $observer->getEvent()->getData('action');
        $object = $observer->getEvent()->getData('object');
        $initialParams = $observer->getEvent()->getData('initial_params');

        $this->eventManager->dispatch(
            'mageworx_order_updated_' . $action,
            [
                'action' => $action,
                'object' => $object,
                $initialParams            ]
        );
    }
}

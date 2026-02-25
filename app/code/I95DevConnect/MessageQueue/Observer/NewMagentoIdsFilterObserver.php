<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Observer;

use I95DevConnect\MessageQueue\Helper\Data;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Observer class to filter new magento ids
 */
class NewMagentoIdsFilterObserver implements ObserverInterface
{
    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * NewMagentoIdsFilterObserver constructor.
     *
     * @param Data $dataHelper
     */
    public function __construct(
        Data $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
    }

    /**
     * Get Magento Ids
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $is_enabled = $this->dataHelper->isEnabled();
        if (!$is_enabled) {
            return;
        }

        $magentoMgObject = $observer->getEvent()->getData("magentoMgObject");
        $magentoMgObject->magentoIds = ["2", "3"];
    }
}

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
 * Observer class to filter MagentoIds
 */
class MagentoIdsFilterObserver implements ObserverInterface
{
    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * MagentoIdsFilterObserver constructor.
     *
     * @param Data $dataHelper
     */
    public function __construct(
        Data $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
    }

    /**
     * Get Magento ids
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
        $magentoMgObject->magentoIds = [];
    }
}

<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Observer;

use I95DevConnect\MessageQueue\Helper\Data;
use Magento\CatalogInventory\Observer\ItemsForReindex;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Observer class for sales quote submit
 */
class SalesQuoteSubmitBefore implements ObserverInterface
{
    public const I95EXC = 'i95devApiException';

    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * @var ItemsForReindex
     */
    public $itemsForReindex;

    /**
     *
     * @param Data $dataHelper
     * @param ItemsForReindex $itemsForReindex
     */
    public function __construct(
        Data $dataHelper,
        ItemsForReindex $itemsForReindex
    ) {
        $this->dataHelper = $dataHelper;
        $this->itemsForReindex = $itemsForReindex;
    }

    /**
     * Save i95Dev Custom attributes.
     *
     * @param  Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            $is_enabled = $this->dataHelper->isEnabled();
            if (!$is_enabled) {
                return;
            }
            if ($this->dataHelper->getGlobalValue('i95_observer_skip')) {
                $quote = $observer->getQuote();
                $quote->setInventoryProcessed(true);
                $this->itemsForReindex->setItems([]);
            }
        } catch (LocalizedException $ex) {
            $this->dataHelper->logger->createLog(__METHOD__, $ex->getMessage(), self::I95EXC, 'critical');
        }
    }
}

<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Observer;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Price\Tier;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use I95DevConnect\PriceLevel\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Observer to make tier prices form read only
 */
class TierPriceObserver implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var Data
     */
    public $data;

    /**
     *
     * @var \I95DevConnect\MessageQueue\Helper\Data
     */
    public $dataHelper;

    /**
     * Class constructor to include all the dependencies
     *
     * @param LoggerInterface $logger
     * @param \I95DevConnect\MessageQueue\Helper\Data $dataHelper
     * @param Data $data
     */
    public function __construct(
        LoggerInterface $logger,
        \I95DevConnect\MessageQueue\Helper\Data $dataHelper,
        Data $data
    ) {

        $this->logger = $logger;
        $this->data = $data;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Set read only tier prices ui to product
     *
     * @param  Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $block = $observer->getBlock();
        if (!$this->data->isEnabled()) {
            return $this;
        }
        if (!isset($block)) {
            return $this;
        }

        $blockClass = get_class_methods($block);

        if (Tier::class == $blockClass) {
            $block->setTemplate('I95DevConnect_PriceLevel::tier.phtml');
        }
    }
}

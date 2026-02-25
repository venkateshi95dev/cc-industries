<?php
declare(strict_types=1);

namespace Crimson\CustomReports\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Psr\Log\LoggerInterface;

class QueueStockDataOnAddToCart implements ObserverInterface
{
    public const TOPIC_NAME = 'crimson.abandonmentreport.savestockdata';

    public function __construct(
        protected PublisherInterface $publisher,
        protected LoggerInterface    $logger
    )
    {
    }

    public function execute(Observer $observer): void
    {
        try {
            $quoteItem = $observer->getData('quote_item');
            if ($quoteItem->getItemId() && $quoteItem->getParentItemId() === null) {
                $this->publisher->publish(self::TOPIC_NAME, (int)$quoteItem->getItemId());
            } else {
                $this->logger->error('AbandonmentReportBySku: Quote item ID is missing.');
            }
        } catch (\Exception $e) {
            $this->logger->error(
                'AbandonmentReportBySku Publisher Error: ' . $e->getMessage()
            );
        }
    }
}

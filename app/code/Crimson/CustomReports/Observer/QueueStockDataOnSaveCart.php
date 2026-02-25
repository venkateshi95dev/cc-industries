<?php
declare(strict_types=1);

namespace Crimson\CustomReports\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Psr\Log\LoggerInterface;

class QueueStockDataOnSaveCart implements ObserverInterface
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
            $cart = $observer->getData('cart');
            foreach ($cart->getItems() as $key => $item) {
                if ($item->getParentItemId() != null) {
                    continue;
                }
                $this->publisher->publish(self::TOPIC_NAME, (int)$item->getItemId());
            }
        } catch (\Exception $e) {
            $this->logger->error(
                'AbandonmentReportBySku Publisher Error: ' . $e->getMessage()
            );
        }
    }
}

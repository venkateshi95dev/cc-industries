<?php

declare(strict_types=1);

namespace Crimson\AmastyStorePickupWithLocator\Model\Queue\Email;

use Crimson\AmastyStorePickupWithLocator\Model\Queue\Email\Dto\EmailConfig;
use Magento\Framework\MessageQueue\PublisherInterface;

class Publisher
{
    public const TOPIC_NAME = 'crimson_storepickup_shipment_email.send';

    public function __construct(
       private readonly PublisherInterface $publisher,
    ) {}

    public function publish(EmailConfig $emailConfig): void
    {
        $this->publisher->publish(self::TOPIC_NAME, $emailConfig);
    }
}

<?php

declare(strict_types=1);

namespace Crimson\AmastyStorePickupWithLocator\Model\Queue\Email;

use Crimson\AmastyStorePickupWithLocator\Model\Queue\Email\Dto\EmailConfig;
use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

class Consumer
{

    public function __construct(
        private readonly TransportBuilder $transportBuilder,
        private readonly Json $jsonSerializer,
        private readonly LoggerInterface $logger
    ) {}

    public function process(EmailConfig $emailConfig): void
    {
        $template = $emailConfig->getTemplate();
        $sender = $emailConfig->getSender();
        $recipients = $emailConfig->getRecipients();
        if (!$sender || !$template || !$recipients) {
            return;
        }

        $storeId = $emailConfig->getStoreId();
        $data = $emailConfig->getJsonData()
            ? $this->jsonSerializer->unserialize($emailConfig->getJsonData())
            : [];

        try {
            foreach ($recipients as $recipient) {
                $transport = $this->transportBuilder->setTemplateIdentifier($template)
                    ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $storeId])
                    ->setTemplateVars($data)
                    ->setFromByScope($sender, $storeId)
                    ->addTo($recipient)
                    ->getTransport();
                $transport->sendMessage();
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}

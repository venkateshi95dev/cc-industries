<?php

declare(strict_types=1);

namespace Crimson\AmastyStorePickupWithLocator\Model\Queue\Email;

use Amasty\StorePickupWithLocatorSubscriptionFunctionality\Model\Queue\Email\Dto\EmailConfig;
use Crimson\AmastyStorePickupWithLocator\Logger\Logger;
use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class AmastyConsumerCustom
{

    public function __construct(
        private readonly TransportBuilder  $transportBuilder,
        private readonly Json              $jsonSerializer,
        protected OrderRepositoryInterface $orderRepository,
        protected Logger $logger,
    ) {}

    public function process(EmailConfig $emailConfig): void
    {
        $this->logger->debug("**************** Starting Email Send ****************");
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

        //customization here, adding extra info to template variables
        if (!empty($data['order_id'])) {
            $order = $this->_getOrderById((int) $data['order_id']);
            $data['customer_name']  = $order ? $order->getCustomerName() : '';
            $data['customer_email'] = $order ? $order->getCustomerEmail() : '';
            $data['customer_phone'] = $order && $order->getBillingAddress() ? $order->getBillingAddress()->getTelephone() : '';
        }

        $this->logger->debug("Email Data");
        $this->logger->debug($this->jsonSerializer->serialize($data));

        try {
            foreach ($recipients as $recipient) {
                $this->logger->debug("Sending Email to: " . $recipient);
                $transport = $this->transportBuilder->setTemplateIdentifier($template)
                    ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $storeId])
                    ->setTemplateVars($data)
                    ->setFromByScope($sender, $storeId)
                    ->addTo($recipient)
                    ->getTransport();
                $this->logger->debug("TRYING Email now");
                $transport->sendMessage();
                $this->logger->debug("Email sent to: " . $recipient);
            }
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        } finally {
            $this->logger->debug("**************** END ****************");
        }
    }

    private function _getOrderById(int $orderId): ?OrderInterface
    {
        try {
            return $this->orderRepository->get($orderId);
        } catch (\Exception $e) {
            return null;
        }
    }
}

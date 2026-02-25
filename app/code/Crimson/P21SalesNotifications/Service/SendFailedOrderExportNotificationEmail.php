<?php

namespace Crimson\P21SalesNotifications\Service;

use Crimson\P21SalesNotifications\Api\SendFailedOrderExportNotificationEmailInterface;
use Crimson\P21SalesNotifications\Model\Config;
use Magento\Backend\Model\Url;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class SendFailedOrderExportNotificationEmail implements SendFailedOrderExportNotificationEmailInterface
{
    public function __construct(
        private readonly Config $config,
        private readonly TransportBuilder $transportBuilder,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly Url $backendUrl,
        private readonly Escaper $escaper,
        private readonly OrderStatusHistoryInterfaceFactory $orderStatusHistoryFactory,
    ) {
    }

    public function send(
        int $magentoOrderEntityId,
        string $errorBody = '',
        string $errorSubject = '',
        array $additionalEmailRecipients = [],
        bool $addComment = false,
    ): bool {
        $magentoOrder = $this->orderRepository->get($magentoOrderEntityId);

        $defaultRecipients = $this->config->getFailedOrderExportEmailRecipients();
        $recipients = array_unique(array_merge($defaultRecipients, $additionalEmailRecipients));

        if (!$recipients) {
            return false;
        }

        $emailTemplate = $this->config->getFailedORderExportEmailTemplate();

        $this->transportBuilder->setTemplateIdentifier($emailTemplate)
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_ADMINHTML,
                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
            ])
            ->setTemplateVars([
                'magento_order_number' => $magentoOrder->getIncrementId(),
                'magento_order_id' => $magentoOrderEntityId,
                'magento_order_link' => $this->backendUrl->getUrl('sales/order/view', [
                    'order_id' => $magentoOrderEntityId,
                ]),
                'lastname' => $magentoOrder->getCustomerLastname(),
                'firstname' => $magentoOrder->getCustomerFirstname(),
                'email' => $magentoOrder->getCustomerEmail(),
                'telephone' => $this->_getTelephone($magentoOrder),
                'error_subject' => $this->escaper->escapeHtml($errorSubject),
                'error_body' => $this->escaper->escapeHtml(
                    $errorBody,
                    ['span', 'strong', 'br', 'ul', 'li', 'p', 'table', 'tr', 'th', 'td', 'tbody', 'thead', 'tfood', 'u', 'i', 'b', 'a']
                ),
            ])
            ->setFromByScope('general');

        foreach ($recipients as $recipient) {
            $this->transportBuilder->addTo($recipient);
        }

        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();

        if ($addComment) {
            /** @var \Magento\Sales\Api\Data\OrderStatusHistoryInterface $statusHistory */
            $statusHistory = $this->orderStatusHistoryFactory->create();
            $statusHistory->setComment($errorBody)
                ->setIsVisibleOnFront(false);

            $magentoOrder->addStatusHistory($statusHistory);
            $this->orderRepository->save($magentoOrder);
        }

        return true;
    }

    protected function _getTelephone(OrderInterface|Order $magentoOrder): ?string
    {
        $shippingAddress = $magentoOrder->getShippingAddress();
        if ($shippingAddress && $shippingAddress->getTelephone()) {
            return $shippingAddress->getTelephone();
        }

        $billingAddress = $magentoOrder->getBillingAddress();
        if ($billingAddress && $billingAddress->getTelephone()) {
            return $billingAddress->getTelephone();
        }

        return null;
    }
}

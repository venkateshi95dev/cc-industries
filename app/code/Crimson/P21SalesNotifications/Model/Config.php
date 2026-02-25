<?php

namespace Crimson\P21SalesNotifications\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    const XPATH_P21_SALES_NOTIFICATIONS_FAILED_ORDER_EXPORT_EMAIL_RECIPIENTS = 'p21_sales_notifications/failed_order_export/email_recipients';
    const XPATH_P21_SALES_NOTIFICATIONS_FAILED_ORDER_EXPORT_EMAIL_TEMPLATE = 'p21_sales_notifications/failed_order_export/email_template';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
    ) {
    }

    public function getFailedOrderExportEmailRecipients(): array
    {
        $emailRecipients = $this->scopeConfig->getValue(self::XPATH_P21_SALES_NOTIFICATIONS_FAILED_ORDER_EXPORT_EMAIL_RECIPIENTS);
        if (!$emailRecipients) {
            return [];
        }

        $emailRecipients = trim($emailRecipients);
        $emailRecipients = trim($emailRecipients, ',');

        return array_map('trim', explode(',', $emailRecipients));
    }

    public function getFailedORderExportEmailTemplate(): string
    {
        return $this->scopeConfig->getValue(self::XPATH_P21_SALES_NOTIFICATIONS_FAILED_ORDER_EXPORT_EMAIL_TEMPLATE);
    }
}

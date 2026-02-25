<?php

namespace Crimson\P21SalesNotifications\Api;

interface SendFailedOrderExportNotificationEmailInterface
{
    /**
     * @param int    $magentoOrderEntityId
     * @param string $errorBody
     * @param string $errorSubject
     * @param string[] $additionalEmailRecipients - additional recipients to what is configured in the module
     *                                            - Allowed HTML tags: span, strong, br, ul, li, p, table, tr, th, td, tbody, thead, tfood, u, i, b, a
     * @param bool   $addComment - whether to additionally add the error body as a comment to the order
     *
     * @return bool
     */
    public function send(
        int $magentoOrderEntityId,
        string $errorBody = '',
        string $errorSubject = '',
        array $additionalEmailRecipients = [],
        bool $addComment = false
    ): bool;
}

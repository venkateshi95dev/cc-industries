<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Forward;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;

/**
 * Class responsible for preparing order comment data which will be added in order result to ERP
 */
class OrderComments
{
    /**
     *
     * @var OrderManagementInterface
     */
    public $orderManagement;

    /**
     *
     * @param OrderManagementInterface $orderManagement
     */
    public function __construct(
        OrderManagementInterface $orderManagement
    ) {
        $this->orderManagement = $orderManagement;
    }

    /**
     * Returns comment history from order
     *
     * @param  OrderInterface $orderId
     * @throws LocalizedException
     * @return array
     * @author Debashis S. Gopal
     */
    public function getOrderComments($orderId)
    {
        try {
            $commentsHistory = [];
            $commentsHistoryData = $this->orderManagement->getCommentsList($orderId)->getData();
            if (!empty($commentsHistoryData)) {
                foreach ($commentsHistoryData as $comments) {
                    $orderComments = [];
                    if (isset($comments['comment'])) {
                        $orderComments['comment'] = str_replace('"', '', $comments['comment']);
                        $orderComments['source'] = "admin";
                        $orderComments['createdDate'] = $comments['created_at'];
                        $commentsHistory[] = $orderComments;
                    }
                }
            }
            return $commentsHistory;
        } catch (LocalizedException $ex) {
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
    }
}

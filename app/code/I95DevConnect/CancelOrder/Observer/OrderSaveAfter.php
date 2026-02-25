<?php

/**
 * Send email to customer on order cancel
 *
 * @package   I95DevConnect_CancelOrder
 * @author    i95Dev Team <info@i95dev.com>
 * @copyright Copyright (c) 2021 i95Dev(https://www.i95dev.com)
 */

namespace I95DevConnect\CancelOrder\Observer;

use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\Logger;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;
use I95DevConnect\CancelOrder\Helper\Data as Helper;

class OrderSaveAfter implements ObserverInterface
{
    /**
     * @var OrderCommentSender
     */
    protected $commentEmailSender;

    /**
     * @var Logger
     */
    public $logger;

    /**
     * @var Helper
     */
    public $helper;

    /**
     * OrderSaveAfter constructor.
     *
     * @param OrderCommentSender $commentEmailSender
     * @param Logger             $logger
     * @param Helper             $helper
     */
    public function __construct(
        OrderCommentSender $commentEmailSender,
        Logger $logger,
        Helper $helper
    ) {
        $this->commentEmailSender = $commentEmailSender;
        $this->logger = $logger;
        $this->helper = $helper;
    }

    /**
     * Execute function
     *
     * @param  Observer $observer
     * @return bool|string
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($this->helper->isEnabled() && $order->getState() === 'canceled') {
            try {
                $this->commentEmailSender->send($order, true);
            } catch (LocalizedException $ex) {
                $this->logger->createLog(
                    __METHOD__,
                    $ex->getMessage(),
                    Data::I95EXC,
                    'critical'
                );

                return $ex->getMessage();
            }
        }

        return true;
    }
}

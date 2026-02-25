<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Forward\Payment;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class for PaymentInfo payment method data in order result to ERP
 */
class PaymentInfo
{
    /**
     * @var PaymentEntity
     */
    public $paymentEntity;

    /**
     *
     * @param PaymentEntity $paymentEntity
     */
    public function __construct(
        PaymentEntity $paymentEntity
    ) {
        $this->paymentEntity = $paymentEntity;
    }

    /**
     * Assign payment data to payment entity
     *
     * @param OrderInterface $order
     * @throws LocalizedException
     * @return array
     * @createdBy SravaniPolu
     */
    public function getOrderPayment($order)
    {
        $paymentEntityDetails = [];
        try {
            $paymentData = $order->getPayment();
            $paymentEntityDetails[] = $this->paymentEntity->assignPaymentData($paymentData);
        } catch (LocalizedException $ex) {
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }

        return $paymentEntityDetails;
    }
}

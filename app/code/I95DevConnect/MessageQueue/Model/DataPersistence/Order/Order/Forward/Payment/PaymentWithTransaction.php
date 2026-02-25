<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Forward\Payment;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class for PaypalExpressCheckout payment method data in order result to ERP
 */
class PaymentWithTransaction
{
    /**
     * Assign payment data to payment entity
     *
     * @param object $paymentData
     * @throws LocalizedException
     * @return array
     * @createdBy Sravani Polu
     */
    public function assignPaymentData($paymentData)
    {
        try {
            $paymentEntity['paymentMethod'] = $paymentData->getMethod();
            $paymentEntity['transactionNumber'] = $paymentData->getLastTransId();
            return $paymentEntity;
        } catch (LocalizedException $ex) {
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
    }
}

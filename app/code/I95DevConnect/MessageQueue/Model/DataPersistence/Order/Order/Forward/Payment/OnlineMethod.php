<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Forward\Payment;

use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Class for Charge Logic and authorize .net method data in order result to ERP
 */
class OnlineMethod
{
    /**
     * @var LoggerInterface
     */
    public $_logger;// phpcs:ignore
    /**
     * @var array
     */
    public $paymentEntity = [];

    /**
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->_logger = $logger;
    }

    /**
     * Assign payment data to payment entity
     *
     * @param  object $paymentData
     * @throws LocalizedException
     * @return array
     */
    public function assignPaymentData($paymentData)
    {
        try {
            $ccNumber = '1111';
            $month = date('m');
            $dummyYear = date('Y', strtotime('+1 year'));
            $year = $dummyYear;
            $type = 'VI';
            $this->paymentEntity['paymentMethod'] = $paymentData->getMethod();
            $this->paymentEntity['ccNumber'] = $ccNumber;
            $this->paymentEntity['ccExpMonth'] = $month;
            $this->paymentEntity['ccExpYear'] = $year;
            $this->paymentEntity['ccType'] = $type;
            $this->paymentEntity['transactionNumber'] = $paymentData->getLastTransId();
        } catch (LocalizedException $ex) {
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
        return $this->paymentEntity;
    }
}

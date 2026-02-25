<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2021 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Forward\Payment;

use I95DevConnect\MessageQueue\Model\ChequeNumberFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class for payment data for order from Magento to ERP
 */
class PaymentEntity
{
    /**
     * @var I95DevConnect\MessageQueue\Model\ChequeNumber
     */
    public $chequeNumberModel;

    /**
     *
     * @param ChequeNumberFactory $chequeNumber
     */
    public function __construct(
        ChequeNumberFactory $chequeNumber
    ) {
        $this->chequeNumberModel = $chequeNumber;
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
        $paymentEntity = [];
        try {
            $paymentMethod = $paymentData->getMethod();
            if ($paymentMethod === "checkmo") {
                $paymentEntity = $this->getCheckDetails($paymentData);
            }
            $paymentEntity['paymentMethod'] = $paymentData->getMethod();
            if (!empty($paymentData->getPoNumber())) {
                $paymentEntity['poNumber'] = $paymentData->getPoNumber();
            }
            if (!empty($paymentData->getCcLast4())) {
                $paymentEntity['ccNumber'] = $paymentData->getCcLast4();
            }
            if (!empty($paymentData->getCcExpMonth())) {
                $paymentEntity['ccExpMonth'] = $paymentData->getCcExpMonth();
            }
            if (!empty($paymentData->getCcExpYear())) {
                $paymentEntity['ccExpYear'] = $paymentData->getCcExpYear();
            }
            if (!empty($paymentData->getCcType())) {
                $paymentEntity['ccType'] = $paymentData->getCcType();
            }
            if (!empty($paymentData->getLastTransId())) {
                $paymentEntity['transactionNumber'] = $paymentData->getLastTransId();
            }
            if (!empty($paymentData->getData('ebzc_cust_id'))) {
                $paymentEntity['ebzcCustId'] = $paymentData->getData('ebzc_cust_id');
            }
            if (!empty($paymentData->getData('cc_trans_id'))) {
                $paymentEntity['ccTransId'] = $paymentData->getData('cc_trans_id');
            }
            if (!empty($paymentData->getData('ebzc_method_id'))) {
                $paymentEntity['ebzcMethodId'] = $paymentData->getData('ebzc_method_id');
            }
            $additionalInfo = $paymentData->getAdditionalInformation();
            if (!empty($additionalInfo)) {
                $paymentEntity['additionalInfo'][] = $additionalInfo;
            }
        } catch (LocalizedException $ex) {
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }

        return $paymentEntity;
    }

    /**
     * Get check details
     *
     * @param object $paymentData
     * @return array
     */
    public function getCheckDetails($paymentData)
    {
        $paymentEntity = [];
        try {
            $orderId = $paymentData->getParentId();
            $chequeNumber = $this->chequeNumberModel->create()->getCollection()
                ->addFieldToSelect('target_cheque_number')
                ->addFieldToFilter('source_order_id', $orderId);
            $chequeNumber->getSelect()->limit(1);
            $chequeNumber = $chequeNumber->getData();
            if (isset($chequeNumber[0])) {
                $chequeNumber = $chequeNumber[0]['target_cheque_number'];
            } else {
                $chequeNumber = null;
            }

            $paymentEntity['chequeNumber'] = $chequeNumber;
            $paymentEntity['transactionNumber'] = $chequeNumber;
        } catch (LocalizedException $ex) {
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }

        return $paymentEntity;
    }
}

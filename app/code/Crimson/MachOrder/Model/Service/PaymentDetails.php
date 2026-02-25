<?php

namespace Crimson\MachOrder\Model\Service;

use Magento\Framework\Api\FilterBuilderFactory;
use Magento\Framework\Api\Search\SearchCriteriaBuilderFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;

class PaymentDetails
{

    public function __construct(
        private readonly TransactionRepositoryInterface $transactionRepository,
        private readonly SearchCriteriaBuilderFactory $searchCriteriaBuilder,
        private readonly FilterBuilderFactory $filterBuilderFactory,
    ) {}

    public function getTransactionByOrder(OrderInterface $order): null|TransactionInterface
    {
        $payment = $order->getPayment();

        if (!($transactionId = $payment->getCcTransId()) &&
            !($transactionId = $payment->getAdditionalInformation('parent_transaction_id'))) {
            return null;
        }

        $paymentId = $payment->getEntityId();
        if ($payment->getAdditionalInformation('parent_payment_id')) {
            $paymentId = $payment->getAdditionalInformation('parent_payment_id');
        }

        $transIdFilter = $this->filterBuilderFactory->create()->setField('txn_id')->setValue($transactionId)->create();
        $paymentIdFilter = $this->filterBuilderFactory->create()
            ->setField('payment_id')
            ->setValue($paymentId)
            ->create();
        $transactionList = $this->transactionRepository->getList(
            $this->searchCriteriaBuilder->create()->addFilter($transIdFilter)->addFilter($paymentIdFilter)->create()
        );

        if ($transactionList->getTotalCount() == 0) {
            return null;
        }

        return current($transactionList->getItems());
    }

    public function getAuthCode(null|TransactionInterface $transaction): null|string
    {
        if (!$transaction) {
            return null;
        }

        $rawDetails = $transaction->getAdditionalInformation();
        if (!$rawDetails) {
            return null;
        }

        return $rawDetails['raw_details_info']['authcode'] ?? null;
    }

    public function getAvsCode(null|TransactionInterface $transaction): null|string
    {
        if (!$transaction) {
            return null;
        }

        $rawDetails = $transaction->getAdditionalInformation();
        if (!$rawDetails) {
            return null;
        }

        return $rawDetails['raw_details_info']['avsresponse'] ?? null;
    }

    public function getCvvCode(null|TransactionInterface $transaction): null|string
    {
        if (!$transaction) {
            return null;
        }

        $rawDetails = $transaction->getAdditionalInformation();
        if (!$rawDetails) {
            return null;
        }

        return $rawDetails['raw_details_info']['cvvresponse'] ?? null;
    }
}

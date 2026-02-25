<?php

namespace Silk\Payment\Helper;

use Magento\Sales\Model\Order;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\Payment\Transaction as TransactionModel;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface as TransactionBuilder;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem\DirectoryList;

/**
 * @SuppressWarnings(PHPMD)
 */
class Transaction extends AbstractHelper
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;
    /**
     * @var TransactionBuilder
     */
    protected $transactionBuilder;

    /**
     * @var TransactionRepositoryInterface
     */
    protected $transactionRepository;


    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        TransactionBuilder $transactionBuilder,
        CartRepositoryInterface $cartRepository,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->transactionBuilder = $transactionBuilder;
        $this->cartRepository = $cartRepository;
        $this->transactionRepository = $transactionRepository;
        parent::__construct($context);
    }

    public function buildTransaction(
        Order $order,
        $amount,
        $transactionId,
        $additionalInformation = [],
        $type = TransactionModel::TYPE_AUTH
    ) {
        return $this->transactionBuilder
            ->setPayment($order->getPayment())
            ->setOrder($order)
            ->setTransactionId($transactionId)
            ->setAdditionalInformation($additionalInformation)
            ->setFailSafe(true)
            ->build($type);
    }


    protected function getTransactionSetupRequestData(Order $order)
    {
        return $this->getTransactionSetupPaymentData($order);
    }

    /**
     * @param Order $order
     * @param $paymentAccountId
     * @param null $amount
     * @throws PaymentRejectedException
     * @throws Exception
     */
    public function doAuthorizationTransaction(Order $order, $amount = null)
    {
        try {
            $transactionId = $this->getAuthorizationTransactionId($order->getIncrementId());
            // Load Transaction Data
            if (!$transactionData = $this->getTransactionSetupRequestData($order)) {
                throw new LocalizedException(__('Payment Error'));
            }
            // Create Auth Transaction
            $transaction = $this->buildTransaction(
                $order,
                $amount,
                $transactionId,
                [TransactionModel::RAW_DETAILS => $transactionData]
            );
            // Payment Data Update
            $payment = $order->getPayment();
            $payment->setLastTransId($transactionId);
            $payment->setTransactionId($transactionId);
            $payment->setIsTransactionClosed(true);
            $payment->setAmountAuthorized($order->getTotalDue());
            $payment->setBaseAmountAuthorized($order->getBaseTotalDue());
            // Update Transaction Data
            $payment->setAdditionalInformation('transaction_authorize_data', $transactionData);
            // Save
            $payment->addTransactionCommentsToOrder($transaction, __('Authorization Transaction Created.'));
            $payment->setParentTransactionId(null);
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            throw $e;
        }

        $this->saveOrderAuthorizationSuccess($order, $transaction);
    }

    /**
     * @param Order $order
     * @param TransactionInterface $transaction
     * @return $this
     */
    protected function saveOrderAuthorizationSuccess(Order $order, TransactionInterface $transaction)
    {
        // Update Order Status
        $fPrice = $order->getBaseCurrency()->formatTxt($order->getGrandTotal());
        $order->setState(Order::STATE_PROCESSING);
        $order->setStatus(Order::STATE_PROCESSING);
        $order->addCommentToStatusHistory(__('Authorized amount: %1.', $fPrice));

        $this->orderRepository
            ->save($order);

        return $this;
    }


    /**
     * @param $transactionId
     * @return string
     */
    public function getAuthorizationTransactionId($transactionId)
    {
        return 'authorize_' . $transactionId;
    }

    /**
     * @param $transactionId
     * @return string
     */
    public function getTransactionSetupTransactionId($transactionId)
    {
        return 'transaction_setup_' . $transactionId;
    }

    private function getTransactionSetupPaymentData(Order $order)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/coker.quote.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $data =[];
        $logger->info('quote id', [$order->getQuoteId()]);
        try {
            $quote = $this->cartRepository->get($order->getQuoteId());
        } catch (Exception $e) {
            $quote = null;
        }
        if (!$quote) {
            return $data;
        }
        $logger->info('quote info', [$quote->getData()]);
        if ($clientToken = $quote->getPaymentclienttoken()) {
            $directory = ObjectManager::getInstance()->get(DirectoryList::class);
            $var = $directory->getPath('var');
            $dir = $var."/payment/";
            $filename = $dir.$clientToken.".log";
            if (file_exists($filename)) {
                $json_string = file_get_contents($filename);
                $jsonData = json_decode($json_string, true);
                $data = $this->toArray($jsonData);
            }
        }
        $logger->info('transaction info', [$data]);
        return $data;
    }
    private function toArray($array)
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $new = $this->toArray($value);
                $result = array_merge($result, $new);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}

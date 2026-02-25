<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PaymentJournal
 */

namespace I95DevConnect\PaymentJournal\Model\DataPersistence\PaymentJournal\Forward;

use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Forward\Payment\PaymentInfo;
use I95DevConnect\PaymentJournal\Model\PaymentJournalFactory;
use Magento\Framework\Event\Manager;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class For Send Payment Journal Info To ERP
 */
class Info
{
    public const TARGET_INVOICE_ID = "target_invoice_id";
    public const SOURCE_INVOICE_ID = "source_invoice_id";
    public const SOURCE_ORDER_ID = "source_order_id";
    public const INVOICE_AMOUNT = "invoice_amount";
    public const INVOICE_DATE = "invoice_date";
    public const PAYMENT_METHOD = "payment_method";
    public const TRAN_INVOICE_AMOUNT =  'transactionInvoiceAmount';
    public const BASE_CURRENCY_CODE = 'baseCurrencySymbol';
    public const TRAN_CURRENCY_CODE = 'transactionCurrencySymbol';
    public const CONVERSION_FACTOR = 'conversionFactor';
    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * @var Manager
     */
    public $eventManager;

    /**
     * @var PaymentJournalFactory
     */
    public $paymentJournalFactory;

    /**
     * @var OrderRepositoryInterface
     */
    public $orderRepository;

    /**
     * @var InvoiceRepositoryInterface
     */
    public $invoiceRepository;

    /**
     * @var PaymentInfo
     */
    public $paymentInfo;

    /**
     * @var string[]
     */
    public $fieldMapInfo = [
        'sourcePaymentJournalId' => 'id',
        'targetInvoiceId' => self::TARGET_INVOICE_ID,
        'sourceInvoiceId' => self::SOURCE_INVOICE_ID,
        'sourceOrderId' => self::SOURCE_ORDER_ID,
        'invoiceAmount' => self::INVOICE_AMOUNT,
        'transactionInvoiceAmount' => self::TRAN_INVOICE_AMOUNT,
        'paymentDate' => self::INVOICE_DATE,
        'paymentMethod' => self::PAYMENT_METHOD,
        'baseCurrencySymbol' => self::BASE_CURRENCY_CODE,
        'transactionCurrencySymbol' => self::TRAN_CURRENCY_CODE,
        'conversionFactor' => self::CONVERSION_FACTOR
    ];

    /**
     * @var string[]
     */
    public $InfoData;

    /**
     * Info constructor.
     * @param Data $dataHelper
     * @param Manager $eventManager
     * @param PaymentJournalFactory $paymentJournalFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param PaymentInfo $paymentInfo
     */
    public function __construct(
        Data $dataHelper,
        Manager $eventManager,
        PaymentJournalFactory $paymentJournalFactory,
        OrderRepositoryInterface $orderRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        PaymentInfo $paymentInfo
    ) {
        $this->dataHelper = $dataHelper;
        $this->eventManager = $eventManager;
        $this->paymentJournalFactory = $paymentJournalFactory;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->paymentInfo = $paymentInfo;
    }

    /**
     * Get Payment Journal Info
     *
     * @param string $journalId
     * @param string $entityCode
     * @param string $erpCode
     * @return array
     * @author Hrusikesh Manna
     */
    public function getInfo($journalId, $entityCode, $erpCode) //NOSONAR
    {
        $data = $this->getPaymentJournalDetails($journalId);
        $this->InfoData = $this->dataHelper->prepareInfoArray($this->fieldMapInfo, $data);
        $event = "erpconnect_forward_paymenyJournalInfo";
        $this->eventManager->dispatch($event, ['paymentJournal' => $this]);
        return $this->InfoData;
    }

    /**
     * Get Payment Journal Details By Id
     *
     * @param int $id
     * @return array
     * @author Hrusikesh Manna
     */
    public function getPaymentJournalDetails($id)
    {
        $model = $this->paymentJournalFactory->create();
        $details = $model->load($id)->getData();
        $orderDetails = $this->getOrderDetails($details[self::SOURCE_ORDER_ID]);
        $invoiceDetails = $this->getInvoiceDetails($details[self::SOURCE_INVOICE_ID]);
        $payment = $this->paymentInfo->getOrderPayment($orderDetails);
        $paymentMethod = !empty($payment) ? $payment[0]["paymentMethod"] : null;
        if ($details['invoice_amount'] > 0) {
            $invoiceAmount = $details['invoice_amount'] ?? 0;
        } else {
            $invoiceAmount = $invoiceDetails->getGrandTotal();
        }
        return [
            'id' => $details['id'],
            self::TARGET_INVOICE_ID => $details[self::TARGET_INVOICE_ID],
            self::SOURCE_INVOICE_ID => $invoiceDetails->getIncrementId(),
            self::SOURCE_ORDER_ID => $orderDetails->getIncrementId(),
            self::INVOICE_AMOUNT => number_format($invoiceAmount / $orderDetails->getBaseToOrderRate(), 2),
            self::TRAN_INVOICE_AMOUNT => $invoiceAmount,
            self::INVOICE_DATE => $invoiceDetails->getCreatedAt(),
            self::PAYMENT_METHOD => $paymentMethod,
            self::BASE_CURRENCY_CODE => $orderDetails->getBaseCurrencyCode(),
            self::TRAN_CURRENCY_CODE => $orderDetails->getOrderCurrencyCode(),
            self::CONVERSION_FACTOR => $orderDetails->getBaseToOrderRate()
        ];
    }

    /**
     * Get order Details
     *
     * @param int $orderId
     * @return object
     */
    public function getOrderDetails($orderId)
    {
        return $this->orderRepository->get($orderId);
    }

    /**
     * Get invoice details by Id
     *
     * @param int $invoiceId
     * @return object
     * @author Hrusikesh Manna
     */
    public function getInvoiceDetails($invoiceId)
    {
        return $this->invoiceRepository->get($invoiceId);
    }
}

<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PaymentJournal
 */

namespace I95DevConnect\PaymentJournal\Observer;

use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\PaymentJournal\Model\PaymentJournalFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Mq to Magento invoice save after observer
 */
class MqInvoiceSaveAfter implements ObserverInterface
{
    /**
     * @var PaymentJournalFactory
     */
    public $paymentJournal;

    /**
     * @var DateTime
     */
    public $date;

    /**
     * @var ManagerInterface
     */
    public $eventManager;

    /**
     * @var Data
     */
    public $baseHelperData;
    /**
     * @var OrderRepositoryInterface
     */
    public $orderRepository;

    /**
     * MqInvoiceSaveAfter constructor.
     * @param PaymentJournalFactory $paymentJournal
     * @param DateTime $date
     * @param ManagerInterface $eventManager
     * @param OrderRepositoryInterface $orderRepository
     * @param Data $baseHelperData
     */
    public function __construct(
        PaymentJournalFactory $paymentJournal,
        DateTime $date,
        ManagerInterface $eventManager,
        OrderRepositoryInterface $orderRepository,
        Data $baseHelperData
    ) {
        $this->paymentJournal = $paymentJournal;
        $this->date = $date;
        $this->eventManager = $eventManager;
        $this->orderRepository = $orderRepository;
        $this->baseHelperData = $baseHelperData;
    }

    /**
     * Execute method
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            $currentObject = $observer->getEvent()->getData("currentObject");
            $orderId  = $currentObject->orderObject->getEntityId();
            $orderDetails = $this->orderRepository->get($orderId);
            $isOffline = $orderDetails->getPayment()->getMethodInstance()->isOffline();
            if ($isOffline) {
                return;
            }
            $this->baseHelperData->unsetGlobalValue('i95_observer_skip');
            $this->baseHelperData->setGlobalValue('i95_observer_skip', false);
            $paymentJournalFactory = $this->paymentJournal->create();
            $paymentJournalFactory->setSourceInvoiceId($currentObject->invoiceId);
            $paymentJournalFactory->setTargetInvoiceId($currentObject->targetInvoiceId);
            $paymentJournalFactory->setSourceOrderId($currentObject->orderObject->getId());
            $paymentJournalFactory->setInvoiceAmount($currentObject->grandTotal);
            $paymentJournalFactory->setCreatedDt($this->date->gmtDate());
            $paymentJournalFactory->setUpdatedDt($this->date->gmtDate());
            $paymentJournalFactory->save();
            $data = $this->paymentJournal->create()->load($paymentJournalFactory->getId());
            // Dispatch after save event
            //$afterEventName = 'payment_journal_save_after';
            //$this->eventManager->dispatch($afterEventName, ['data_object' => $data]);
        } catch (LocalizedException $ex) {
            throw new LocalizedException(__($ex->getMessage()));
        }
    }
}

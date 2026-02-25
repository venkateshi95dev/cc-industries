<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PaymentJournal
 */

namespace I95DevConnect\PaymentJournal\Model\DataPersistence\PaymentJournal\Forward;

use I95DevConnect\MessageQueue\Api\I95DevResponseInterface;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\AbstractDataPersistence;
use I95DevConnect\PaymentJournal\Model\PaymentJournalFactory;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class For Set Payment Journal Response From ERP
 */
class Response
{
    /**
     * @var PaymentJournalFactory
     */
    public $paymentJournalFactory;

    /**
     * @var DateTime
     */
    public $date;

    /**
     * @var AbstractDataPersistence
     */
    public $abstractDataPersistence;

    /**
     * @var Manager
     */
    public $eventManager;
    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * Response constructor.
     * @param LoggerInterface $logger
     * @param PaymentJournalFactory $paymentJournalFactory
     * @param DateTime $date
     * @param AbstractDataPersistence $abstractDataPersistence
     * @param Manager $eventManager
     */
    public function __construct(
        LoggerInterface $logger,
        PaymentJournalFactory $paymentJournalFactory,
        DateTime $date,
        AbstractDataPersistence $abstractDataPersistence,
        Manager $eventManager
    ) {
        $this->logger = $logger;
        $this->paymentJournalFactory = $paymentJournalFactory;
        $this->date = $date;
        $this->abstractDataPersistence = $abstractDataPersistence;
        $this->eventManager = $eventManager;
    }

    /**
     * Set receipt Id in Payment Journal
     *
     * @param string $requestString
     * @param string $entityCode
     * @param string $erpCode
     * @return I95DevResponseInterface
     * @author Hrusikesh Manna
     */
    public function getResponse($requestString, $entityCode, $erpCode) //NOSONAR
    {
        try {
            $paymentJournal = $this->paymentJournalFactory->create()->load($requestString['sourceId']);
            $paymentJournal->setReceiptId($requestString['targetId']);
            $paymentJournal->setUpdatedDt($this->date->gmtDate());
            $paymentJournal->save();
            $paymentJournalResponseEvent = "erpconnect_forward_paymentjournalresponse";
            $this->eventManager->dispatch($paymentJournalResponseEvent, ['currentObject' => $requestString]);
            return $this->abstractDataPersistence->setResponse(
                Data::SUCCESS,
                __("Response send successfully"),
                $paymentJournal
            );
        } catch (LocalizedException $ex) {
            $this->logger->createLog(
                __METHOD__,
                $ex->getMessage(),
                LoggerInterface::I95EXC,
                'critical'
            );
            return $this->abstractDataPersistence->setResponse(
                Data::ERROR,
                __("Some error occurred in response sync -- " . $entityCode),
                null
            );
        }
    }
}

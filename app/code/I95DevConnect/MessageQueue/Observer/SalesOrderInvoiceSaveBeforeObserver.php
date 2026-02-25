<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 * @createdBy vinayakrao.shetkar
 */

namespace I95DevConnect\MessageQueue\Observer;

use I95DevConnect\MessageQueue\Helper\Data;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Observer class for sales order invoice before save
 */
class SalesOrderInvoiceSaveBeforeObserver implements ObserverInterface
{
    /**
     * @var Data
     */
    public $baseHelperData;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * SalesOrderInvoiceSaveBeforeObserver constructor.
     *
     * @param Data $baseHelperData
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        Data $baseHelperData,
        TimezoneInterface $timezone
    ) {
        $this->baseHelperData = $baseHelperData;
        $this->timezone = $timezone;
    }

    /**
     * Save custom invoice
     *
     * @param Observer $observer
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        try {
            $erp_invoice_date = $this->baseHelperData->getGlobalValue('invoice_date');

            if ($erp_invoice_date !== null) {
                $invoice = $observer->getEvent()->getInvoice();
                $erp_invoice_date_correct = $this->timezone->convertConfigTimeToUtc($erp_invoice_date);
                $invoice->setCreatedAt($erp_invoice_date_correct);
                $this->baseHelperData->unsetGlobalValue('invoice_date');
            }
        } catch (LocalizedException $e) {
            $this->baseHelperData->unsetGlobalValue('invoice_date');
            throw new LocalizedException(__("invoice_not_synced"));
        }
    }
}

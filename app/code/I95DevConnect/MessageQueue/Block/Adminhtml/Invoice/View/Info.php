<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Block\Adminhtml\Invoice\View;

use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\SalesInvoice;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\Shipment;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Block to retrieve Invoice info
 * @api
 */
class Info extends Template
{
    public const I95EXC = 'i95devApiException';

    /**
     * @var customSalesInvoice
     */
    public $customSalesInvoice;

    /**
     * @var Registry
     */
    public $coreRegistry;

    /**
     * @var Data
     */
    public $basedata;
    /**
     * @var salesShipment
     */
    public $salesShipment;

    /**
     *
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var Data
     */
    public $mqHelper;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     *
     * @param Context $context
     * @param Registry $registry
     * @param SalesInvoice $customSalesInvoice
     * @param Shipment $salesShipment
     * @param LoggerInterface $logger
     * @param Data $mqHelper
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct( // NOSONAR
        Context $context,
        Registry $registry,
        SalesInvoice $customSalesInvoice,
        Shipment $salesShipment,
        LoggerInterface $logger,
        Data $mqHelper,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->customSalesInvoice = $customSalesInvoice;
        $this->salesShipment = $salesShipment;
        $this->logger = $logger;
        $this->mqHelper = $mqHelper;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * Retrieves AX Payment Journal Ids from Invoice
     *
     * @return string
     */
    public function getTargetPaymentIds()
    {
        $targetPaymentIds = '';
        try {
            $customInvoice = $this->getCustomInvoice();

            if (isset($customInvoice[0]) && isset($customInvoice[0]['cash_receipt_number'])) {
                $targetPaymentIds = $customInvoice[0]['cash_receipt_number'];
            }
        } catch (LocalizedException $ex) {
            $this->logger->createLog(__METHOD__, $ex->getMessage(), self::I95EXC, 'critical');
        }
        return $targetPaymentIds;
    }

    /**
     * Retrive invoice information from custom collection
     *
     * @return SalesInvoice $customSalesInvoice
     */
    public function getCustomInvoice()
    {
        $invoice = $this->getCurrentInvoice();
        $sourceInvoiceId = $invoice->getIncrementId();
        $customSalesInvoiceColl = $this->customSalesInvoice->getCollection();

        $customSalesInvoiceColl->addFieldToFilter('source_invoice_id', $sourceInvoiceId);

        $customSalesInvoiceColl->getSelect()->limit(1);

        return $customSalesInvoiceColl->getData();
    }

    /**
     * Retrieve current invoice
     *
     * @return string|null
     */
    public function getCurrentInvoice()
    {
        return $this->coreRegistry->registry('current_invoice');
    }

    /**
     * To get Current Component
     *
     * @return string
     */
    public function getComponent()
    {
        return $this->mqHelper->getscopeConfig(
            'i95dev_messagequeue/I95DevConnect_settings/component',
            ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getDefaultStoreView()->getWebsiteId()
        );
    }

    /**
     * To check module is enable/disable
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->mqHelper->isEnabledInAnyWebsite();
    }

    /**
     * Retrieves target invoice id
     *
     * @return string
     */
    public function getTargetInvoiceId()
    {
        $targetInvoiceId = '';
        try {
            $customInvoice = $this->getCustomInvoice();

            if (isset($customInvoice[0]) && isset($customInvoice[0]['target_invoice_id'])) {
                $targetInvoiceId = $customInvoice[0]['target_invoice_id'];
            }
        } catch (LocalizedException $ex) {
            $this->logger->createLog(__METHOD__, $ex->getMessage(), self::I95EXC, 'critical');
        }
        return $targetInvoiceId;
    }
}

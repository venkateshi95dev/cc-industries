<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Block\Adminhtml\Shipment\View;

use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\SalesShipment;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\Invoice;

/**
 * Block for displaying target information in shipment view page
 * @api
 */
class Info extends Template
{
    public const TARGET_SHIPMENT_ID = 'target_shipment_id';

    /**
     * @var customSalesShipment
     */
    public $customSalesShipment;

    /**
     * @var Registry
     */
    public $coreRegistry;

    /**
     * @var Data
     */
    public $basedata;

    /**
     * @var salesInvoice
     */
    public $salesInvoice;

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var Data
     */
    public $mqHelper;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param SalesShipment $customSalesShipment
     * @param Invoice $salesInvoice
     * @param LoggerInterface $logger
     * @param Data $mqHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        SalesShipment $customSalesShipment,
        Invoice $salesInvoice,
        LoggerInterface $logger,
        Data $mqHelper,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->customSalesShipment = $customSalesShipment;
        $this->salesInvoice = $salesInvoice;
        $this->logger = $logger;
        $this->mqHelper = $mqHelper;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current shipment
     *
     * @return string|null
     */
    public function getCurrentShipment()
    {
        return $this->coreRegistry->registry('current_shipment');
    }

    /**
     * Retrieve shipment information from custom collection
     *
     * @return string
     */
    public function getCustomShipment()
    {

        $shipment = $this->getCurrentShipment();
        $sourceShipmentId = $shipment->getIncrementId();
        $this->customSalesShipment = $this->customSalesShipment->getCollection();
        $this->customSalesShipment->addFieldToSelect(self::TARGET_SHIPMENT_ID)
            ->addFieldToFilter('source_shipment_id', $sourceShipmentId);

        $this->customSalesShipment->getSelect()->limit(1);

        return $this->customSalesShipment->getData();
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
    public function getTargetShipmentId()
    {
        $targetShipmentId = '';
        try {
            $customShipment = $this->getCustomShipment();

            if (isset($customShipment[0]) && isset($customShipment[0][self::TARGET_SHIPMENT_ID])) {
                $targetShipmentId = $customShipment[0][self::TARGET_SHIPMENT_ID];
            }
        } catch (LocalizedException $ex) {
            $this->mqHelper->criticalLog(__METHOD__, $ex->getMessage(), "i95devException");
        }
        return $targetShipmentId;
    }
}

<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_CancelOrder
 */

namespace I95DevConnect\CancelOrder\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;

/**
 * Returns helper
 */
class Data extends AbstractHelper
{
    public const XML_PATH_ENABLED = 'i95devconnect_CancelOrder/cancelorder_enabled_settings/enable_cancelorder';
    public const I95EXC = 'i95devApiException';
    public const TARGET_SHIPMENT_IDS = "targetShipmentIds";
    public const TARGET_INVOICE_IDS = "targetInvoiceIds";
    public const TRANSTYPE = 'transaction_type';

    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->scopeConfig = $context->getScopeConfig();
    }

    /**
     * Check if enabled
     *
     * @return string|null
     */
    public function isEnabled($websiteId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    /**
     * Validate partial data
     *
     * @param  object $cancelOrder
     * @throws LocalizedException
     */
    public function validatePartialData($cancelOrder)
    {
        $this->validateShipmentData($cancelOrder);
        $this->validateInvoiceData($cancelOrder);
    }

    /**
     * Validate shipment data
     *
     * @param  object $cancelOrder
     * @throws LocalizedException
     */
    public function validateShipmentData($cancelOrder)
    {
        $cancelledshippedIds = [];
        if (isset($cancelOrder->stringData[self::TARGET_SHIPMENT_IDS])
            && $cancelOrder->stringData[self::TARGET_SHIPMENT_IDS] != ""
        ) {
            $cancelledshippedIds = explode(",", $cancelOrder->stringData[self::TARGET_SHIPMENT_IDS]);
        }
        if (count($cancelledshippedIds) > 0) {
            $shipmentIds = $cancelOrder->getShipmentIds($cancelOrder->order);
            $remainingShipIds = array_diff($shipmentIds, $cancelledshippedIds);
            $missingMagentoShipIds = array_diff($cancelledshippedIds, $shipmentIds);
            $str = "Missing shipmentIds for partial cancellation - ";
            if (count($remainingShipIds)) {
                $message = $str . implode(",", $remainingShipIds);
                throw new LocalizedException(__($message));
            }
            if (count($shipmentIds) === 0) {
                $message = $str . implode(",", $cancelledshippedIds);
                throw new LocalizedException(__($message));
            }
            if (count($shipmentIds) && count($missingMagentoShipIds)) {
                $message = $str . implode(",", $missingMagentoShipIds);
                throw new LocalizedException(__($message));
            }
        }
    }

    /**
     * Validate invoice data
     *
     * @param  object $cancelOrder
     * @throws LocalizedException
     */
    public function validateInvoiceData($cancelOrder)
    {
        $cancelledInvoiceIds = [];
        if (isset($cancelOrder->stringData[self::TARGET_INVOICE_IDS])
            && $cancelOrder->stringData[self::TARGET_INVOICE_IDS] != ""
        ) {
            $cancelledInvoiceIds = explode(",", $cancelOrder->stringData[self::TARGET_INVOICE_IDS]);
        }
        if (count($cancelledInvoiceIds) > 0) {
            $invoiceIds = $cancelOrder->getInvoiceIds($cancelOrder->order);
            $paymentDetails = $cancelOrder->order->getPayment();
            $transactionDetails = $paymentDetails->getAdditionalInformation();
            $transactionType = '';
            if (isset($transactionDetails[self::TRANSTYPE])) {
                $transactionType = $transactionDetails[self::TRANSTYPE];
            }
            if ($transactionType === 'auth_capture') {
                $invoiceIds = explode(",", $invoiceIds[0]);
            }
            $remaininginvoiceIds = array_diff($invoiceIds, $cancelledInvoiceIds);
            $missingMagentoInvoiceIds = array_diff($cancelledInvoiceIds, $invoiceIds);
            $str = "Missing InvoiceIds for partial cancellation - ";
            if (count($remaininginvoiceIds)) {
                $message = $str . implode(",", $remaininginvoiceIds);
                throw new LocalizedException(__($message));
            }
            if (empty($invoiceIds)) {
                $message = $str . implode(",", $cancelledInvoiceIds);
                throw new LocalizedException(__($message));
            }
            if (count($invoiceIds) && count($missingMagentoInvoiceIds)) {
                $message = $str . implode(",", $missingMagentoInvoiceIds);
                throw new LocalizedException(__($message));
            }
        }
    }
}

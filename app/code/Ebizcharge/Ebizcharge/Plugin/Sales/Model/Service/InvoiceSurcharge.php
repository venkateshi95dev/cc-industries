<?php
/**
 * Century Business Solutions
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the centurybizsolutions.com license that is
 * available through the URL:  https://www.centurybizsolutions/License.txt
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file to upgrade this extension to newer
 * version in the future please contact to CENTURY BUSINESS SOLUTIONS.
 *
 * @category    Ebizcharge
 * @package     Ebizcharge_Ebizcharge
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Plugin\Sales\Model\Service;

use Ebizcharge\Ebizcharge\Model\AbstractModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Service\InvoiceService;

/**
 * After plugin on preparing invoice
 *
 * Class InvoiceSurcharge
 */
class InvoiceSurcharge extends AbstractModel
{

    /**
     * @param InvoiceService $subject
     * @param Invoice $resultInvoice
     * @param Order $order
     * @param array $orderItemsQtyToInvoice
     * @return Invoice
     * @throws LocalizedException
     */
    public function afterPrepareInvoice(
        InvoiceService $subject,
        Invoice        $resultInvoice,
        Order          $order,
        array          $orderItemsQtyToInvoice = []
    ): Invoice
    {
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);
        $surchargeSettings = $this->customerFactory->create()->getSurchargeSettings($order->getStoreId());
        $surchargeEnabled = isset($surchargeSettings["surchargeEnabled"]) ? (float)$surchargeSettings["surchargeEnabled"] : false;
        $surchargePercentage = isset($surchargeSettings["surchargePercentage"]) ? (float)$surchargeSettings["surchargePercentage"] : 0;

        if($isEbizChargeActive) {
            /**
             * if order ID then add surcharge in invoice
             */
            if ($order->getId()) {
                /**
                 * if order qty need to be updated.
                 */
                /** @var  $orderedItems */
                $orderedItems = $order->getAllVisibleItems() ?? [];
                $surchargePercentage = (float)($order->getEcSurchargePercentage() ?? 0);
                $surchargeAmount = (float)($order->getEcSurchargePercentage() ?? 0);

                if (count($orderedItems) > 0) {
                    foreach ($orderedItems as $orderItem) {
                        $orderItemId = $orderItem->getItemId() ?? "";
                        $rowTotalInclTax = (float)($orderItem->getRowTotalInclTax() ?? 0);
                        $orderItem->setQtyToInvoiced(0);

                        if ($orderItem && isset($orderItemsQtyToInvoice[$orderItemId]) && array_key_exists($orderItemId, $orderItemsQtyToInvoice)) {
                            $qtyToInvoiced = (float)($orderItemsQtyToInvoice[$orderItemId] ?? 0);
                            if ($qtyToInvoiced > 0) {
                                $surchargeItemAmount = 0;
                                if ($surchargeAmount > 0) {
                                    $surchargeItemAmount = ($surchargePercentage * $rowTotalInclTax) / 100;
                                }
                                $orderItem->setQtyToInvoiced($qtyToInvoiced)
                                    ->setEcSurchargeAmount($surchargeItemAmount);
                            }
                        }
                        $orderItem->save();
                    }
                }
            }
            if ($surchargePercentage > 0 && $surchargeEnabled) {
                $baseGrandTotal = (float)($resultInvoice->getBaseGrandTotal() ?? 0);
                $grandTotal = (float)($resultInvoice->getGrandTotal() ?? 0);
                $surchargeInvoicedAmount = floatval($surchargePercentage * $baseGrandTotal) / 100;
                $surchargeBaseGrandTotalInvoicedAmount = floatval($baseGrandTotal) + $surchargeInvoicedAmount;
                $surchargeGrandTotalInvoicedAmount = floatval($grandTotal) + $surchargeInvoicedAmount;
                $resultInvoice->setEcSurchargePercentage($surchargePercentage);
                $resultInvoice->setEcSurchargeAmount($surchargeInvoicedAmount);
                $resultInvoice->setBaseGrandTotal($surchargeBaseGrandTotalInvoicedAmount);
                $resultInvoice->setGrandTotal($surchargeGrandTotalInvoicedAmount);
            }
        }

        /** @var Invoice $resultInvoice */
        return $resultInvoice;
    }

    /**
     * Check is Surcharge Already Included
     *
     * @param $invoiceCollection
     * @return bool
     */
    public function checkSurchargeAlreadyIncluded($invoiceCollection = null): bool
    {
        $surchargeIncluded = false;
        /** @var \Ebizcharge\Ebizcharge\Model\Order\Invoice $invoice */
        foreach ($invoiceCollection as $invoice) {
            if (floatval($invoice->getEcSurchargePercentage()) > 0 && floatval($invoice->getEcSurchargeAmount()) > 0) {
                $surchargeIncluded = true;
            }
        }

        return $surchargeIncluded;
    }
}

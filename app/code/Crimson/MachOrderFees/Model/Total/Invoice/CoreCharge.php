<?php
/**
 * @namespace   Crimson
 * @module      MacOrderFees
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/12/2019 12:39 PM
 * @brief
 */

namespace Crimson\MachOrderFees\Model\Total\Invoice;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

/**
 * Class CoreCharge
 * @package Crimson\MachOrderFees\Model\Total\Invoice
 */
class CoreCharge extends AbstractTotal
{

    /**
     * @param Invoice $invoice
     * @return $this
     */
    public function collect(Invoice $invoice): CoreCharge
    {
        $invoice->setCoreChargeAmount(0);
        $invoice->setBaseCoreChargeAmount(0);
        $orderCoreChargeAmount        = $invoice->getOrder()->getCoreChargeAmount();
        $baseOrderCoreChargeAmount    = $invoice->getOrder()->getBaseCoreChargeAmount();
        if ($orderCoreChargeAmount) {
            /**
             * Check coreCharge amount in previous invoices
             */
            foreach ($invoice->getOrder()->getInvoiceCollection() as $previousInvoice) {
                if ($previousInvoice->getCoreChargeAmount() && !$previousInvoice->isCanceled()) {
                    return $this;
                }
            }
            $invoice->setCoreChargeAmount($orderCoreChargeAmount);
            $invoice->setBaseCoreChargeAmount($baseOrderCoreChargeAmount);

            $invoice->setGrandTotal($invoice->getGrandTotal()+$orderCoreChargeAmount);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal()+$baseOrderCoreChargeAmount);
        }

        return $this;
    }
}

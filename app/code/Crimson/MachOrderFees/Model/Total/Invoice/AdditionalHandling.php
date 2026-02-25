<?php
/**
 * @namespace   Crimson
 * @module      MacOrderFees
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/12/2019 12:42 PM
 * @brief
 */

namespace Crimson\MachOrderFees\Model\Total\Invoice;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

/**
 * Class AdditionalHandling
 * @package Crimson\MachOrderFees\Model\Total\Invoice
 */
class AdditionalHandling extends AbstractTotal
{

    /**
     * @param Invoice $invoice
     * @return $this|AdditionalHandling
     */
    public function collect(Invoice $invoice): AdditionalHandling
    {
        $invoice->setAdditionalHandlingAmount(0);
        $invoice->setBaseAdditionalHandlingAmount(0);
        $orderAdditionalHandlingAmount        = $invoice->getOrder()->getAdditionalHandlingAmount();
        $baseOrderAdditionalHandlingAmount    = $invoice->getOrder()->getBaseAdditionalHandlingAmount();
        if ($orderAdditionalHandlingAmount) {
            /**
             * Check additionalHandling amount in previus invoices
             */
            foreach ($invoice->getOrder()->getInvoiceCollection() as $previousInvoice) {
                if ($previousInvoice->getAdditionalHandlingAmount() && !$previousInvoice->isCanceled()) {
                    return $this;
                }
            }
            $invoice->setAdditionalHandlingAmount($orderAdditionalHandlingAmount);
            $invoice->setBaseAdditionalHandlingAmount($baseOrderAdditionalHandlingAmount);

            $invoice->setGrandTotal($invoice->getGrandTotal()+$orderAdditionalHandlingAmount);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal()+$baseOrderAdditionalHandlingAmount);
        }

        return $this;
    }
}

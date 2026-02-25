<?php

namespace Crimson\MachTax\Model\Order\Invoice\Total;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\Tax as InvoiceTax;

/**
 * Class Tax
 * @package Crimson\MachTax\Model\Order\Invoice\Total
 */
class Tax extends InvoiceTax
{
    /**
     * Collect invoice tax amount
     * We collect from the Order, using Mach Taxes we don't know the Tax applied to each item, so we
     * never registered that info into Magento.
     *
     * Mach only returns the total Tax Amount applied to the Quote/Order, so Quote Items and Order Items
     * don't have taxes info.
     *
     * @param Invoice $invoice
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function collect(Invoice $invoice): Tax
    {
        $totalTax = 0;
        $baseTotalTax = 0;
        $totalDiscountTaxCompensation = 0;
        $baseTotalDiscountTaxCompensation = 0;

        $order = $invoice->getOrder();

        $totalTax = $order->getTaxAmount() - $order->getTaxInvoiced();
        $baseTotalTax = $order->getBaseTaxAmount() - $order->getBaseTaxInvoiced();
        $totalDiscountTaxCompensation = $order->getDiscountTaxCompensationAmount()
            - $order->getDiscountTaxCompensationInvoiced();
        $baseTotalDiscountTaxCompensation = $order->getBaseDiscountTaxCompensationAmount()
            - $order->getBaseDiscountTaxCompensationInvoiced();

        if ($this->_canIncludeShipping($invoice)) {
            $totalTax += $order->getShippingTaxAmount();
            $baseTotalTax += $order->getBaseShippingTaxAmount();
            $totalDiscountTaxCompensation += $order->getShippingDiscountTaxCompensationAmount();
            $baseTotalDiscountTaxCompensation += $order->getBaseShippingDiscountTaxCompensationAmnt();
            $invoice->setShippingTaxAmount($order->getShippingTaxAmount());
            $invoice->setBaseShippingTaxAmount($order->getBaseShippingTaxAmount());
            $invoice->setShippingDiscountTaxCompensationAmount($order->getShippingDiscountTaxCompensationAmount());
            $invoice->setBaseShippingDiscountTaxCompensationAmnt($order->getBaseShippingDiscountTaxCompensationAmnt());
        }
        $allowedTax = $order->getTaxAmount() - $order->getTaxInvoiced();
        $allowedBaseTax = $order->getBaseTaxAmount() - $order->getBaseTaxInvoiced();
        $allowedDiscountTaxCompensation = $order->getDiscountTaxCompensationAmount() +
            $order->getShippingDiscountTaxCompensationAmount() -
            $order->getDiscountTaxCompensationInvoiced() -
            $order->getShippingDiscountTaxCompensationInvoiced();
        $allowedBaseDiscountTaxCompensation = $order->getBaseDiscountTaxCompensationAmount() +
            $order->getBaseShippingDiscountTaxCompensationAmnt() -
            $order->getBaseDiscountTaxCompensationInvoiced() -
            $order->getBaseShippingDiscountTaxCompensationInvoiced();

        if (!$invoice->isLast()) {
            $totalTax = $allowedTax;
            $baseTotalTax = $allowedBaseTax;
            $totalDiscountTaxCompensation = $allowedDiscountTaxCompensation;
            $baseTotalDiscountTaxCompensation = $allowedBaseDiscountTaxCompensation;
        } else {
            $totalTax = min($allowedTax, $totalTax);
            $baseTotalTax = min($allowedBaseTax, $baseTotalTax);
            $totalDiscountTaxCompensation = min($allowedDiscountTaxCompensation, $totalDiscountTaxCompensation);
            $baseTotalDiscountTaxCompensation = min(
                $allowedBaseDiscountTaxCompensation,
                $baseTotalDiscountTaxCompensation
            );
        }

        $invoice->setTaxAmount($totalTax);
        $invoice->setBaseTaxAmount($baseTotalTax);
        $invoice->setDiscountTaxCompensationAmount($totalDiscountTaxCompensation);
        $invoice->setBaseDiscountTaxCompensationAmount($baseTotalDiscountTaxCompensation);

        $invoice->setGrandTotal($invoice->getGrandTotal() + $totalTax + $totalDiscountTaxCompensation);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseTotalTax + $baseTotalDiscountTaxCompensation);

        return $this;
    }
}

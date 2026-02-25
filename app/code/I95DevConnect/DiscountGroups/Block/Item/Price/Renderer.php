<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Block\Item\Price;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Order\CreditMemo\Item as CreditMemoItem;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Weee\Model\Tax as WeeeDisplayConfig;

/**
 * Item price render block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Renderer extends \Magento\Weee\Block\Item\Price\Renderer
{
    /**
     * @var \Magento\Weee\Helper\Data
     */
    protected $weeeHelper;

    /**
     * Return the total amount minus discount
     *
     * @param OrderItem|InvoiceItem|CreditMemoItem $item
     * @return mixed
     */
    public function getTotalAmount($item)
    {
        return $item->getRowTotal()
            - $item->getDiscountAmount()
            + $item->getTaxAmount()
            + $item->getDiscountTaxCompensationAmount()
            + $this->weeeHelper->getRowWeeeTaxInclTax($item) + $item->getDiscountGroupAmount();
    }

    /**
     * Return the total amount minus discount
     *
     * @param OrderItem|InvoiceItem|CreditMemoItem $item
     * @return mixed
     */
    public function getBaseTotalAmount($item)
    {
        return $item->getBaseRowTotal()
            - $item->getBaseDiscountAmount()
            + $item->getBaseTaxAmount()
            + $item->getBaseDiscountTaxCompensationAmount()
            + $this->weeeHelper->getBaseRowWeeeTaxInclTax($item)
                + $item->getBaseDiscountGroupAmount();
    }

    /**
     * Get item row total
     *
     * @param OrderItem|InvoiceItem|CreditMemoItem $item
     * @return float|int
     */
    public function getRowTotal($item)
    {
        $itemRowTotal = $item->getRowTotal();
        $discount = $this->getDiscountAmount($item);
        return $itemRowTotal - ($itemRowTotal * $discount) / 100;
    }
}

<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Plugin;

use Closure;
use I95DevConnect\DiscountGroups\Helper\Data;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;

class TaxAmount
{
    /**
     * @var Data
     */
    public $helper;

    /**
     * TaxAmount constructor.
     *
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Around plugin for mapItem function
     *
     * @param CommonTaxCollector $subject
     * @param Closure $proceed
     * @param QuoteDetailsItemInterfaceFactory $itemDataObjectFactory
     * @param AbstractItem $item
     * @param bool $priceIncludesTax
     * @param bool $useBaseCurrency
     * @param string $parentCode
     * @return mixed
     */
    public function aroundMapItem(
        CommonTaxCollector $subject, //NOSONAR
        Closure $proceed,
        QuoteDetailsItemInterfaceFactory $itemDataObjectFactory,
        AbstractItem $item,
        $priceIncludesTax,
        $useBaseCurrency,
        $parentCode = null
    ) {
        $isEnabled = $this->helper->isDiscountGroupsEnabled();
        if (!$isEnabled) {
            return $proceed($itemDataObjectFactory, $item, $priceIncludesTax, $useBaseCurrency, $parentCode);
        }

        $returnValue = $proceed($itemDataObjectFactory, $item, $priceIncludesTax, $useBaseCurrency, $parentCode);
        $returnValue->setSku($item->getSku());
        $returnValue->setDiscountGroupAmount($item->getDiscountGroupAmount());
        $returnValue->setBaseDiscountGroupAmount($item->getBaseDiscountGroupAmount());
        return $returnValue;
    }
}

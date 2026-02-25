<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Model\Quote\Address\Total;

use Exception;
use I95DevConnect\DiscountGroups\Model\ApplyDiscount;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total as MTotal;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use I95DevConnect\DiscountGroups\Helper\Data;

class Discountgroup extends AbstractTotal
{
    /**
     * @var null
     */
    protected $customerId = null;

    /**
     * @var ApplyDiscount
     */
    protected $discCaluculation;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Discountgroup constructor
     *
     * @param ApplyDiscount $discCaluculation
     */
    public function __construct(
        ApplyDiscount $discCaluculation,
        Data $helper
    ) {
        $this->discCaluculation = $discCaluculation;
        $this->helper = $helper;
        $this->setCode('discount_group');
    }

    /**
     * Set discount group amount to quote totals
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param MTotal $total
     * @return $this
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        MTotal $total
    ) {
        if (!$this->helper->isDiscountGroupsEnabled()) {
            return $this;
        }

        parent::collect($quote, $shippingAssignment, $total);
        $address = $shippingAssignment->getShipping()->getAddress();
        $this->customerId = $address->getCustomerId();
        $discountAmount = 0;
        $baseDiscountAmount = 0;
        $items = $shippingAssignment->getItems();

        foreach ($items as $item) {
            $result = $this->_applyDiscountGroup($item);
            if (!empty($result)) {
                $baseDiscountAmount += $result['baseDiscountAmount'];
                $discountAmount += $result['discountAmount'];
            }
        }

        $total->setDiscountGroupAmount(-$discountAmount);
        $total->setBaseDiscountGroupAmount(-$baseDiscountAmount);

        $total->addTotalAmount($this->getCode(), -$discountAmount);
        $total->addBaseTotalAmount($this->getCode(), -$baseDiscountAmount);

        return $this;
    }

    /**
     * Set discount group amount to quote items
     *
     * @param item $item
     * @return array
     */
    protected function _applyDiscountGroup($item)  // phpcs:ignore
    {
        try {
            $discountGroup = $this->discCaluculation->getDiscountPercentage($item, $this->customerId);
            $baseCustomerDiscountAmount = $discountGroup['baseDiscountAmount'];
            $discountGroupAmount = $discountGroup['discountAmount'];

            $item->setBaseDiscountGroupAmount(-$baseCustomerDiscountAmount);
            $item->setDiscountGroupAmount(-$discountGroupAmount);
        } catch (Exception $ex) {
            return [];
        }
        return ["baseDiscountAmount" => $baseCustomerDiscountAmount,"discountAmount" => $discountGroupAmount];
    }

    /**
     * Assign subtotal amount and label to address object
     *
     * @param Quote $quote
     * @param MTotal $total
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(Quote $quote, MTotal $total)
    {
        $result = null;
        $amount = $total->getDiscountGroupAmount();

        if ($amount != 0) {
            $result = [
                'code' => $this->getCode(),
                'title' => $this->getLabel(),
                'value' => $amount
            ];
        }
        return $result;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return __('Discount Group Amount');
    }
}

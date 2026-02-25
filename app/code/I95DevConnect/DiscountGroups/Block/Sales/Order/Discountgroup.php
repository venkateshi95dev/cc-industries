<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Block\Sales\Order;

use I95DevConnect\DiscountGroups\Helper\Data;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;

/**
 * Discount block for order
 *
 */
class Discountgroup extends Template
{
    /**
     * @var bool
     */
    protected $_isScopePrivate;// phpcs:ignore

    /**
     * @param Context $context
     * @param Data $discountGroupsHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $discountGroupsHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $isEnabled = $discountGroupsHelper->isDiscountGroupsEnabled();
        if (!$isEnabled) {
            return;
        }
        $this->_isScopePrivate = true;
    }

    /**
     * Retrieve current order model instance
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * Get Source
     *
     * @return mixed
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * Initialize customer balance order total
     *
     * @return $this
     */
    public function initTotals()
    {
        if ((double) $this->getSource()->getDiscountGroupAmount() == 0) {
            return $this;
        }
        $total = new DataObject(
            [
                'code' => 'discountgroup',
                'strong' => false,
                'label' => __('Discount Group Amount'),
                'value' => $this->getSource()->getDiscountGroupAmount(),
            ]
        );
        $this->getParentBlock()->addTotal($total);
        return $this;
    }

    /**
     * Get label properties
     *
     * @return string
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * Get value properties
     *
     * @return string
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }
}

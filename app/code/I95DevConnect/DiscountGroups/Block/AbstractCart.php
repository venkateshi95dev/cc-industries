<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Block;

use I95DevConnect\DiscountGroups\Helper\Data;
use Magento\Framework\View\Element\Template;

class AbstractCart
{
    /**
     * @var Data
     */
    public $helper;

    /**
     * AbstractCart constructor.
     *
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * After plugin for getItemRenderer function
     *
     * @param \Magento\Checkout\Block\Cart\AbstractCart $subject
     * @param Template $result
     * @return mixed
     */
    public function afterGetItemRenderer(\Magento\Checkout\Block\Cart\AbstractCart $subject, $result) //NOSONAR
    {
        $isEnabled = $this->helper->isDiscountGroupsEnabled();
        if ($isEnabled) {
            $result->setTemplate('I95DevConnect_DiscountGroups::cart/item/default.phtml');
            return $result;
        } else {
            $result->setTemplate('Magento_Checkout::cart/item/default.phtml');
            return $result;
        }
    }
}

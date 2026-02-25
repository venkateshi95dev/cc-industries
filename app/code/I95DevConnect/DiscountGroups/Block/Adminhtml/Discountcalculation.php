<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Discountcalculation extends Container
{
    /**
     * @var string
     */
    protected $_controller;// phpcs:ignore
    /**
     * @var string
     */
    protected $_blockGroup;// phpcs:ignore
    protected $_headerText;// phpcs:ignore

    /**
     * Discountcalculation constructor
     *
     * @return void
     */
    protected function _construct() // phpcs:ignore
    {
        $this->_controller = 'adminhtml_discountcalculation';
        $this->_blockGroup = 'I95DevConnect_DiscountGroups';
        $this->_headerText = __('I95Dev Discount Prices');
        parent::_construct();
        $this->removeButton('add');
    }
}

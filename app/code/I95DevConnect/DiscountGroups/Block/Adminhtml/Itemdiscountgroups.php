<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

/*Class to remove add buttion from Item Discount Page*/
class Itemdiscountgroups extends Container
{
    /**
     * Item discount groups constructor
     *
     * @return void
     */
    protected function _construct() // phpcs:ignore
    {
        $this->_controller = 'adminhtml_itemdiscountgroups';
        $this->_blockGroup = 'I95DevConnect_DiscountGroups';
        $this->_headerText = __('I95Dev Item Discount Groups');
        parent::_construct();
        $this->removeButton('add');
    }
}

<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

/**
 * Price Level Grid
 */
class PriceLevel extends Container
{
    /**
     * Class constructor to include all the dependencies
     *
     * @return void
     */
    // @codingStandardsIgnoreStart
    protected function _construct()
    {
        $this->_controller = 'adminhtml_priceLevel';
        $this->_blockGroup = 'I95DevConnect_PriceLevel';
        $this->_headerText = __('Custom Grid');
        parent::_construct();
        $this->removeButton('add');
    }
    // @codingStandardsIgnoreEnd
}

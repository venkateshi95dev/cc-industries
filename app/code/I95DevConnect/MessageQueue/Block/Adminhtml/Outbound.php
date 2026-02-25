<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

/**
 * Outbound Message Queue Grid class
 */
class Outbound extends Container
{
    /**
     * Outbound messagequeue constructor
     */
    protected function _construct()// phpcs:ignore
    {
        $this->_controller = 'Adminhtml_Outbound';
        $this->_blockGroup = 'I95DevConnect_MessageQueue';
        $this->_headerText = __('Custom Grid');
        parent::_construct();
        $this->removeButton('add');
    }
}

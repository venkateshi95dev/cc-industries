<?php

namespace Silk\Shipping\Block\Adminhtml;

class Methods extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'mappingshipping_index';
        $this->_headerText = __('Manage Shipping Methods');
        $this->_addButtonLabel = __('Add New Shipping Method Mapping');
        parent::_construct();
    }
}

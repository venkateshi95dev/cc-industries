<?php

namespace Cokertire\Distributors\Block\Adminhtml;

class Distributors extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_distributors';
        $this->_blockGroup = 'Cokertire_Distributors';
        $this->_headerText = __('Distributors');
        $this->_addButtonLabel = __('Create New Distributors');
        parent::_construct();
    }
}

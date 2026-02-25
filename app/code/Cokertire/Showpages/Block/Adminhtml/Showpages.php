<?php
namespace Cokertire\Showpages\Block\Adminhtml;

class Showpages extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_showpages';
        $this->_blockGroup = 'Cokertire_Showpages';
        $this->_headerText = __('Showpages');
        $this->_addButtonLabel = __('Create New Showpages');
        parent::_construct();
    }
}

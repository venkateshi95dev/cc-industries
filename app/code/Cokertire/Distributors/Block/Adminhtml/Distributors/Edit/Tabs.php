<?php

namespace Cokertire\Distributors\Block\Adminhtml\Distributors\Edit;

/**
 * @method Tabs setTitle(\string $title)
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('distributors_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Distributors Information'));
    }
}

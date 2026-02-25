<?php

namespace Cokertire\Disclaimers\Block\Adminhtml\Disclaimers\Edit;

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
        $this->setId('disclaimers_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Disclaimers Information'));
    }
}

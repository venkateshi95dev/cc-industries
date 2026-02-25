<?php

namespace Cokertire\Showpages\Block\Adminhtml\Showpages\Edit;

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
        $this->setId('Showpages_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Showpages Information'));
    }
}

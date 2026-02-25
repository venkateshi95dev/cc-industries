<?php

namespace Cokertire\Magazine\Block\Adminhtml\Magazine\Edit;

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
        $this->setId('magazine_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Magazine Information'));
    }
}

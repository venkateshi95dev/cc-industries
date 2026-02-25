<?php

namespace Silk\Shipping\Controller\Adminhtml\Index;

class Index extends \Silk\Shipping\Controller\Adminhtml\Index
{
    /**
     * THN Shipping Methods list action
     *
     * @return void
     */
    public function execute()
    {
        if ($this->getRequest()->getParam('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->_view->loadLayout();
        $this->_setActiveMenu('Silk_Shipping::manage');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Manage Shipping Methods'));
        $this->_addBreadcrumb(__('Silk'), __('Silk'));
        $this->_addBreadcrumb(__('Manage'), __('Manage'));
        $this->_addBreadcrumb(__('Manage THN Shipping Methods'), __('Manage Shipping Methods'));

        $this->_view->renderLayout();
    }
}

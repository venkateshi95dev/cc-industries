<?php

namespace Crimson\ProductAlert\Controller\Adminhtml\BackInStockAlert;

class Index extends \Crimson\ProductAlert\Controller\Adminhtml\BackInStockAlert
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Crimson_ProductAlert::back_in_stock_alert');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Back In Stock Alerts'));
        $this->_view->renderLayout();
    }
}

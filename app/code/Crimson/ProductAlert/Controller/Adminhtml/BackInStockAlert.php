<?php

namespace Crimson\ProductAlert\Controller\Adminhtml;

abstract class BackInStockAlert extends \Magento\Backend\App\Action
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Crimson_ProductAlert::back_in_stock_alert');
    }
}

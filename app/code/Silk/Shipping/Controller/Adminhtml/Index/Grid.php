<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Silk\Shipping\Controller\Adminhtml\Index;

class Grid extends \Silk\Shipping\Controller\Adminhtml\Index
{
    /**
     * Backup list action
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}

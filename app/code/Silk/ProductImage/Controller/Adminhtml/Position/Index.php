<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Silk\ProductImage\Controller\Adminhtml\Position;

use Magento\Framework\Exception\NotFoundException;

class Index extends \Silk\ProductImage\Controller\Adminhtml\Position
{
    /**
     * Index action
     *
     * @return void
     * @throws NotFoundException
     */
    public function execute()
    {
        throw new NotFoundException(__('Page not found.'));
    }
}

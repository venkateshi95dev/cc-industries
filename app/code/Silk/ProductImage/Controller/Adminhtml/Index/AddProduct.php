<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Silk\ProductImage\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Backend\App\Action\Context;
use \Magento\Framework\View\Result\LayoutFactory;

class AddProduct extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Silk_ProductImage::document';

    /**
     * @var LayoutFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param LayoutFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        LayoutFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Layout $resultPage */
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }
}

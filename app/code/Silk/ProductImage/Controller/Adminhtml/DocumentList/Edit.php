<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Silk\ProductImage\Controller\Adminhtml\DocumentList;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Backend\App\Action;

/**
 * Edit CMS document action.
 */
class Edit extends \Silk\ProductImage\Controller\Adminhtml\DocumentList implements HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \Silk\ProductImage\Model\Position\Cache
     */
    protected $cache;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Silk\ProductImage\Model\Position\Cache $cache,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
         $this->cache = $cache;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultDocument = $this->resultPageFactory->create();
        $resultDocument->setActiveMenu('Silk_ProductImage::img_document')
            ->addBreadcrumb(__('CMS'), __('CMS'))
            ->addBreadcrumb(__('Manage Images'), __('Manage Images'));
        return $resultDocument;
    }

    /**
     * Edit CMS document
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $this->_coreRegistry->register(
            \Silk\ProductImage\Model\Position\Cache::POSITION_CACHE_KEY,
            uniqid()
        );
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('document_id');
        $model = $this->_objectManager->create(\Silk\ProductImage\Model\Document::class);

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This document no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->_coreRegistry->register('img_document', $model);

        // 5. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Document $resultDocument */
        $resultDocument = $this->_initAction();
        $resultDocument->addBreadcrumb(
            $id ? __('Edit Image') : __('New Image'),
            $id ? __('Edit Image') : __('New Image')
        );
        $resultDocument->getConfig()->getTitle()->prepend(__('Images'));
        $resultDocument->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getTitle() : __('New Image'));

        return $resultDocument;
    }
}

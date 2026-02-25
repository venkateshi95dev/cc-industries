<?php
namespace Silk\CKDocument\Controller\Adminhtml\CKDocumentList;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Backend\App\Action;

/**
 * Edit CMS document action.
 */
class Edit extends \Silk\CKDocument\Controller\Adminhtml\CKDocumentList implements HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
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
        $resultCKDocument = $this->resultPageFactory->create();
        $resultCKDocument->setActiveMenu('Silk_CKDocument::cms_document')
            ->addBreadcrumb(__('CMS'), __('CMS'))
            ->addBreadcrumb(__('Manage Documents'), __('Manage Documents'));
        return $resultCKDocument;
    }

    /**
     * Edit CMS document
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('document_id');
        $model = $this->_objectManager->create(\Silk\CKDocument\Model\CKDocument::class);

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

        $this->_coreRegistry->register('cms_document', $model);

        // 5. Build edit form
        /** @var \Magento\Backend\Model\View\Result\CKDocument $resultCKDocument */
        $resultCKDocument = $this->_initAction();
        $resultCKDocument->addBreadcrumb(
            $id ? __('Edit Document') : __('New Document'),
            $id ? __('Edit Document') : __('New Document')
        );
        $resultCKDocument->getConfig()->getTitle()->prepend(__('Documents'));
        $resultCKDocument->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getTitle() : __('New Document'));

        return $resultCKDocument;
    }
}

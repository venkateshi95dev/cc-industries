<?php
namespace Silk\CKDocument\Controller\Adminhtml;

abstract class CKDocumentList extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Silk_CKDocument::document';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\Registry $coreRegistry)
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Init page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initCKDocument($resultPage)
    {
        $resultPage->setActiveMenu('Silk_CKDocument::cms_document')
            ->addBreadcrumb(__('CMS'), __('CMS'))
            ->addBreadcrumb(__('List'), __('Document List'));
        return $resultPage;
    }
}

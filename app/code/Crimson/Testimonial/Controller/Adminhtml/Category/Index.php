<?php

namespace Crimson\Testimonial\Controller\Adminhtml\Category;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class Index extends Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;


    /**
     * @param Context
     * @param \Magento\Framework\View\Result\PageFactory
     * @param \Magento\Framework\Registry
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry     = $registry;
        parent::__construct($context);
    }


    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Crimson_Testimonial::category');
    }


    public function execute()
    {

        $resultPage = $this->resultPageFactory->create();

        /*
            * Set active menu item
         */
        $resultPage->setActiveMenu("Crimson_Testimonial::testimonial");
        $resultPage->getConfig()->getTitle()->prepend(__('Categories'));

        /*
            * Add breadcrumb item
         */
        $resultPage->addBreadcrumb(__('Crimson_Testimonial'), __('Categories'));
        $resultPage->addBreadcrumb(__('Manage Categories'), __('Manage Categories'));

        return $resultPage;
    }
}

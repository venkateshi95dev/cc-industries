<?php

namespace Cokertire\Showpages\Controller\Adminhtml\Showpages;

class Edit extends \Cokertire\Showpages\Controller\Adminhtml\Showpages
{
    /**
     * Backend session
     *
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * Page factory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * Result JSON factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * constructor
     *
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Cokertire\Showpages\Model\ShowpagesFactory $showpagesFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Cokertire\Showpages\Model\ShowpagesFactory $showpagesFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->_backendSession    = $backendSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        parent::__construct($showpagesFactory, $registry, $resultRedirectFactory, $context);
    }

    /**
     * is action allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Cokertire_Showpages::showpages');
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $showpages = $this->_initShowpages();
        /** @var \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Cokertire_Showpages::showpages');
        $resultPage->getConfig()->getTitle()->set(__('Showpages'));
        if ($id) {
            $showpages->load($id);
            if (!$showpages->getShowpagesId()) {
                $this->messageManager->addError(__('This Showpages no longer exists.'));
                $resultRedirect = $this->_resultRedirectFactory->create();
                $resultRedirect->setPath(
                    'showpages/showpages/edit',
                    [
                        'id' => $showpages->getShowpagesId(),
                        '_current' => true
                    ]
                );
                return $resultRedirect;
            }
        }
        $title = $showpages->getShowpagesId() ? $showpages->getShowSpace() : __('New Showpages');
        $resultPage->getConfig()->getTitle()->prepend($title);
        $data = $this->_backendSession->getData('coker_showpages', true);
        if (!empty($data)) {
            $showpages->setData($data);
        }
        return $resultPage;
    }
}

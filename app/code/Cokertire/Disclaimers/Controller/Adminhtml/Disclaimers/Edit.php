<?php
namespace Cokertire\Disclaimers\Controller\Adminhtml\Disclaimers;

class Edit extends \Cokertire\Disclaimers\Controller\Adminhtml\Disclaimers
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
     * @param \Cokertire\Disclaimers\Model\DisclaimersFactory $DisclaimersFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Cokertire\Disclaimers\Model\DisclaimersFactory $DisclaimersFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->_backendSession    = $backendSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        parent::__construct($DisclaimersFactory, $registry, $resultRedirectFactory, $context);
    }

    /**
     * is action allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Cokertire_Disclaimers::disclaimers');
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $disclaimers = $this->_initPost();
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Cokertire_Disclaimers::disclaimers');
        $resultPage->getConfig()->getTitle()->set(__('Disclaimers'));
        if ($id) {
            $disclaimers->load($id);
            if (!$disclaimers->getDisclaimersId()) {
                $this->messageManager->addError(__('This Disclaimers no longer exists.'));
                $resultRedirect = $this->_resultRedirectFactory->create();
                $resultRedirect->setPath(
                    'Cokertire_Disclaimers/*/edit',
                    [
                        'id' => $disclaimers->getDisclaimersId(),
                        '_current' => true
                    ]
                );
                return $resultRedirect;
            }
        }
        $title = $disclaimers->getDisclaimersId() ? __('Edit Disclaimers') : __('New Disclaimers');
        $resultPage->getConfig()->getTitle()->prepend($title);
        $data = $this->_backendSession->getData('cokertire_disclaimers', true);
        if (!empty($data)) {
            $disclaimers->setData($data);
        }
        return $resultPage;
    }
}

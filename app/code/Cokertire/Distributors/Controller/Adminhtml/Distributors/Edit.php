<?php
namespace Cokertire\Distributors\Controller\Adminhtml\Distributors;

class Edit extends \Cokertire\Distributors\Controller\Adminhtml\Distributors
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
     * @param \Cokertire\Distributors\Model\DistributorsFactory $distributorsFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Cokertire\Distributors\Model\DistributorsFactory $distributorsFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->_backendSession    = $backendSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        parent::__construct($distributorsFactory, $registry, $resultRedirectFactory, $context);
    }

    /**
     * is action allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Cokertire_Distributors::distributors');
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $distributors = $this->_initPost();
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Cokertire_Distributors::distributors');
        $resultPage->getConfig()->getTitle()->set(__('Distributors'));
        if ($id) {
            $distributors->load($id);
            if (!$distributors->getDistributorsid()) {
                $this->messageManager->addError(__('This Distributors no longer exists.'));
                $resultRedirect = $this->_resultRedirectFactory->create();
                $resultRedirect->setPath(
                    'Cokertire_Distributors/*/edit',
                    [
                        'id' => $distributors->getDistributorsid(),
                        '_current' => true
                    ]
                );
                return $resultRedirect;
            }
        }
        $title = $distributors->getDistributorsid() ? $distributors->getCompany() : __('New Distributors');
        $resultPage->getConfig()->getTitle()->prepend($title);
        $data = $this->_backendSession->getData('cokertire_distributors', true);
        if (!empty($data)) {
            $distributors->setData($data);
        }
        return $resultPage;
    }
}

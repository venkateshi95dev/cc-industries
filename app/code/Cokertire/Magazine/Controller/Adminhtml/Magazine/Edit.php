<?php
namespace Cokertire\Magazine\Controller\Adminhtml\Magazine;

class Edit extends \Cokertire\Magazine\Controller\Adminhtml\Magazine
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
     * @param \Cokertire\Magazine\Model\MagazineFactory $magazineFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Cokertire\Magazine\Model\MagazineFactory $magazineFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->_backendSession    = $backendSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        parent::__construct($magazineFactory, $registry, $resultRedirectFactory, $context);
    }

    /**
     * is action allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Cokertire_Magazine::magazine');
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $object = $this->_initPost();
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Cokertire_Magazine::magazine');
        $resultPage->getConfig()->getTitle()->set(__('Magazine'));
        if ($id) {
            $object->load($id);
            if (!$object->getMagazineId()) {
                $this->messageManager->addError(__('This Magazine no longer exists.'));
                $resultRedirect = $this->_resultRedirectFactory->create();
                $resultRedirect->setPath(
                    'Cokertire_Magazine/*/edit',
                    [
                        'id' => $object->getMagazineId(),
                        '_current' => true
                    ]
                );
                return $resultRedirect;
            }
        }
        $title = $object->getMagazineId() ? __('Edit Magazine') : __('New Magazine');
        $resultPage->getConfig()->getTitle()->prepend($title);
        $data = $this->_backendSession->getData('ct_magazine', true);
        if (!empty($data)) {
            $object->setData($data);
        }
        return $resultPage;
    }
}

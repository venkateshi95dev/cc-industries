<?php

namespace Cokertire\Disclaimers\Controller\Adminhtml\Disclaimers;

class Save extends \Cokertire\Disclaimers\Controller\Adminhtml\Disclaimers
{
    /**
     * Backend session
     * 
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * constructor
     * 
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Cokertire\Disclaimers\Model\DisclaimersFactory $disclaimersFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Backend\Model\Session $backendSession,
        \Cokertire\Disclaimers\Model\DisclaimersFactory $disclaimersFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->_backendSession = $backendSession;
        parent::__construct($disclaimersFactory, $registry, $resultRedirectFactory, $context);
    }

    /**
     * run the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost('post');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $disclaimers = $this->_initPost();
            $disclaimers->setData($data);
            try {
                $disclaimers->save();
                $this->messageManager->addSuccess(__('The Disclaimers has been saved.'));
                $this->_backendSession->setCokertireDistributors(false);
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        'disclaimers/disclaimers/edit',
                        [
                            'id' => $disclaimers->getDisclaimersId(),
                            '_current' => true
                        ]
                    );
                    return $resultRedirect;
                }
                $resultRedirect->setPath('disclaimers/disclaimers/');
                return $resultRedirect;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Distributors.'));
            }
            $this->_getSession()->setCokertireDistributors($data);
            $resultRedirect->setPath(
                'disclaimers/disclaimers/edit',
                [
                    'id' => $disclaimers->getDisclaimersId(),
                    '_current' => true
                ]
            );
            return $resultRedirect;
        }
        $resultRedirect->setPath('disclaimers/disclaimers/');
        return $resultRedirect;
    }

}

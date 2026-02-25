<?php

namespace Cokertire\Distributors\Controller\Adminhtml\Distributors;

class Save extends \Cokertire\Distributors\Controller\Adminhtml\Distributors
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
     * @param \Cokertire\Distributors\Model\DistributorsFactory $distributorsFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Backend\Model\Session $backendSession,
        \Cokertire\Distributors\Model\DistributorsFactory $distributorsFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->_backendSession = $backendSession;
        parent::__construct($distributorsFactory, $registry, $resultRedirectFactory, $context);
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
            $distributors = $this->_initPost();
            $distributors->setData($data);
            try {
                $distributors->save();
                $this->messageManager->addSuccess(__('The Distributors has been saved.'));
                $this->_backendSession->setCokertireDistributors(false);
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        'distributors/distributors/edit',
                        [
                            'id' => $distributors->getId(),
                            '_current' => true
                        ]
                    );
                    return $resultRedirect;
                }
                $resultRedirect->setPath('distributors/distributors/');
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
                'distributors/distributors/edit',
                [
                    'id' => $distributors->getDistributorsid(),
                    '_current' => true
                ]
            );
            return $resultRedirect;
        }
        $resultRedirect->setPath('distributors/distributors/');
        return $resultRedirect;
    }

}

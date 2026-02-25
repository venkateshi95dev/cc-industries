<?php

namespace Cokertire\Magazine\Controller\Adminhtml\Magazine;

class Save extends \Cokertire\Magazine\Controller\Adminhtml\Magazine
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
     * @param \Cokertire\Magazine\Model\MagazineFactory $magazineFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Backend\Model\Session $backendSession,
        \Cokertire\Magazine\Model\MagazineFactory $magazineFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->_backendSession = $backendSession;
        parent::__construct($magazineFactory, $registry, $resultRedirectFactory, $context);
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
            $object = $this->_initPost();
            $object->setData($data);
            try {
                $object->save();
                $this->messageManager->addSuccess(__('The Magazine has been saved.'));
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        'magazine/magazine/edit',
                        [
                            'id' => $distributors->getMagazineid(),
                            '_current' => true
                        ]
                    );
                    return $resultRedirect;
                }
                $resultRedirect->setPath('magazine/magazine/');
                return $resultRedirect;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Magazine.'));
            }
            $resultRedirect->setPath(
                'magazine/magazine/edit',
                [
                    'id' => $object->getMagazineid(),
                    '_current' => true
                ]
            );
            return $resultRedirect;
        }
        $resultRedirect->setPath('magazine/magazine/');
        return $resultRedirect;
    }

}

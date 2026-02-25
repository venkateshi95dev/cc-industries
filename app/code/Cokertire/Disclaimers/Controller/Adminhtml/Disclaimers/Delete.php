<?php

namespace Cokertire\Disclaimers\Controller\Adminhtml\Disclaimers;

class Delete extends \Cokertire\Disclaimers\Controller\Adminhtml\Disclaimers
{
    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->_resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $disclaimers = $this->_disclaimersFactory->create();
                $disclaimers->load($id);
                $disclaimers->delete();
                $this->messageManager->addSuccess(__('The Disclaimers has been deleted.'));
                $resultRedirect->setPath('disclaimers/disclaimers/');
                return $resultRedirect;
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                $resultRedirect->setPath('disclaimers/disclaimers/edit', ['id' => $id]);
                return $resultRedirect;
            }
        }

        $this->messageManager->addError(__('Disclaimers to delete was not found.'));

        $resultRedirect->setPath('disclaimers/disclaimers/');
        return $resultRedirect;
    }
}
